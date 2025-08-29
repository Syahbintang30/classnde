@extends('layouts.app')

@section('title', 'Thank you')

@section('content')
<div style="min-height:72vh;background:#000;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:24px;">
    <!-- progress indicator: steps (Info -> Payment -> Done) -->
    <div class="steps" role="tablist" aria-label="Booking steps" style="display:flex;align-items:center;gap:14px;max-width:720px;width:100%;justify-content:center;padding-top:20px;">
        <div class="step" title="Info"><i class="fa fa-info" aria-hidden="true"></i><span class="sr-only">Info</span></div>
        <div class="line" aria-hidden="true"></div>
        <div class="step" title="Payment"><i class="fa fa-credit-card" aria-hidden="true"></i><span class="sr-only">Payment</span></div>
        <div class="line" aria-hidden="true"></div>
        <div class="step active" aria-current="step" title="Done"><i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">Done</span></div>
    </div>

    <div style="flex:1;display:flex;align-items:center;justify-content:center;width:100%;">
    <div style="text-align:center;max-width:820px;padding:60px 20px;color:#fff;">

            @if($package)
                @php
                    $beginnerSlugs = ['beginner', 'intermediate'];
                    $coachingSlug = config('coaching.coaching_package_slug', 'coaching-ticket');
                @endphp

                {{-- Coaching-ticket specific thank-you card --}}
                @if(isset($package->slug) && $package->slug === $coachingSlug)
                    <div role="region" aria-labelledby="thankyou-head" class="thankyou-card">
                        <h1 id="thankyou-head" class="thankyou-head">Welcome — you've got your coaching ticket!</h1>
                        <p class="thankyou-sub">Your ticket is confirmed and ready to use. The next step is to schedule your one-on-one session to get personalized feedback from our expert coach.</p>

                        <div class="purchase-summary">
                            <div class="purchase-line"><strong>Item Purchased:</strong> Coaching Ticket</div>
                            <div class="purchase-line" style="margin-top:6px;"><strong>Benefit:</strong> Redeemable for one live, 1-on-1 session.</div>
                        </div>

                        <div class="cta-wrap">
                            {{-- direct users to coaching booking/index where they can schedule; include ticket id if available --}}
                            @if(!empty($ticket) && isset($ticket->id))
                                <a href="{{ route('coaching.index') }}" class="thankyou-cta">Book Your Session Now</a>
                            @else
                                <a href="{{ route('coaching.index') }}" class="thankyou-cta">Book Your Session Now</a>
                            @endif
                        </div>

                        <div style="margin-top:18px;max-width:680px;margin-left:auto;margin-right:auto;text-align:left;">
                            <div style="border:1px dashed rgba(255,255,255,0.06);padding:16px;border-radius:8px;background:rgba(255,255,255,0.01);">
                                <h4 style="margin:0 0 8px 0;color:#fff">Not Ready to Schedule Now?</h4>
                                <div style="color:rgba(255,255,255,0.85);line-height:1.5;font-size:14px;margin-bottom:10px">No problem. You can book your session anytime from your dashboard. Your ticket will be waiting for you.</div>
                                <a href="{{ route('coaching.upcoming') }}" class="bonus-link" style="color:#fff;font-weight:700;text-decoration:underline;">Go to My Dashboard</a>
                            </div>
                        </div>
                    </div>

                {{-- beginner/intermediate (course) thank-you card --}}
                @elseif(isset($package->slug) && in_array($package->slug, $beginnerSlugs))
                    <div role="region" aria-labelledby="thankyou-head" class="thankyou-card">
                        <h1 id="thankyou-head" class="thankyou-head">Welcome — you're all set to start learning.</h1>
                        <p class="thankyou-sub">Your access to the <strong>{{ $package->name }}</strong> course is ready. You can dive into your first lesson now and begin your journey to becoming a better guitarist.</p>

                        <div class="purchase-summary">
                            <div class="purchase-line"><strong>Course Purchased:</strong> {{ $package->name }}</div>
                            <div class="purchase-line" style="margin-top:6px;"><strong>Access:</strong> Lifetime access to all video lessons.</div>
                        </div>

                        @php
                            $currentUser = auth()->user();
                            $canAccessCourse = false;
                            if($currentUser && isset($package->id)){
                                $canAccessCourse = (int)$currentUser->package_id === (int)$package->id;
                            }
                        @endphp
                        <div class="cta-wrap">
                @if($canAccessCourse && isset($lesson) && $lesson)
                    <a href="{{ route('kelas.show', $lesson->id) }}" class="thankyou-cta">Start Learning Now</a>
                            @else
                                <a href="{{ route('registerclass') }}" class="thankyou-cta">Start Learning Now</a>
                            @endif
                        </div>
                        @unless($canAccessCourse)
                            <div style="margin-top:10px;color:rgba(255,255,255,0.75);font-size:13px">If you haven't completed payment or aren't signed in yet, go to the classes page to complete your purchase or sign in.</div>
                        @endunless

                        <div class="bonus-panel">
                            <strong>Bonus Offer: Unlock Personal Coaching</strong>
                            <div class="bonus-copy">As a student, you now have an exclusive opportunity to get personalized feedback with a one-on-one Coaching Ticket. <a href="{{ route('coaching.index') }}" class="bonus-link">Learn more</a></div>
                        </div>
                    </div>
                @else
                    <div style="margin:24px auto 26px auto;max-width:640px;text-align:left;padding:18px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);">
                        <h3 style="margin-top:0;margin-bottom:8px">Your Package: {{ $package->name }}</h3>
                        <div style="font-size:14px;opacity:0.85;margin-bottom:10px">Benefits:</div>
                        <div style="font-size:13px;opacity:0.9">
                            @if(!empty($package->benefits))
                                @php $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $package->benefits))); @endphp
                                @if(count($lines))
                                    <ul style="margin:0 0 0 18px;padding:0;">
                                        @foreach($lines as $line)
                                            <li style="margin-bottom:6px">{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <div style="opacity:0.8">One coaching ticket to book a session.</div>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div style="margin:24px auto 26px auto;max-width:640px;text-align:left;padding:18px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);">
                    <h3 style="margin-top:0;margin-bottom:8px">Account details</h3>
                    <div style="font-size:14px;opacity:0.85">Name: <strong>{{ $user->name }}</strong></div>
                    <div style="font-size:14px;opacity:0.85">Email: <strong>{{ $user->email }}</strong></div>
                </div>
            @endif

            {{-- single CTA is inside the box above; no extra buttons here to avoid confusion --}}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .buy-progress { position:relative; }
    .buy-progress .progress-line { flex:1;height:2px;background:rgba(255,255,255,0.06);border-radius:2px; }
    .buy-progress .circle { width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:18px }
    .buy-progress .circle.active { background:transparent;border-color:#fff;color:#fff }

    /* New steps styles matching sizes of the original progress */
    .steps { position:relative; }
    .steps .line { flex:1;height:3px;background:rgba(184, 184, 184, 0.247);border-radius:2px; }
    /* slightly larger circles and icons */
    .steps .step { width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:20px }
    /* active (third) step: white background with black icon */
    .steps .step.active { background:#fff;border-color:#fff;color:#000 }
    /* accessible hidden text */
    .sr-only { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0 }

    /* Thank you card styles */
    .thankyou-card {
        margin:24px auto 26px auto;
        max-width:780px;
        padding:32px 36px;
        border-radius:12px;
        background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
        border:1px solid rgba(255,255,255,0.04);
        box-shadow: 0 6px 30px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.01);
        text-align:center;
    }
    .thankyou-head { margin:0 0 10px 0; font-size:28px; font-weight:800; color:#ffffff; letter-spacing: -0.2px }
    .thankyou-sub { margin:0 0 18px 0; color:rgba(255,255,255,0.9); font-size:15px; line-height:1.6; max-width:680px; margin-left:auto; margin-right:auto }
    .purchase-summary { margin:8px auto 18px auto; color:rgba(255,255,255,0.95); font-size:14px }
    .purchase-line { margin:6px 0 }

    .cta-wrap { margin-top:8px; display:flex; justify-content:center }
    .thankyou-cta {
        display:inline-block; padding:14px 28px; border-radius:10px; background:#ffffff; color:#000; font-weight:800; text-decoration:none; box-shadow:0 6px 18px rgba(0,0,0,0.45);
        transition:transform .12s ease, box-shadow .12s ease; font-size:15px
    }
    .thankyou-cta:hover { transform:translateY(-3px); box-shadow:0 12px 30px rgba(0,0,0,0.6) }

    .bonus-panel { margin-top:20px; padding:16px; border-radius:8px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.03); text-align:center }
    .bonus-copy { margin-top:8px; color:rgba(255,255,255,0.9); line-height:1.5 }
    .bonus-link { color:#fff; text-decoration:underline; font-weight:700 }
</style>
@endpush
