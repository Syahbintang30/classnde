<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Package;
use App\Models\CoachingTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    public function index()
    {
    // Dashboard becomes the buy/home page showing package options
    // only show lessons of type 'course' on the buy page
    $lessons = Lesson::where('type', 'course')->with(['topics' => function($q){ $q->orderBy('position'); }])->orderBy('position')->get();
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    // coaching package slug and eligible public package slugs are configurable
    $coachingSlug = config('coaching.coaching_package_slug', 'coaching-ticket');
    $eligibleSlugs = config('coaching.eligible_packages', ['beginner','intermediate']);

    if ($user) {
        // logged-in users should only see the coaching-ticket package
        $packages = Package::where('slug', $coachingSlug)->orderBy('price')->get();
    } else {
        // guests see the eligible beginner/intermediate packages only
        $packages = Package::whereIn('slug', $eligibleSlugs)->orderBy('price')->get();
    }
    // pick a default lesson (first) so purchase route in the buy view has an id
    $lesson = $lessons->first();
    // show buy page with packages
    return view('kelas.buy', ['lessons' => $lessons, 'packages' => $packages, 'lesson' => $lesson]);
    }

    public function show(Lesson $lesson)
    {
        // load topics ordered by position
        $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
        // also provide list of all lessons for sidebar navigation
        // only show lessons with type 'course' in the sidebar
        $lessons = Lesson::where('type', 'course')->orderBy('position')->get();
        // if the requested lesson is not a course, redirect to the first course lesson
        if ($lesson->type !== 'course') {
            $first = Lesson::where('type', 'course')->orderBy('position')->first();
            if ($first) {
                return redirect()->route('kelas.show', $first->id);
            }
        }
        return view('kelas', compact('lessons', 'lesson'));
    }

    /**
     * Return the lesson main content as a partial (AJAX)
     */
    public function content(Lesson $lesson)
    {
        // Only return content for lessons of type 'course'
        if ($lesson->type !== 'course') {
            // return an empty partial so AJAX consumers gracefully handle it
            return view('kelas._lesson_content', ['lesson' => $lesson->loadMissing(['topics' => function($q){ $q->whereRaw('1 = 0'); }])]);
        }
        $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
        return view('kelas._lesson_content', compact('lesson'));
    }

    /**
     * Show purchase page for a lesson (beli kelas).
     */
    public function buy(Lesson $lesson)
    {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $packages = Package::orderBy('price')->get();

        // determine package from request or user's existing package
        $packageId = request()->input('package_id') ?: ($user->package_id ?? null);
        $package = $packageId ? Package::find($packageId) : null;

        // package price is canonical; fallback to a sensible default
        $price = (int) ($package->price ?? 125000);
        // qty can be passed as query param (guests) or request; default 1
        $qty = (int) (request()->input('package_qty') ?: session('pre_register.package_qty') ?: 1);

        // prepare order and apply referral discount if present in session/request
        $rawAmount = $price * max(1, $qty);
        $appliedReferralPercent = 0;
        $referralCode = session('pre_register.referral') ?: request()->input('referral');
        if (! empty($referralCode)) {
            $refUser = \App\Models\User::where('referral_code', $referralCode)->first();
            $dbVal = \App\Models\Setting::get('referral.discount_percent', null);
            $discountPercent = $dbVal !== null ? (int) $dbVal : (int) config('referral.discount_percent', 2);
            if ($refUser) {
                $appliedReferralPercent = (int) $discountPercent;
            }
        }

        $grossAmount = $rawAmount;
        if ($appliedReferralPercent > 0) {
            $grossAmount = (int) round($rawAmount * (100 - $appliedReferralPercent) / 100);
        }

        $order = [
            'order_id' => 'ORDER-' . time() . '-' . ($user->id ?? 'guest'),
            'gross_amount' => $grossAmount,
            'original_amount' => $rawAmount,
            'applied_referral_percent' => $appliedReferralPercent,
            'referral_code' => $referralCode,
            'item_details' => [
                ['id' => $package ? 'package-'.$package->id : 'lesson-'.$lesson->id, 'price' => (int) ($price * (100 - $appliedReferralPercent) / 100), 'quantity' => max(1, $qty), 'name' => $package ? $package->name : $lesson->title . ($appliedReferralPercent ? (' (Referral ' . $appliedReferralPercent . '%)') : '')],
            ],
            'customer_details' => [
                'first_name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
            ],
        ];

        // pass Midtrans client key to view
        $midtrans = config('services.midtrans');
    // load active payment methods
    $methods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get();
    return view('kelas.payment', compact('lesson', 'order', 'midtrans', 'package', 'methods'));
    }

    /**
     * Show the payment UI for a specific lesson (requires auth route).
     */
    public function payment(Lesson $lesson)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $packages = Package::orderBy('price')->get();

        // determine package from request or user's existing package
        $packageId = request()->input('package_id') ?: ($user->package_id ?? null);
        $package = $packageId ? Package::find($packageId) : null;

        // package price is canonical; fallback to a sensible default
        $price = (int) ($package->price ?? 125000);
        $qty = (int) (request()->input('package_qty') ?: session('pre_register.package_qty') ?: 1);

        $rawAmount = $price * max(1, $qty);
        $appliedReferralPercent = 0;
        $referralCode = session('pre_register.referral') ?: request()->input('referral');
        if (! empty($referralCode)) {
            $refUser = \App\Models\User::where('referral_code', $referralCode)->first();
            $dbVal = \App\Models\Setting::get('referral.discount_percent', null);
            $discountPercent = $dbVal !== null ? (int) $dbVal : (int) config('referral.discount_percent', 2);
            if ($refUser) {
                $appliedReferralPercent = (int) $discountPercent;
            }
        }

        $grossAmount = $rawAmount;
        if ($appliedReferralPercent > 0) {
            $grossAmount = (int) round($rawAmount * (100 - $appliedReferralPercent) / 100);
        }

        $order = [
            'order_id' => 'ORDER-' . time() . '-' . ($user->id ?? 'guest'),
            'gross_amount' => $grossAmount,
            'original_amount' => $rawAmount,
            'applied_referral_percent' => $appliedReferralPercent,
            'referral_code' => $referralCode,
            'item_details' => [
                ['id' => $package ? 'package-'.$package->id : 'lesson-'.$lesson->id, 'price' => (int) ($price * (100 - $appliedReferralPercent) / 100), 'quantity' => max(1, $qty), 'name' => $package ? $package->name : $lesson->title . ($appliedReferralPercent ? (' (Referral ' . $appliedReferralPercent . '%)') : '')],
            ],
            'customer_details' => [
                'first_name' => $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
            ],
        ];

        $midtrans = config('services.midtrans');
        $methods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('name')->get();

        return view('kelas.payment', compact('lesson', 'order', 'midtrans', 'package', 'methods'));
    }

    /**
     * Handle purchase form submission (very small stub).
     */
    public function purchase(Request $request, Lesson $lesson)
    {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
        // Assign user's package if provided
        if ($user) {
            $pkg = $request->input('package_id') ?: $user->package_id;
            if ($pkg) {
                $user->package_id = $pkg;
                $user->save();
            }
        }
        // create a CoachingTicket placeholder (no lesson_id now)
        CoachingTicket::create([
            'user_id' => $user->id,
            'source' => 'purchase',
            'is_used' => false,
        ]);

        return redirect()->route('coaching.index')->with('status', 'Pembelian berhasil. Anda dapat memesan sesi coaching.');
    }

    /**
     * Handle client/server notification after payment completes (simple handler).
     */
    public function paymentComplete(Request $request, Lesson $lesson)
    {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

        // In a production app you'd validate the notification from Midtrans signature
        // If there's no authenticated user, check for pre-register session data and create the user now
        if (! $user && $request->session()->has('pre_register')) {
            $pre = $request->session()->get('pre_register');
            // basic validation - ensure email not already used
            $exists = \App\Models\User::where('email', $pre['email'])->exists();
            if ($exists) {
                // if exists, log the user in
                $user = \App\Models\User::where('email', $pre['email'])->first();
                Auth::login($user);
            } else {
                $user = \App\Models\User::create([
                    'name' => $pre['name'] ?? 'User',
                    'email' => $pre['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($pre['password'] ?? str()->random(12)),
                    'phone' => $pre['phone'] ?? null,
                        'package_id' => $pre['package_id'] ?? null,
                        'referred_by' => null,
                ]);
                    // If a referral code was provided in the pre-register data, try to resolve it
                    if (! empty($pre['referral'])) {
                        $refCode = $pre['referral'];
                        $referrer = \App\Models\User::where('referral_code', $refCode)->first();
                        if ($referrer) {
                            $user->referred_by = $referrer->id;
                            $user->save();
                        }
                    }
                event(new \Illuminate\Auth\Events\Registered($user));
                Auth::login($user);
            }
            // remove pre_register session now that we've created or logged in the user
            $request->session()->forget('pre_register');
        }

        if (! $user) {
            // cannot associate ticket without a user; redirect home
            return redirect()->route('dashboard')->with('error', 'User not found after payment. Please contact support.');
        }

        // For now, store a CoachingTicket and mark as used=false until webhook confirms settlement
            $qty = (int) ($request->input('package_qty') ?: session('pre_register.package_qty') ?: 1);
            $createdTickets = [];
            for ($i = 0; $i < max(1, $qty); $i++) {
                $createdTickets[] = CoachingTicket::create([
                    'user_id' => $user->id,
                    'source' => 'midtrans',
                    'is_used' => false,
                ]);
            }

        // ensure user's package_id persisted if provided in request or session
        if ($request->input('package_id')) {
            $user->package_id = $request->input('package_id');
            $user->save();
        }

        // Decide redirect based on purchased package slug for better UX
        $firstTicketId = !empty($createdTickets) && isset($createdTickets[0]) ? $createdTickets[0]->id : null;
        $pkgId = $request->input('package_id') ?: $user->package_id;
        $package = $pkgId ? \App\Models\Package::find($pkgId) : null;
        $beginnerSlugs = ['beginner', 'intermediate'];
        // Ensure buyers of beginner/intermediate packages receive at least one live coaching ticket
        if ($package && isset($package->slug) && in_array($package->slug, $beginnerSlugs)) {
            if (empty($createdTickets) || count($createdTickets) === 0) {
                $ticket = CoachingTicket::create([
                    'user_id' => $user->id,
                    'source' => 'auto-grant',
                    'is_used' => false,
                ]);
                $createdTickets[] = $ticket;
                // ensure firstTicketId points to created ticket so thankyou can show it
                $firstTicketId = $ticket->id;
            }
            // redirect to class-specific thank you page so student can start learning
            return redirect()->route('kelas.thankyou', ['lesson' => $lesson->id])->with(['ticket_id' => $firstTicketId]);
        }

        // Default: keep existing thank-you redirect (backwards compatible)
        return redirect()->route('kelas.thankyou', ['lesson' => $lesson->id])->with(['ticket_id' => $firstTicketId]);
    }

    /**
     * Show final step / thank you page after purchase
     */
    public function thankyou(Lesson $lesson)
    {
        $user = Auth::user();
        // If an order_id query param exists, send user to the centralized payments.finish
        $orderId = request()->query('order_id') ?? request()->query('orderId') ?? null;
        if ($orderId) {
            return redirect()->route('payments.finish', ['order_id' => $orderId]);
        }

        if (! $user) return redirect()->route('dashboard');

        $package = null;
        if ($user->package_id) {
            $package = Package::find($user->package_id);
        }

        // try to load ticket from flashed session (fallback to last ticket)
        $ticket = null;
        $ticketId = session('ticket_id');
        if ($ticketId) {
            $ticket = CoachingTicket::find($ticketId);
        }
        if (! $ticket) {
            $ticket = CoachingTicket::where('user_id', $user->id)->orderByDesc('id')->first();
        }

        return view('kelas.thankyou', compact('user', 'package', 'ticket', 'lesson'));
    }
}
