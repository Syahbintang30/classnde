<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CoachingSlotCapacity;
use App\Models\CoachingBooking;
use Carbon\Carbon;

class CoachingSlotCapacityController extends Controller
{
    // show simple admin UI to list, create, and remove slot capacities
    public function index(Request $request)
    {
    // admin can view a month calendar and edit multiple dates; always show current month in realtime
    $year = (int) date('Y');
    $month = (int) date('m');

        // fetch existing capacities for the selected month and group by date
        $rows = CoachingSlotCapacity::whereYear('date', $year)->whereMonth('date', $month)->get();
        $slots = [];
        foreach ($rows as $r) {
            $d = $r->date->toDateString();
            if (!isset($slots[$d])) $slots[$d] = [];
            // store times as simple array for easier JSON consumption in view
            $slots[$d][] = $r->time;
        }

        // fetch bookings in this month (exclude cancelled) and group by date->time (HH:MM)
        $bookingRows = CoachingBooking::whereYear('booking_time', $year)->whereMonth('booking_time', $month)->where('status', '!=', 'cancelled')->get();
        $booked = [];
        foreach ($bookingRows as $b) {
            $bt = $b->booking_time;
            // ensure we have a Carbon instance (some records may come back as strings)
            if (! $bt instanceof Carbon) {
                try {
                    $bt = Carbon::parse($bt);
                } catch (\Throwable $e) {
                    // skip malformed booking_time values
                    continue;
                }
            }

            $d = $bt->toDateString();
            $t = $bt->format('H:i');
            if (!isset($booked[$d])) $booked[$d] = [];
            if (!in_array($t, $booked[$d])) $booked[$d][] = $t;
        }

        return view('admin.coaching.slotcapacities', compact('year','month','slots','booked'));
    }

    public function store(Request $request)
    {
        // Support two modes:
        // - bulk JSON payload `slots_json` => { '2025-08-24': ['08:00','09:00'], ... }
        // - single slot old behavior (date,time,capacity)

        if ($request->has('slots_json')) {
            // accept either a JSON-encoded string or an already-decoded array.
            $raw = $request->input('slots_json');
            if (is_string($raw)) {
                $payload = json_decode($raw, true);
            } elseif (is_array($raw)) {
                $payload = $raw;
            } else {
                // fallback: try to parse raw request body (some clients send nested JSON)
                $content = $request->getContent();
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['slots_json'])) {
                    $payload = is_string($decoded['slots_json']) ? json_decode($decoded['slots_json'], true) : $decoded['slots_json'];
                } else {
                    $payload = null;
                }
            }

            if (!is_array($payload)) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Invalid payload'], 422);
                }
                return redirect()->back()->with('error', 'Invalid payload');
            }

            // determine mode: replace existing slots for the date, or merge (add only new hours)
            $replace = filter_var($request->input('replace', true), FILTER_VALIDATE_BOOLEAN);

            foreach ($payload as $date => $hours) {
                // normalize hours to unique HH:MM strings
                $hours = array_values(array_filter(array_map(function($t){
                    if (!$t) return null;
                    return date('H:i', strtotime($t));
                }, (array)$hours)));

                if ($replace) {
                    // remove all existing slots for that date and recreate according to hours
                    CoachingSlotCapacity::where('date', $date)->delete();

                    foreach ($hours as $h) {
                        CoachingSlotCapacity::create([
                            'date' => $date,
                            'time' => $h,
                        ]);
                    }
                } else {
                    // merge: add only hours that don't already exist
                    $existing = CoachingSlotCapacity::where('date', $date)->pluck('time')->toArray();
                    $toCreate = array_values(array_diff($hours, $existing));
                    foreach ($toCreate as $h) {
                        CoachingSlotCapacity::create([
                            'date' => $date,
                            'time' => $h,
                        ]);
                    }
                }
            }

            // return updated slots for the processed dates so the UI can refresh
            $dates = array_keys($payload);
            $rows = CoachingSlotCapacity::whereIn('date', $dates)->get();
            $result = [];
            foreach ($rows as $r) {
                $d = $r->date->toDateString();
                if (!isset($result[$d])) $result[$d] = [];
                $result[$d][] = $r->time;
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'updated' => $result]);
            }

            return redirect()->back()->with('success', 'Slot capacities saved');
        }

        // fallback: single slot create/update

        $data = $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
        ]);

        // normalize time to HH:MM
        $time = date('H:i', strtotime($data['time']));

        CoachingSlotCapacity::updateOrCreate(
            ['date' => $data['date'], 'time' => $time],
            []
        );

        return redirect()->back()->with('success', 'Slot capacity saved');
    }

    // delete all slots for a given date (AJAX expected)
    public function destroy(Request $request)
    {
        $date = $request->input('date');
        if (!$date) {
            return $request->wantsJson() ? response()->json(['error' => 'Missing date'], 422) : redirect()->back()->with('error', 'Missing date');
        }

        // basic validation: ensure date format
        if (!strtotime($date)) {
            return $request->wantsJson() ? response()->json(['error' => 'Invalid date'], 422) : redirect()->back()->with('error', 'Invalid date');
        }

        $time = $request->input('time');
        if ($time) {
            // normalize time
            $t = date('H:i', strtotime($time));
            CoachingSlotCapacity::where('date', $date)->where('time', $t)->delete();
        } else {
            // delete entire date
            CoachingSlotCapacity::where('date', $date)->delete();
        }

        // return remaining times for that date
        $remaining = CoachingSlotCapacity::where('date', $date)->pluck('time')->toArray();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'remaining' => $remaining]);
        }

        return redirect()->back()->with('success', 'Slots deleted');
    }
}

