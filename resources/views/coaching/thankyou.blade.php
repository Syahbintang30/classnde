@extends('layouts.app')

@push('styles')
    <style>
        .thank-page { min-height: 60vh; display:flex; align-items:center; justify-content:center; padding:40px 16px; }
        .thank-inner { text-align:center; color: rgba(255,255,255,0.95); }
        .thank-inner h1 { font-weight:600; font-size:28px; margin-bottom:12px; }
        .thank-inner p { opacity:0.85; margin-bottom:22px; }
        .btn-view { display:inline-block; padding:12px 26px; border-radius:24px; background:#fff; color:#000; font-weight:700; text-decoration:none; }
        .steps { width:100%; max-width:820px; margin:0 auto 26px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .steps .step { width:48px; height:48px; border-radius:50%; border:2px solid rgba(255,255,255,0.12); display:inline-flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.9); }
        .steps .line { flex:1; height:1px; background:rgba(255,255,255,0.95); margin:0 12px; }
        .steps .step.complete { background: rgba(255,255,255,0.95); color:#000; }
    </style>
@endpush

@section('content')
    <div class="thank-page">
        <div style="width:100%;max-width:980px;">
            <div class="steps" aria-hidden="true">
                <div class="step complete"><i class="fa fa-info"></i></div>
                <div class="line"></div>
                <div class="step complete"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></div>
                <div class="line"></div>
                <div class="step complete"><i class="fa fa-check"></i></div>
            </div>

            @php
                $bookingObj = null;
                try {
                    if (!empty($booking)) {
                        $bookingObj = \App\Models\CoachingBooking::find($booking);
                    }
                } catch (\Throwable $e) {
                    $bookingObj = null;
                }
            @endphp

            @if($bookingObj)
                {{-- If booking exists in DB, redirect to upcoming appointments page so user sees the slot list --}}
                <script>window.location.href = '{{ route('coaching.upcoming') }}';</script>
            @else
                <div class="thank-inner">
                    <h1>Thank you for booking your appointment!</h1>
                    <p>We've received your booking and will see you on your selected date and time.</p>
                    <a href="{{ route('coaching.upcoming') }}" class="btn-view">VIEW APPOINTMENTS</a>
                </div>
            @endif
        </div>
    </div>
@endsection
