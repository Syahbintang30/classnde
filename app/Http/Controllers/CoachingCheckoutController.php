<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Support\Str;

class CoachingCheckoutController extends Controller
{
    public function checkoutForm(Request $request)
    {
        $user = Auth::user();
        if (! $user) return redirect()->route('login');

        // find coaching package configured by admin
        $slug = config('coaching.coaching_package_slug', 'coaching-ticket');
        $package = Package::where('slug', $slug)->first();

        // prepare order summary from selected schedule params
        $schedule = $request->query('schedule'); // expected format: 2025-08-23 14:00
        $scheduleDisplay = $schedule ?: null;

        // check user eligibility: user must have purchased one of eligible packages
        $eligibleSlugs = config('coaching.eligible_packages', []);
        $hasPackage = false;
        if ($user && $user->package_id) {
            $p = Package::find($user->package_id);
            if ($p && in_array($p->slug, $eligibleSlugs)) $hasPackage = true;
        }

        // midtrans client key config
        $midtrans = config('services.midtrans');

        return view('coaching.checkout', compact('package', 'scheduleDisplay', 'hasPackage', 'midtrans'));
    }

    public function createOrder(Request $request)
    {
        $user = Auth::user();
        if (! $user) return response()->json(['error' => 'Authentication required'], 401);

        $data = $request->validate([
            'schedule' => 'required|string',
            'package_id' => 'required|integer|exists:packages,id',
        ]);

        // validate schedule format and that it's in the future
        try {
            $dt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data['schedule']);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'schedule must be in format YYYY-MM-DD HH:MM:SS'], 422);
        }
        if ($dt->lt(now()->addMinutes(10))) {
            return response()->json(['error' => 'Selected schedule must be at least 10 minutes in the future'], 422);
        }
        if ($dt->gt(now()->addMonths(6))) {
            return response()->json(['error' => 'Selected schedule is too far in the future'], 422);
        }

        // ensure user has eligible package
        $eligibleSlugs = config('coaching.eligible_packages', []);
        $userHasEligible = false;
        if ($user->package_id) {
            $p = Package::find($user->package_id);
            if ($p && in_array($p->slug, $eligibleSlugs)) $userHasEligible = true;
        }
        if (! $userHasEligible) {
            return response()->json(['error' => 'User not eligible to buy coaching ticket. Please purchase the required package first.'], 422);
        }

        $package = Package::find($data['package_id']);
        $gross = (int) ($package->price ?? 0);

        // create a local Transaction record (pending) - reuse Transaction model
        $external = 'coaching-' . time() . '-' . Str::random(6);
            $txn = Transaction::create([
                'order_id' => $external,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'method' => 'midtrans',
                'amount' => $gross,
                'status' => 'pending',
                'midtrans_response' => null,
            ]);

        // Return order info for client to call Midtrans Snap (client will call /api/midtrans/create)
        return response()->json(['order_id' => $external, 'gross_amount' => $gross, 'package_id' => $package->id]);
    }
}
