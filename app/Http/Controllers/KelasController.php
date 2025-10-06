<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Package;
use App\Models\CoachingTicket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        // logged-in users should normally only see the coaching-ticket package
        $packages = Package::where('slug', $coachingSlug)->orderBy('price')->get();

        // If the user previously purchased the 'beginner' package, offer an
        // "Upgrade Intermediate" package priced at (intermediate - beginner).
        try {
            $hasBeginner = false;
            // check current package_id first
            if (! empty($user->package_id)) {
                $cur = Package::find($user->package_id);
                if ($cur && $cur->slug === 'beginner') $hasBeginner = true;
            }
            // fallback: check historical purchases via UserPackage
            if (! $hasBeginner) {
                $hasBeginner = \App\Models\UserPackage::where('user_id', $user->id)
                    ->whereHas('package', function($q){ $q->where('slug', 'beginner'); })
                    ->exists();
            }

            if ($hasBeginner) {
                $beginner = Package::where('slug', 'beginner')->first();
                $intermediate = Package::where('slug', 'intermediate')->first();
                if ($beginner && $intermediate) {
                    $diff = max(0, intval($intermediate->price) - intval($beginner->price));
                    if ($diff > 0) {
                        // create or update a special upgrade package record so it can be selected/validated
                        $upgrade = Package::where('slug', 'upgrade-intermediate')->first();
                        if (! $upgrade) {
                            $upgrade = Package::create([
                                'name' => 'Upgrade Intermediate',
                                'slug' => 'upgrade-intermediate',
                                'price' => $diff,
                                'description' => 'Upgrade from Beginner to Intermediate — bayar selisih harga saja.',
                                'benefits' => "Upgrade fee to move from Beginner to Intermediate.",
                                'image' => null,
                            ]);
                        } else {
                            // keep price in sync with current difference
                            if ((int)$upgrade->price !== $diff) {
                                $upgrade->price = $diff;
                                $upgrade->save();
                            }
                        }
                        // append upgrade package to the packages collection
                        $packages = $packages->concat(collect([$upgrade]));
                    }
                }
            }
        } catch (\Throwable $e) {
            // don't break the page if upgrade creation fails; just log and continue
            \Illuminate\Support\Facades\Log::warning('Failed to prepare upgrade package', ['err' => $e->getMessage(), 'user_id' => $user->id]);
        }
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
            // Use safe approach instead of raw SQL for getting empty results
            return view('kelas._lesson_content', [
                'lesson' => $lesson->loadMissing(['topics' => function($q){ 
                    $q->where('id', '=', -1); // Safe way to get no results
                }])
            ]);
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
            'order_id' => \App\Services\OrderIdGenerator::generate('nde'),
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
            'order_id' => \App\Services\OrderIdGenerator::generate('nde'),
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
    // Do not grant package or create tickets here — permission and DB inserts must
    // only happen once the payment reaches 'settlement'. Keep a lightweight
    // acknowledgement and redirect the user to the payment UI where the
    // settlement will be processed via webhook / client polling.
    return redirect()->route('kelas.payment', ['lesson' => $lesson->id, 'package_id' => $request->input('package_id')])->with('info', 'Silakan lanjutkan pembayaran untuk menyelesaikan pembelian. Akses paket akan diberikan setelah pembayaran terkonfirmasi.');
    }

    /**
     * Handle client/server notification after payment completes (simple handler).
     */
    public function paymentComplete(Request $request, Lesson $lesson)
    {
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    // In a production app you'd validate the notification from Midtrans signature
    // NOTE: Do NOT create user accounts or write DB records here for pending payments.
    // Guest account creation is postponed until we have a confirmed settlement so
    // we avoid creating accounts for incomplete/abandoned payments.

        if (! $user) {
            // cannot associate ticket without a user; redirect home
            return redirect()->route('dashboard')->with('error', 'User not found after payment. Please contact support.');
        }

    // We no longer create tickets or assign package here. Tickets and package
    // assignment will be created when we detect 'settlement' below (either
    // from client-reported midtrans_result or via webhook).
    $createdTickets = [];
    $firstTicketId = null;
    $pkgId = $request->input('package_id') ?: $user->package_id;
    $package = $pkgId ? \App\Models\Package::find($pkgId) : null;
    $beginnerSlugs = ['beginner', 'intermediate', 'upgrade-intermediate'];

        // Default: keep existing thank-you redirect (backwards compatible)
        // If midtrans_result is provided (snap client returned), try to persist a Transaction
        $midResRaw = $request->input('midtrans_result');
        if (! empty($midResRaw)) {
            $data = is_string($midResRaw) ? json_decode($midResRaw, true) : (array) $midResRaw;
            $orderId = $data['order_id'] ?? $data['orderId'] ?? $request->input('order_id') ?? null;
            $txnStatus = $data['transaction_status'] ?? $data['status'] ?? $data['status_code'] ?? null;
            try {
                if ($orderId) {
                    $existing = Transaction::where('order_id', $orderId)->latest()->first();
                    if (! $existing) {
                        // Normalize status to either 'pending' or 'settlement'
                        $normalized = 'pending';
                        $lower = strtolower((string) ($txnStatus ?? ''));
                        if (in_array($lower, ['settlement','capture','success'])) $normalized = 'settlement';

                        // Do NOT persist a Transaction record if it's still pending. Only
                        // create DB transaction when we already have settlement confirmed.
                        if ($normalized === 'settlement') {
                            // If guest flow provided pre_register, create/login user now before persisting
                            if (! $user && $request->session()->has('pre_register')) {
                                $pre = $request->session()->get('pre_register');
                                $exists = \App\Models\User::where('email', $pre['email'])->exists();
                                if ($exists) {
                                    $user = \App\Models\User::where('email', $pre['email'])->first();
                                    Auth::login($user);
                                } else {
                                    // SECURITY FIX: Generate random secure password instead of using stored password
                                    // This eliminates password storage vulnerability in session
                                    $plainPassword = str()->random(16); // Generate secure random password
                                    
                                    $user = \App\Models\User::create([
                                        'name' => $pre['name'] ?? 'User',
                                        'email' => $pre['email'],
                                        'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                                        'phone' => $pre['phone'] ?? null,
                                        'package_id' => $pre['package_id'] ?? null,
                                        'referred_by' => null,
                                    ]);
                                    if (! empty($pre['referral'])) {
                                        $refCode = $pre['referral'];
                                        $referrer = \App\Models\User::where('referral_code', $refCode)->first();
                                        if ($referrer) {
                                            $user->referred_by = $referrer->id;
                                            $user->save();
                                        }
                                    }
                                    event(new \Illuminate\Auth\Events\Registered($user));
                                    
                                    // Send welcome email with password to new user
                                    try {
                                        $user->notify(new \App\Notifications\WelcomeWithPasswordNotification($plainPassword));
                                    } catch (\Throwable $e) {
                                        // Log error but don't fail the registration process
                                        \Illuminate\Support\Facades\Log::error('Failed to send welcome email: ' . $e->getMessage());
                                    }
                                    
                                    Auth::login($user);
                                }
                                $request->session()->forget('pre_register');
                            }

                            Transaction::create([
                                'order_id' => $orderId,
                                'user_id' => $user->id ?? null,
                                'package_id' => $pkgId ?? null,
                                'method' => isset($data['payment_type']) ? strtoupper($data['payment_type']) : ($request->input('payment_method') ?? null),
                                'amount' => $data['gross_amount'] ?? null,
                                'original_amount' => $data['gross_amount'] ?? null,
                                'status' => $normalized,
                                'midtrans_response' => $data,
                            ]);

                            // grant tickets & package immediately when the client already reports settlement
                            $qty = (int) ($request->input('package_qty') ?: session('pre_register.package_qty') ?: 1);
                            for ($i = 0; $i < max(1, $qty); $i++) {
                                $createdTickets[] = CoachingTicket::create([
                                    'user_id' => $user->id,
                                    'source' => 'midtrans',
                                    'is_used' => false,
                                ]);
                            }
                            if ($request->input('package_id') && $user) {
                                $user->package_id = $request->input('package_id');
                                $user->save();
                            }
                            $firstTicketId = !empty($createdTickets) && isset($createdTickets[0]) ? $createdTickets[0]->id : null;
                            if ($package && isset($package->slug) && in_array($package->slug, $beginnerSlugs)) {
                                return redirect()->route('kelas.thankyou', ['lesson' => $lesson->id])->with(['ticket_id' => $firstTicketId]);
                            }
                        } else {
                            // pending: do not write DB transaction here. The webhook will
                            // create the DB transaction on settlement. Keep user on payment
                            // page / client polling will check transactionStatus endpoint.
                        }
                    } else {
                        $existing->midtrans_response = array_merge(is_string($existing->midtrans_response) ? (json_decode($existing->midtrans_response, true) ?: []) : (array) $existing->midtrans_response, $data ?: []);
                        if ($txnStatus) {
                            $lower = strtolower((string) $txnStatus);
                                $existing->status = in_array($lower, ['settlement','capture','success']) ? 'settlement' : 'pending';
                                // If this update moved the txn into settlement, grant tickets & package now.
                                if (in_array($lower, ['settlement','capture','success'])) {
                                    // If guest flow provided pre_register, create/login user now before granting
                                    if (! $user && $request->session()->has('pre_register')) {
                                        $pre = $request->session()->get('pre_register');
                                        $exists = \App\Models\User::where('email', $pre['email'])->exists();
                                        if ($exists) {
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
                                        $request->session()->forget('pre_register');
                                    }

                                    $qty = (int) ($request->input('package_qty') ?: session('pre_register.package_qty') ?: 1);
                                    for ($i = 0; $i < max(1, $qty); $i++) {
                                        $createdTickets[] = CoachingTicket::create([
                                            'user_id' => $user->id,
                                            'source' => 'midtrans',
                                            'is_used' => false,
                                        ]);
                                    }
                                    if ($request->input('package_id') && $user) {
                                        $user->package_id = $request->input('package_id');
                                        $user->save();
                                    }
                                    $firstTicketId = !empty($createdTickets) && isset($createdTickets[0]) ? $createdTickets[0]->id : null;
                                    if ($package && isset($package->slug) && in_array($package->slug, $beginnerSlugs)) {
                                        return redirect()->route('kelas.thankyou', ['lesson' => $lesson->id])->with(['ticket_id' => $firstTicketId]);
                                    }
                                }
                        }
                        $existing->save();
                    }
                }
            } catch (\Throwable $e) {
                // don't break flow; webhook will still create DB txn later
                Log::warning('paymentComplete: failed to persist midtrans_result', ['err' => $e->getMessage(), 'order_id' => $orderId ?? null]);
            }

            // If the client-side reported settlement already, redirect to thankyou with order_id
            $lowerStat = strtolower((string) ($txnStatus ?? ''));
            if (in_array($lowerStat, ['settlement','capture','success'])) {
                // client already reported settlement; the grant logic above will have
                // executed. Redirect to centralized thankyou page.
                return redirect()->route('payments.thankyou', ['lesson' => $lesson->id, 'order_id' => $orderId]);
            }

            // If client reports pending/not-paid, keep the user on the payment page (do not redirect to thankyou)
            // Include order_id so client-side polling or waiting UI can pick it up.
            if (! in_array($lowerStat, ['settlement','capture','success'])) {
            return redirect()->route('kelas.payment', ['lesson' => $lesson->id, 'order_id' => $orderId])
                ->with('info', 'Pembayaran belum dikonfirmasi. Silakan selesaikan atau tunggu konfirmasi di halaman pembayaran.');
            }
        }

        return redirect()->route('kelas.thankyou', ['lesson' => $lesson->id])->with(['ticket_id' => $firstTicketId]);
    }

    /**
     * Show final step / thank you page after purchase
     */
    public function thankyou(Lesson $lesson)
    {
        $user = Auth::user();
        // If an order_id query param exists, send user to the centralized payments.thankyou
        $orderId = request()->query('order_id') ?? request()->query('orderId') ?? null;
        if ($orderId) {
            return redirect()->route('payments.thankyou', ['order_id' => $orderId]);
        }

        if (! $user) return redirect()->route('dashboard');

        // Block access to thankyou page until we received settlement webhook from Midtrans
        $hasSettlement = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['settlement','capture','success'])
            ->whereNotNull('midtrans_response')
            ->exists();

        if (! $hasSettlement) {
            // If we haven't recorded settlement yet, keep user on payment page
            return redirect()->route('kelas.payment', ['lesson' => $lesson->id])
                ->with('error', 'Pembayaran belum dikonfirmasi. Silakan selesaikan pembayaran di halaman pembayaran.');
        }

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
