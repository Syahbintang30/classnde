<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CachingBooking;
use App\Models\CoachingBooking;
use App\Models\CoachingTicket;
use Illuminate\Support\Facades\DB;

class CachingBookingController extends Controller
{
    public function index()
    {
        // show recent coaching bookings (authoritative source). Keep caching listing for compatibility if table exists.
        $rows = CoachingBooking::with('user')->orderBy('booking_time', 'desc')->paginate(40);
        return view('admin.caching_bookings.index', compact('rows'));
    }

    /**
     * Admin accepts a caching booking: persist the authoritative CoachingBooking record
     * (if not already present or if it's still pending) and mark caching row accepted.
     */
    public function accept(CachingBooking $caching)
    {
        DB::beginTransaction();
        try {
            // if a coaching booking exists for this caching.booking_id, update status
            // if caching exists, migrate into coaching bookings; else find or use existing booking
            $cb = $caching->booking;
            if (! $cb) {
                $ticket = CoachingTicket::where('user_id', $caching->user_id)->where('is_used', false)->first();
                if (! $ticket) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'User has no available ticket to accept this booking.');
                }
                $bookingTime = $caching->date . ' ' . $caching->time;
                $cb = CoachingBooking::create([
                    'user_id' => $caching->user_id,
                    'ticket_id' => $ticket->id,
                    'booking_time' => $bookingTime,
                    'status' => 'accepted',
                    'session_number' => 1,
                    'notes' => $caching->meta['note'] ?? null,
                ]);
                $ticket->is_used = true;
                $ticket->save();
            } else {
                $cb->status = 'accepted';
                $cb->save();
            }

            $caching->status = 'accepted';
            $caching->booking_id = $cb->id;
            $caching->save();

            DB::commit();

            // notify user
            try { if ($cb->user) $cb->user->notify(new \App\Notifications\BookingStatusChanged($cb, 'accepted')); } catch (\Throwable $e) { logger()->warning('User notify failed on caching accept: ' . $e->getMessage()); }

            return redirect()->back()->with('success', 'Caching booking accepted and persisted');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Accepting caching booking failed: ' . $e->getMessage(), ['caching' => $caching->id]);
            return redirect()->back()->with('error', 'Failed to accept caching booking: ' . $e->getMessage());
        }
    }

    /**
     * Admin rejects a caching booking: set caching row and any linked booking to rejected and release ticket.
     */
    public function reject(CachingBooking $caching)
    {
        DB::beginTransaction();
        try {
            $cb = $caching->booking;
            if ($cb) {
                if ($cb->ticket) { $cb->ticket->is_used = false; $cb->ticket->save(); }
                $cb->status = 'rejected';
                $cb->save();
            }
            $caching->status = 'rejected';
            $caching->save();
            DB::commit();
            try { if ($caching->booking && $caching->booking->user) $caching->booking->user->notify(new \App\Notifications\BookingStatusChanged($caching->booking, 'rejected')); } catch (\Throwable $e) { logger()->warning('User notify failed on caching reject: ' . $e->getMessage()); }
            return redirect()->back()->with('success', 'Caching booking rejected');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Rejecting caching booking failed: ' . $e->getMessage(), ['caching' => $caching->id]);
            return redirect()->back()->with('error', 'Failed to reject caching booking: ' . $e->getMessage());
        }
    }
}
