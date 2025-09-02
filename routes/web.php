<?php

use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\PackageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\ProfileController;

// move company profile to /ndeofficial and make root redirect to dashboard (home)
Route::get('/ndeofficial', function () {
    return view('compro'); // company profile (company profile stays at /ndeofficial)
})->name('compro');

// make the app home point to the registerclass (purchase/promotions landing)
Route::get('/', function () {
    return redirect(route('registerclass'));
});

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('topics', TopicController::class);
// });


// Public-facing registerclass (lessons, promos, purchase entrypoint)
Route::get('/registerclass', [KelasController::class, 'index'])->name('registerclass');
// user-facing dashboard alias (kept for compatibility with auth redirects)
Route::get('/dashboard', function () { return redirect(route('registerclass')); })->name('dashboard');
// Backwards compatibility: keep a named route 'kelas' that points to registerclass to avoid breaking
// old templates that call route('kelas'). This will generate /kelas and redirect visitors to /registerclass.
Route::get('/kelas', function () { return redirect(route('registerclass')); })->name('kelas');
// Keep the route names for lesson show/content to avoid breaking references elsewhere
Route::get('/registerclass/{lesson}', [KelasController::class, 'show'])->name('kelas.show');
Route::get('/registerclass/{lesson}/content', [KelasController::class, 'content'])->name('kelas.content');

// Song Tutorial page (access controlled in controller)
// Public landing for Song Tutorial index (navbar should point here)
Route::get('/song-tutorial/index', [App\Http\Controllers\SongTutorialController::class, 'indexLanding'])->name('song.tutorial.index');
Route::get('/song-tutorial', [App\Http\Controllers\SongTutorialController::class, 'index'])->name('song.tutorial');
Route::get('/song-tutorial/{lesson}', [App\Http\Controllers\SongTutorialController::class, 'show'])->name('song.tutorial.show');
Route::get('/song-tutorial/{lesson}/content', [App\Http\Controllers\SongTutorialController::class, 'content'])->name('song.tutorial.content');

// Route::get('/admin', [LessonController::class, 'index'])->name('admin.lessons.index');
// Route::get('/admin/create', [LessonController::class, 'create'])->name('admin.lessons.create');
// Route::post('admin/storedata', [LessonController::class, 'store'])->name('admin.lessons.store');
// Route::get('/admin/edit', [LessonController::class, 'edit'])->name('admin.lessons.edit');
// Route::delete('/admin/destroy/{lesson}', [LessonController::class, 'destroy'])->name('admin.lessons.destroy');


// // Admin Lesson
// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('lessons', LessonController::class);
//     Route::resource('topics', TopicController::class);
// });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function(){
        return redirect(route('admin.lessons.index'));
    })->name('dashboard');
    Route::resource('lessons', LessonController::class);
    // packages admin
    Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('packages', [PackageController::class, 'store'])->name('packages.store');
    Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
    Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
    // Route to create a signed upload URL for direct-to-Bunny uploads
    // Temporarily use a closure to avoid calling controller dispatch while debugging
    Route::post('bunny/upload-url', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => false,
            'message' => 'Direct browser upload endpoint temporarily disabled. Use the server upload endpoint at admin/bunny/upload-server.',
            'upload_url' => null,
        ], 501);
    })->name('bunny.upload-url');
    Route::post('bunny/upload-server', [App\Http\Controllers\BunnyController::class, 'uploadToBunny'])->name('bunny.upload-server');
    Route::get('bunny/video-status/{guid}', [App\Http\Controllers\BunnyController::class, 'videoStatus'])->name('bunny.video-status');
    Route::get('lessons/{lesson}/topics/create', [TopicController::class, 'create'])->name('topics.create');
    Route::post('lessons/{lesson}/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get('lessons/{lesson}/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
    Route::put('lessons/{lesson}/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('lessons/{lesson}/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
    // Payment methods admin
    Route::get('payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('payment-methods/update', [App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::post('payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::delete('payment-methods/{id}', [App\Http\Controllers\Admin\PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    Route::post('payment-methods/{id}/test', [App\Http\Controllers\Admin\PaymentMethodController::class, 'test'])->name('payment-methods.test');
    // transactions admin
    Route::get('transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
    // coaching bookings admin list
    Route::get('coaching/bookings', [App\Http\Controllers\Admin\CoachingBookingController::class, 'index'])->middleware('can:admin')->name('admin.coaching.bookings');
    Route::post('coaching/bookings/{booking}/accept', [App\Http\Controllers\Admin\CoachingBookingController::class, 'accept'])->middleware('can:admin');
    Route::post('coaching/bookings/{booking}/reject', [App\Http\Controllers\Admin\CoachingBookingController::class, 'reject'])->middleware('can:admin');
    // create Twilio room for a booking on demand
    Route::post('coaching/bookings/{booking}/create-room', [App\Http\Controllers\Admin\CoachingBookingController::class, 'createRoom'])->middleware('can:admin');
    Route::post('coaching/bookings/{booking}/end-room', [App\Http\Controllers\Admin\CoachingBookingController::class, 'endRoom'])->middleware('can:admin');
    // coaching feedback administration
    Route::get('coaching/feedbacks', [App\Http\Controllers\Admin\AdminFeedbackController::class, 'index'])->middleware('can:admin')->name('admin.coaching.feedbacks.index');
    Route::put('coaching/feedbacks/{feedback}', [App\Http\Controllers\Admin\AdminFeedbackController::class, 'update'])->middleware('can:admin')->name('admin.coaching.feedback.update');
    // coaching slot capacities admin
    Route::get('coaching/slot-capacities', [App\Http\Controllers\Admin\CoachingSlotCapacityController::class, 'index'])->name('admin.coaching.slotcapacities');
    Route::post('coaching/slot-capacities', [App\Http\Controllers\Admin\CoachingSlotCapacityController::class, 'store']);
    // delete saved slots for a specific date (AJAX)
    Route::post('coaching/slot-capacities/delete', [App\Http\Controllers\Admin\CoachingSlotCapacityController::class, 'destroy']);
    // referral settings (admin)
    Route::get('settings/referral', [\App\Http\Controllers\Admin\SettingController::class, 'referralForm'])->name('referral.settings');
    Route::post('settings/referral', [\App\Http\Controllers\Admin\SettingController::class, 'referralSave'])->name('referral.save');
    Route::get('settings/referral/export', [\App\Http\Controllers\Admin\SettingController::class, 'exportReferralCsv'])->name('referral.export');
    // new referral admin pages (settings, leaderboard)
    Route::get('referral/settings', [\App\Http\Controllers\Admin\ReferralController::class, 'settingsForm'])->name('referral.settings.form');
    Route::post('referral/settings', [\App\Http\Controllers\Admin\ReferralController::class, 'saveSettings'])->name('referral.settings.save');
    Route::get('referral/leaderboard', [\App\Http\Controllers\Admin\ReferralController::class, 'leaderboard'])->name('referral.leaderboard');

    // vouchers admin
    Route::get('vouchers', [\App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('vouchers/create', [\App\Http\Controllers\Admin\VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('vouchers', [\App\Http\Controllers\Admin\VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('vouchers/{voucher}/edit', [\App\Http\Controllers\Admin\VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('vouchers/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('vouchers/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'destroy'])->name('vouchers.destroy');

    // admin user packages/tickets page
    Route::get('users/packages', [\App\Http\Controllers\Admin\ReferralController::class, 'userPackages'])->name('users.packages');
    // admin: edit user package and tickets
    Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\ReferralController::class, 'editUser'])->name('users.edit');
    Route::post('users/{user}', [\App\Http\Controllers\Admin\ReferralController::class, 'updateUser'])->name('users.update');
    // NOTE: caching-bookings routes removed — bookings are now handled directly via coaching/bookings accept/reject
});

// Return stream URL for a topic (used by frontend to load private Bunny streams)
use App\Http\Controllers\BunnyController;
use App\Http\Controllers\CoachingController;
use App\Http\Controllers\CoachingCheckoutController;

Route::get('/topics/{topic}/stream', function (App\Models\Topic $topic) {
    // Prefer bunny_guid if present
    if ($topic->bunny_guid) {
        $signed = BunnyController::signUrl($topic->bunny_guid, 300);
        if ($signed) return response()->json(['url' => $signed]);
        return response()->json(['url' => BunnyController::cdnUrl($topic->bunny_guid)]);
    }

    // Fallback to legacy video_url for compatibility
    $path = $topic->video_url ?? null;
    if (! $path) return response()->json(['url' => null]);
    if (preg_match('#^https?://#i', $path)) return response()->json(['url' => $path]);
    $signed = BunnyController::signUrl($path, 300);
    if ($signed) return response()->json(['url' => $signed]);
    return response()->json(['url' => BunnyController::cdnUrl($path)]);
})->name('topics.stream');

// Coaching feature routes (require authentication)
Route::middleware('auth')->group(function(){
    Route::get('/coaching', [CoachingController::class, 'index'])->name('coaching.index');
    Route::get('/coaching/availability', [CoachingController::class, 'availability'])->name('coaching.availability');
    // batch availability for a range (start and end query params YYYY-MM-DD)
    Route::get('/coaching/availability-range', [CoachingController::class, 'availabilityRange'])->name('coaching.availability.range');
    Route::post('/coaching/book', [CoachingController::class, 'storeBooking'])->name('coaching.book');
    // feedback is saved together with booking; no separate route required
    Route::get('/coaching/thankyou/{booking?}', function ($booking = null) { return view('coaching.thankyou', compact('booking')); })->name('coaching.thankyou');
    // upcoming appointments page (user-facing)
    Route::get('/coaching/upcoming', function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $qb = \App\Models\CoachingBooking::where('user_id', $user->id)->where('status', '!=', 'cancelled')->orderBy('booking_time');
            // Only eager-load feedback if the table exists (migration may not have been run)
            if (\Illuminate\Support\Facades\Schema::hasTable('coaching_feedbacks')) {
                $qb = $qb->with('feedback');
            }
            $bookings = $qb->get();
        } else {
            $bookings = collect();
        }
        $hasTicket = $user ? \App\Models\CoachingTicket::where('user_id', $user->id)->where('is_used', false)->exists() : false;
        // load user's tickets so the view can show details
        $tickets = $user ? \App\Models\CoachingTicket::where('user_id', $user->id)->orderByDesc('id')->get() : collect();
    return view('coaching.upcoming', compact('bookings', 'hasTicket', 'tickets'));
    })->name('coaching.upcoming');
    // save note for a confirmed booking
    Route::post('/coaching/{booking}/note', [\App\Http\Controllers\CoachingController::class, 'updateNote'])->name('coaching.note');
    // save note for a pending caching booking
    Route::post('/coaching/caching/{caching}/note', [\App\Http\Controllers\CoachingController::class, 'updateCachingNote'])->name('coaching.caching.note');
    // Checkout flow for purchasing a coaching ticket (shows order summary + Midtrans UI)
    Route::get('/coaching/checkout', [CoachingCheckoutController::class, 'checkoutForm'])->name('coaching.checkout');
    Route::post('/coaching/checkout/create-order', [CoachingCheckoutController::class, 'createOrder'])->name('coaching.checkout.create');
    Route::get('/coaching/session/{booking}', [CoachingController::class, 'joinSession'])->name('coaching.session');
    Route::get('/coaching/token/{booking}', [CoachingController::class, 'token'])->name('coaching.token');
    Route::post('/coaching/{booking}/event', [CoachingController::class, 'logEvent'])->middleware('throttle:30,1')->name('coaching.event');
    // Purchase (beli kelas) routes - POST (submit purchase) requires authentication
    Route::post('/registerclass/{lesson}/buy', [App\Http\Controllers\KelasController::class, 'purchase'])->name('kelas.purchase');

    // Thank you / final step after successful payment
    Route::get('/registerclass/{lesson}/thankyou', [App\Http\Controllers\KelasController::class, 'thankyou'])->name('kelas.thankyou');
});

// Public buy page - viewing the buy page does not require auth so it can act as landing/home
Route::get('/registerclass/{lesson}/buy', [App\Http\Controllers\KelasController::class, 'buy'])->name('kelas.buy');

// Allow payment completion to be posted by Midtrans client (snap callback) even for guest flows
// This endpoint will associate guest pre_register data to a user inside the controller when needed.
Route::post('/registerclass/{lesson}/payment/complete', [App\Http\Controllers\KelasController::class, 'paymentComplete'])->name('kelas.payment.complete');

// Payment step: show payment UI (requires auth because we need user data) and handle midtrans callbacks
Route::middleware('auth')->group(function(){
    Route::get('/registerclass/{lesson}/payment', [App\Http\Controllers\KelasController::class, 'payment'])->name('kelas.payment');
});

// Lightweight endpoint to create midtrans snap token (should be in api.php in bigger apps)
Route::post('/api/midtrans/create', [App\Http\Controllers\MidtransController::class, 'createSnapToken']);

// QRIS generation endpoint
Route::post('/payments/generate-qris', [App\Http\Controllers\PaymentController::class, 'generateQris']);
// Midtrans server-to-server notification (webhook)
Route::post('/payments/midtrans-notify', [App\Http\Controllers\PaymentController::class, 'midtransNotification']);

// Client-side redirect targets after payment actions (user-facing)
Route::get('/payments/finish', [App\Http\Controllers\PaymentRedirectController::class, 'finish'])->name('payments.finish');
Route::get('/payments/error', [App\Http\Controllers\PaymentRedirectController::class, 'error'])->name('payments.error');
Route::get('/payments/status', [App\Http\Controllers\PaymentRedirectController::class, 'status'])->name('payments.status');

// Twilio Video webhook receiver (room/recording/participant events)
Route::post('/webhooks/twilio/video', [App\Http\Controllers\TwilioWebhookController::class, 'video']);

// include authentication routes (login, register, password reset, etc.)
require __DIR__ . '/auth.php';

// Simple AJAX endpoint to validate referral codes and return discount percent (uses admin setting)
Route::post('/referral/validate', function (\Illuminate\Http\Request $request) {
    $code = $request->input('code');
    if (! $code) return response()->json(['valid' => false, 'message' => 'No code provided'], 200);
    $user = \App\Models\User::where('referral_code', $code)->first();
    if (! $user) return response()->json(['valid' => false, 'message' => 'Code not found'], 200);

    // Prefer value stored in admin settings; fall back to config default (2%)
    $dbVal = \App\Models\Setting::get('referral.discount_percent', null);
    $discount = $dbVal !== null ? (int) $dbVal : (int) config('referral.discount_percent', 2);

    return response()->json(['valid' => true, 'discount_percent' => $discount, 'referrer' => ['id' => $user->id, 'name' => $user->name]]);
});

// Simple AJAX endpoint to validate voucher codes
Route::post('/vouchers/validate', function (\Illuminate\Http\Request $request) {
    $code = trim($request->input('code',''));
    if (! $code) return response()->json(['valid'=>false,'message'=>'No code provided']);
    $v = \App\Models\Voucher::where('code', $code)->first();
    if (! $v) return response()->json(['valid'=>false,'message'=>'Voucher not found']);
    if (! $v->isValid()) return response()->json(['valid'=>false,'message'=>'Voucher is not valid']);
    return response()->json(['valid'=>true,'discount_percent'=>$v->discount_percent,'voucher_id'=>$v->id]);
});

// User profile routes
Route::middleware('auth')->group(function(){
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/referrals', [ProfileController::class, 'referrals'])->name('profile.referrals');
    // Password update used by the profile password partial
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});


