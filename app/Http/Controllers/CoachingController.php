<?php

namespace App\Http\Controllers;

use App\Models\CoachingBooking;
use App\Models\CoachingTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService;

class CoachingController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function index()
    {
        $user = Auth::user();
        $tickets = $user ? CoachingTicket::where('user_id', $user->id)->get() : collect();
        $bookings = $user ? CoachingBooking::where('user_id', $user->id)->get() : collect();

        $hasAvailableTicket = false;
        if ($user) {
            $hasAvailableTicket = CoachingTicket::where('user_id', $user->id)->where('is_used', false)->exists();
        }

    $coachingPkg = \App\Models\Package::where('slug', config('coaching.coaching_package_slug'))->first();
    return view('coaching.index', compact('tickets', 'bookings', 'hasAvailableTicket', 'coachingPkg'));
    }

    // feedback is now saved together with booking inside storeBooking()

    public function storeBooking(Request $request)
    {
        $user = Auth::user();
        if (! $user) return redirect()->route('login');
        $data = $request->validate([
            'booking_time' => 'required|string',
            'notes' => 'nullable|string|max:255',
            'keluh_kesah' => 'nullable|string|max:1000',
            'want_to_learn' => 'nullable|string|max:255',
        ]);

    logger()->info('CoachingController@storeBooking called', ['user_id' => $user->id ?? null, 'payload' => $data]);

        // validate booking_time format and window
        try {
            $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data['booking_time']);
        } catch (\Throwable $e) {
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['ok' => false, 'errors' => ['booking_time' => ['Invalid datetime format, expected YYYY-MM-DD HH:MM:SS']]], 422);
            }
            return redirect()->route('coaching.index')->withErrors(['booking_time' => 'Invalid datetime format, expected YYYY-MM-DD HH:MM:SS'])->withInput();
        }
        if ($dt->lt(now()->addMinutes(5))) {
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['ok' => false, 'errors' => ['booking_time' => ['Booking time must be at least 5 minutes in the future']]], 422);
            }
            return redirect()->route('coaching.index')->withErrors(['booking_time' => 'Booking time must be at least 5 minutes in the future'])->withInput();
        }
        if ($dt->gt(now()->addMonths(6))) {
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['ok' => false, 'errors' => ['booking_time' => ['Booking time is too far in the future']]], 422);
            }
            return redirect()->route('coaching.index')->withErrors(['booking_time' => 'Booking time is too far in the future'])->withInput();
        }

        // find available ticket
        $ticket = CoachingTicket::where('user_id', $user->id)->where('is_used', false)->first();

        if (! $ticket) {
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['ok' => false, 'errors' => ['ticket' => ['No available tickets. Please purchase one.']]], 422);
            }
            return redirect()->route('coaching.index')->withErrors(['ticket' => 'No available tickets. Please purchase one.'])->withInput();
        }

        // Attempt atomic reservation: use DB::transaction to wrap create operations
        $booking = null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function() use (&$booking, $data, $user, $ticket) {
                $dt = \Carbon\Carbon::parse($data['booking_time']);
                $date = $dt->toDateString();
                $time = $dt->format('H:i');

                // capacity is implicitly 1 per admin design (one person per slot)
                $capacity = 1;

                // count existing bookings (pending or accepted) for that slot
                $qb = CoachingBooking::whereDate('booking_time', $date)
                        ->whereTime('booking_time', $time);

                // use row locking only when the driver supports it (mysql, pgsql)
                $driver = null;
                try {
                    $driver = \Illuminate\Support\Facades\DB::getPdo() ? \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) : null;
                } catch (\Throwable $e) {
                    $driver = null;
                }

                if (in_array($driver, ['mysql', 'pgsql', 'pgsql'])) {
                    $qb = $qb->lockForUpdate();
                }

                $taken = $qb->count();

                if ($taken >= $capacity) {
                    logger()->info('Booking slot full', ['date' => $date, 'time' => $time, 'taken' => $taken]);
                    throw new \RuntimeException('Slot full');
                }

                // create booking and populate commonly expected fields immediately
                // New behaviour: bookings created by users are 'pending' by default and require admin action
                $booking = CoachingBooking::create([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'booking_time' => $data['booking_time'],
                    // mark pending so admin must accept or reject
                    'status' => 'pending',
                    'session_number' => 1,
                    'notes' => isset($data['notes']) ? $data['notes'] : null,
                ]);

                // attach feedback fields into booking.notes so everything is centralized
                try {
                    $parts = [];
                    if (!empty($data['keluh_kesah'])) $parts[] = "Keluhan: " . $data['keluh_kesah'];
                    if (!empty($data['want_to_learn'])) $parts[] = "Ingin belajar: " . $data['want_to_learn'];
                    if (!empty($parts)) {
                        $extra = implode("\n\n", $parts);
                        $booking->notes = trim(($booking->notes ? $booking->notes . "\n\n" : '') . $extra);
                        $booking->save();
                    }
                } catch (\Throwable $e) {
                    logger()->warning('Failed to merge feedback into booking notes', ['err' => $e->getMessage()]);
                }

                logger()->info('CoachingBooking created inside transaction', ['booking_id' => $booking->id, 'user_id' => $user->id, 'ticket_id' => $ticket->id]);

                // NOTE: CachingBooking table is deprecated — primary source of truth is coaching_bookings.

                // reserve ticket
                $ticket->is_used = true;
                $ticket->save();
            });
        } catch (\Throwable $e) {
            logger()->error('Booking transaction failed', ['error' => $e->getMessage()]);
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['ok' => false, 'error' => 'Selected slot is full or failed to create booking'], 422);
            }
            return redirect()->route('coaching.index')->withErrors(['booking_time' => 'Failed to create booking, please try again.']);
        }

        // ensure booking is fresh and ticket reserved
        if ($booking) {
            $booking = $booking->fresh();

            // log DB connection details to help debug where records are stored
            try {
                $conn = \Illuminate\Support\Facades\DB::getDefaultConnection();
                $pdo = \Illuminate\Support\Facades\DB::getPdo();
                $driver = $pdo ? $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) : null;
                logger()->info('Booking DB info', ['connection' => $conn, 'driver' => $driver, 'booking_id' => $booking->id]);
            } catch (\Throwable $e) {
                logger()->warning('Failed to log DB info for booking', ['err' => $e->getMessage()]);
            }

            // Clear cached availability for the booked date range so frontend reflects the new booking
            try {
                $dt = \Carbon\Carbon::parse($booking->booking_time);
                $key = 'coaching_avail_range:' . $dt->toDateString() . ':' . $dt->toDateString();
                \Illuminate\Support\Facades\Cache::forget($key);
                logger()->info('Cleared coaching availability cache', ['key' => $key, 'booking_id' => $booking->id]);
            } catch (\Throwable $e) {
                logger()->warning('Failed to clear availability cache', ['err' => $e->getMessage(), 'booking_id' => $booking->id]);
            }
        }

        // attempt to create Twilio room only for bookings that are already accepted.
        // This prevents auto-creating rooms for pending bookings (which may later be rejected by admin).
        try {
            if ($booking && $booking->status === 'accepted' && $this->twilio->isConfigured()) {
                // construct room unique name based on the chosen date/time + booking id
                // e.g. coaching-20250829_1800-123
                try {
                    $parsed = \Carbon\Carbon::parse($booking->booking_time);
                    $roomName = 'coaching-' . $parsed->format('Ymd_Hi') . '-' . $booking->id;
                } catch (\Throwable $e) {
                    // fallback to booking id only if parsing fails
                    $roomName = 'coaching-' . $booking->id;
                }
                logger()->info('Creating Twilio room', ['room' => $roomName, 'booking_id' => $booking->id]);
                $room = $this->twilio->createOrFetchRoom($roomName);
                if ($room && isset($room->sid)) {
                    $booking->twilio_room_sid = $room->sid;
                    $booking->save();
                    logger()->info('Twilio room created and saved', ['booking_id' => $booking->id, 'room_sid' => $room->sid]);
                }
            } else {
                logger()->info('Skipping Twilio room creation (not accepted or not configured)', ['booking_id' => $booking->id ?? null]);
            }
        } catch (\Throwable $e) {
            logger()->warning('Failed to create Twilio room during booking', ['booking' => $booking->id ?? null, 'error' => $e->getMessage()]);
        }

        // notify admins
        try {
            $adminEmails = env('ADMIN_EMAILS', '');
            if ($adminEmails) {
                $list = array_map('trim', explode(',', $adminEmails));
                foreach ($list as $addr) {
                    \Illuminate\Support\Facades\Notification::route('mail', $addr)->notify(new \App\Notifications\AdminBookingCreated($booking));
                }
            }
        } catch (\Exception $e) {
            logger()->warning('Failed to notify admins about booking: ' . $e->getMessage());
        }

        // If request expects JSON (AJAX/fetch) return booking id so client can redirect
        if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json(['ok' => true, 'booking' => $booking->id]);
        }

        // Redirect to a simple thank-you page for regular form submissions
        return redirect()->route('coaching.thankyou', ['booking' => $booking->id])->with('success', 'Booking created successfully');
    }

    public function joinSession(CoachingBooking $booking)
    {
        $user = Auth::user();
        // basic authorization: owner OR assigned coach OR configured coach emails
        $isOwner = $user && $booking->user_id === $user->id;
        $isAssignedCoach = $user && $booking->coach_user_id && $booking->coach_user_id === $user->id;
        $isConfiguredCoach = false;
        if ($user && config('coaching.coaches')) {
            $isConfiguredCoach = in_array($user->email, config('coaching.coaches'));
        }

        // allow admins (users with admin ability) to join from admin panel
        $isAdmin = false;
        try {
            $isAdmin = $user && \Illuminate\Support\Facades\Gate::allows('admin');
        } catch (\Throwable $e) {
            $isAdmin = false;
        }
        if (! $user || (! $isOwner && ! $isAssignedCoach && ! $isConfiguredCoach && ! $isAdmin)) {
            abort(403);
        }
        if (! $this->twilio->isConfigured()) {
            abort(500, 'Twilio not configured');
        }

    // Prepare room uniqueName
    $roomName = 'coaching-' . $booking->id;

    // ensure related user is loaded to avoid lazy-loading in the view
    $booking->loadMissing('user');

        // enforce schedule window for non-admins: allow from 10 minutes before until 60 minutes after start
        try {
            $start = \Carbon\Carbon::parse($booking->booking_time);
            $now = now();
            if (! $isAdmin && ($now->lt($start->copy()->subMinutes(10)) || $now->gt($start->copy()->addMinutes(60)))) {
                abort(403, 'Session not available at this time');
            }
        } catch (\Throwable $e) {
            abort(400, 'Invalid booking time');
        }

        try {
            $room = $this->twilio->createOrFetchRoom($roomName);
        } catch (\Exception $e) {
            // log and show friendly message
            logger()->error('Twilio room error: ' . $e->getMessage(), ['booking' => $booking->id]);
            abort(500, 'Failed to prepare video room');
        }

        // Persist twilio_room_sid if not set
        if (! $booking->twilio_room_sid) {
            $booking->twilio_room_sid = $room->sid ?? null;
            $booking->save();
        }

        // Create Access Token
        $identity = $this->twilio->generateIdentity($user);
        try {
            $accessToken = $this->twilio->createAccessToken($identity, $roomName);
        } catch (\Exception $e) {
            logger()->error('Twilio token error: ' . $e->getMessage(), ['booking' => $booking->id]);
            abort(500, 'Failed to generate access token');
        }

        // pass isAdmin flag to view so UI can render admin controls
        return view('coaching.session', compact('booking', 'accessToken', 'roomName'))
            ->with('isAdmin', $isAdmin);
    }

    public function token(Request $request, CoachingBooking $booking)
    {
        $user = Auth::user();
        $isOwner = $user && $booking->user_id === $user->id;
        $isAssignedCoach = $user && $booking->coach_user_id && $booking->coach_user_id === $user->id;
        $isConfiguredCoach = false;
        if ($user && config('coaching.coaches')) {
            $isConfiguredCoach = in_array($user->email, config('coaching.coaches'));
        }

        if (! $user || (! $isOwner && ! $isAssignedCoach && ! $isConfiguredCoach)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if (! $this->twilio->isConfigured()) {
            return response()->json(['error' => 'Twilio not configured'], 500);
        }

        $identity = $this->twilio->generateIdentity($user);
        $roomName = 'coaching-' . $booking->id;

        // enforce schedule window: allow token from 10 minutes before start until 60 minutes after start
        try {
            $start = \Carbon\Carbon::parse($booking->booking_time);
            $now = now();
            if ($now->lt($start->copy()->subMinutes(10)) || $now->gt($start->copy()->addMinutes(60))) {
                return response()->json(['error' => 'Token not available at this time'], 403);
            }
        } catch (\Throwable $e) {
            // if parsing fails, deny to be safe
            return response()->json(['error' => 'Invalid booking time'], 400);
        }

        try {
            $token = $this->twilio->createAccessToken($identity, $roomName);
        } catch (\Exception $e) {
            logger()->error('Twilio token endpoint error: ' . $e->getMessage(), ['booking' => $booking->id]);
            return response()->json(['error' => 'Failed to generate token'], 500);
        }

        return response()->json([
            'token' => $token,
            'room' => $roomName,
        ]);
    }

    public function logEvent(Request $request, CoachingBooking $booking)
    {
        $user = Auth::user();
        $isOwner = $user && $booking->user_id === $user->id;
        $isAssignedCoach = $user && $booking->coach_user_id && $booking->coach_user_id === $user->id;
        $isConfiguredCoach = false;
        if ($user && config('coaching.coaches')) {
            $isConfiguredCoach = in_array($user->email, config('coaching.coaches'));
        }

        if (! $user || (! $isOwner && ! $isAssignedCoach && ! $isConfiguredCoach)) return response()->json(['error' => 'Unauthorized'], 403);

        $data = $request->validate([
            'event' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        // append event details into booking->notes so events are centralized
        try {
            $meta = $data['meta'] ?? null;
            $line = '[' . now()->toDateTimeString() . '] ' . ($data['event'] ?? 'event');
            if ($meta && is_array($meta)) {
                $line .= ' ' . json_encode($meta);
            }
            $booking->notes = trim(($booking->notes ? $booking->notes . "\n\n" : '') . $line);
            $booking->save();
        } catch (\Throwable $e) {
            logger()->warning('Failed to append event to booking notes', ['err' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
    * Update note for a booking (user-editable). Bookings are created as 'pending' and later accepted by admin.
     */
    public function updateNote(Request $request, CoachingBooking $booking)
    {
        $user = Auth::user();
        if (! $user || $booking->user_id !== $user->id) return redirect()->back()->with('error', 'Unauthorized');
        $data = $request->validate([ 'note' => 'required|string|max:255' ]);
        $booking->notes = $data['note'];
        $booking->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'note' => $booking->notes]);
        }
        return redirect()->back()->with('success','Note saved');
    }

    /**
     * Update note on a pending caching booking (user-editable before admin acceptance)
     */
    public function updateCachingNote(Request $request, \App\Models\CachingBooking $caching)
    {
        $user = Auth::user();
        if (! $user || $caching->user_id !== $user->id) return redirect()->back()->with('error', 'Unauthorized');
        $data = $request->validate([ 'note' => 'required|string|max:255' ]);
        $meta = $caching->meta ?? [];
        $meta['note'] = $data['note'];
        $caching->meta = $meta;
        $caching->save();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'note' => $meta['note']]);
        }
        return redirect()->back()->with('success','Note saved');
    }

    // Return availability for a given date (simple implementation)
    public function availability(Request $request)
    {
        $user = Auth::user();
        if (! $user) return response()->json(['error' => 'Unauthorized'], 403);

        $date = $request->query('date');
        if (! $date) return response()->json(['error' => 'date missing'], 400);

        // find bookings on that date
        $booked = CoachingBooking::whereDate('booking_time', $date)->get();

        // load admin-defined capacities for this date
        $capacityRows = \App\Models\CoachingSlotCapacity::whereDate('date', $date)->get();

        $result = [];
        if ($capacityRows->count() > 0) {
            // Build slots from admin-defined times (exact HH:MM keys)
            foreach ($capacityRows as $r) {
                $time = $r->time;
                $cap = (int) ($r->capacity ?? 1);
                $result[$time] = ['capacity' => $cap, 'taken' => 0, 'remaining' => $cap];
            }

            // Count bookings for exact times (HH:MM)
            foreach ($booked as $b) {
                $t = \Carbon\Carbon::parse($b->booking_time)->format('H:i');
                if (isset($result[$t])) {
                    $result[$t]['taken']++;
                    $result[$t]['remaining'] = max(0, $result[$t]['capacity'] - $result[$t]['taken']);
                }
            }
        } else {
            // No admin-defined slots for this date - return empty slots so frontend hides availability
            $result = [];
        }

        return response()->json(['slots' => $result]);
    }

    /**
     * Return availability summary for a date range (inclusive).
     * Response format: { days: { 'YYYY-MM-DD': remainingCount, ... } }
     */
    public function availabilityRange(Request $request)
    {
        $user = Auth::user();
        if (! $user) return response()->json(['error' => 'Unauthorized'], 403);

        $start = $request->query('start');
        $end = $request->query('end');
        if (! $start || ! $end) return response()->json(['error' => 'start/end missing'], 400);

        try {
            $startDt = \Carbon\Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
            $endDt = \Carbon\Carbon::createFromFormat('Y-m-d', $end)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid date format, expected YYYY-MM-DD'], 400);
        }

        if ($endDt->lt($startDt)) return response()->json(['error' => 'end must be >= start'], 400);

        // small validation to avoid huge ranges
        if ($endDt->diffInDays($startDt) > 92) return response()->json(['error' => 'range too large'], 400);

        $days = [];
        // optional short cache to reduce DB load if many users hit same month
        $cacheKey = 'coaching_avail_range:' . $startDt->toDateString() . ':' . $endDt->toDateString();
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached) return response()->json(['days' => $cached]);

        for ($d = $startDt->copy(); $d->lte($endDt); $d->addDay()) {
            $ds = $d->toDateString();
            $booked = CoachingBooking::whereDate('booking_time', $ds)->get();
            $capacityRows = \App\Models\CoachingSlotCapacity::whereDate('date', $ds)->get();

            $remainingCount = 0;
            if ($capacityRows->count() > 0) {
                $map = [];
                foreach ($capacityRows as $r) {
                    $time = $r->time;
                    $cap = (int) ($r->capacity ?? 1);
                    $map[$time] = ['capacity' => $cap, 'taken' => 0, 'remaining' => $cap];
                }
                foreach ($booked as $b) {
                    $t = \Carbon\Carbon::parse($b->booking_time)->format('H:i');
                    if (isset($map[$t])) {
                        $map[$t]['taken']++;
                        $map[$t]['remaining'] = max(0, $map[$t]['capacity'] - $map[$t]['taken']);
                    }
                }
                foreach ($map as $time => $info) {
                    if (($info['remaining'] ?? 0) > 0) $remainingCount++;
                }
            } else {
                $remainingCount = 0;
            }
            $days[$ds] = $remainingCount;
        }

        // cache for short time (10s) to smooth spikes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $days, 10);

        return response()->json(['days' => $days]);
    }
}
