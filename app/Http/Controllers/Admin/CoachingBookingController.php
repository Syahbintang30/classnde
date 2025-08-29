<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoachingBooking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TwilioService;

class CoachingBookingController extends Controller
{
    public function index(Request $request)
    {
        // Simple admin listing: latest first, paginate 50
        // Order so upcoming sessions (>= now) appear first, then past ones; within each group order by booking_time ascending
        $nowStr = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $bookings = CoachingBooking::with(['user','coach','ticket'])
            ->orderByRaw("(CASE WHEN booking_time >= ? THEN 0 ELSE 1 END), booking_time ASC", [$nowStr])
            ->paginate(50);

        // Compute aggregated "Taken" counts per slot for the dates shown on this page to avoid N+1 queries.
        // Build date/time expressions per DB driver
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $dayExpr = "date(booking_time)";
            $hmExpr = "strftime('%H:%M', booking_time)";
        } elseif ($driver === 'mysql') {
            $dayExpr = "DATE(booking_time)";
            $hmExpr = "DATE_FORMAT(booking_time, '%H:%i')";
        } elseif ($driver === 'pgsql') {
            $dayExpr = "booking_time::date";
            $hmExpr = "to_char(booking_time, 'HH24:MI')";
        } else {
            // fallback to sqlite-style expressions
            $dayExpr = "date(booking_time)";
            $hmExpr = "strftime('%H:%M', booking_time)";
        }

        // Collect distinct dates present in current page bookings
        $dates = $bookings->getCollection()->pluck('booking_time')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->unique()->values()->all();

        $slotCounts = [];
        if (!empty($dates)) {
            $rows = CoachingBooking::selectRaw("{$dayExpr} as day, {$hmExpr} as hm, count(*) as cnt")
                ->whereIn(DB::raw($dayExpr), $dates)
                ->groupBy('day','hm')
                ->get();
            foreach ($rows as $r) {
                $slotCounts[$r->day . ' ' . $r->hm] = (int) $r->cnt;
            }
        }

        return view('admin.coaching.bookings', compact('bookings', 'slotCounts'));
    }

    /**
     * Create Twilio room for the booking on-demand and persist sid.
     */
    public function createRoom(CoachingBooking $booking, TwilioService $twilio)
    {
        if (! $twilio->isConfigured()) {
            return redirect()->back()->with('error', 'Twilio not configured');
        }

        $roomName = 'coaching-' . $booking->id;
        try {
            $room = $twilio->createOrFetchRoom($roomName);
            if ($room && isset($room->sid)) {
                $booking->twilio_room_sid = $room->sid;
                $booking->save();
                return redirect()->back()->with('success', 'Twilio room created');
            }
            return redirect()->back()->with('error', 'Failed to create Twilio room');
        } catch (\Throwable $e) {
            logger()->error('Admin createRoom failed: ' . $e->getMessage(), ['booking' => $booking->id]);
            return redirect()->back()->with('error', 'Twilio API error: ' . $e->getMessage());
        }
    }

    /**
     * End (complete) a Twilio room so participants are disconnected and room closed.
     */
    public function endRoom(CoachingBooking $booking, TwilioService $twilio)
    {
        if (! $twilio->isConfigured()) {
            return redirect()->back()->with('error', 'Twilio not configured');
        }
        $sid = $booking->twilio_room_sid;
        if (! $sid) {
            return redirect()->back()->with('error', 'No Twilio room attached to booking');
        }

        try {
            $client = $twilio->getClient();
            if (! $client) throw new \RuntimeException('Twilio client not available');
            // Intelephense doesn't include dynamic SDK resource methods in its stubs.
            // Cast rooms resource to mixed to avoid static analysis warning while keeping runtime behaviour.
            /** @var mixed $roomsResource */
            $roomsResource = $client->video->v1->rooms;
            $roomsResource->update($sid, ['status' => 'completed']);
            return redirect()->back()->with('success', 'Room ended');
        } catch (\Throwable $e) {
            logger()->error('Failed to end Twilio room: ' . $e->getMessage(), ['booking' => $booking->id]);
            return redirect()->back()->with('error', 'Failed to end room: ' . $e->getMessage());
        }
    }

    public function accept(CoachingBooking $booking)
    {
        $booking->status = 'accepted';
        $booking->save();
        // create Twilio room upon admin acceptance so only accepted bookings get rooms
        try {
            if (app()->bound(\App\Services\TwilioService::class)) {
                $twilio = app(\App\Services\TwilioService::class);
                if ($twilio->isConfigured() && empty($booking->twilio_room_sid)) {
                    try {
                        $parsed = \Carbon\Carbon::parse($booking->booking_time);
                        $roomName = 'coaching-' . $parsed->format('Ymd_Hi') . '-' . $booking->id;
                    } catch (\Throwable $e) {
                        $roomName = 'coaching-' . $booking->id;
                    }
                    $room = $twilio->createOrFetchRoom($roomName);
                    if ($room && isset($room->sid)) {
                        $booking->twilio_room_sid = $room->sid;
                        $booking->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            logger()->warning('Failed to create Twilio room on accept: ' . $e->getMessage(), ['booking' => $booking->id]);
        }
        try {
            if ($booking->user) $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, 'accepted'));
        } catch (\Exception $e) { logger()->warning('Failed to notify user about acceptance: ' . $e->getMessage()); }
        return redirect()->back()->with('success', 'Booking accepted');
    }

    public function reject(CoachingBooking $booking, Request $request)
    {
        // release ticket back to user
        if ($booking->ticket) {
            $booking->ticket->is_used = false;
            $booking->ticket->save();
        }

        // set status to rejected and save admin note (reason)
        $booking->status = 'rejected';
        $booking->admin_note = $request->input('reason') ?? 'Admin not available, please reschedule';
        $booking->save();

        // notify user about rejection and reason
        try {
            if ($booking->user) $booking->user->notify(new \App\Notifications\BookingStatusChanged($booking, 'rejected'));
        } catch (\Exception $e) { logger()->warning('Failed to notify user about rejection: ' . $e->getMessage()); }

        return redirect()->back()->with('success', 'Booking marked rejected and ticket returned to user');
    }
}
