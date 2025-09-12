@extends('layouts.app')

@section('content')
<div class="container" style="max-width:900px;margin-top:24px">
    @if($hasPackage)
        <div class="steps" style="justify-content:center;margin-bottom:18px">
            <div class="step"><i class="fa fa-info"></i></div>
            <div class="line"></div>
            <div class="step active"><i class="fa fa-credit-card"></i></div>
            <div class="line"></div>
            <div class="step"><i class="fa fa-check"></i></div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Order Summary</h3>
                <p class="text-muted">Pastikan Anda sudah membeli salah satu package yang diperlukan sebelum membeli ticket coaching.</p>

                <div style="display:flex;gap:20px;align-items:flex-start">
                    <div style="flex:1">
                        <div style="padding:12px;border-radius:8px;background:#f7f7f7">
                            <div style="font-weight:700">Package</div>
                            <div id="pkgName" style="margin-top:6px">{{ $package ? $package->name : 'Not configured' }}</div>
                            <div id="pkgPrice" style="margin-top:8px;font-size:18px;font-weight:800">Rp {{ $package ? number_format($package->price,0,',','.') : '0' }}</div>
                        </div>

                        <div style="margin-top:12px;padding:12px;border-radius:8px;background:#fff;border:1px solid #eee">
                            <div style="font-weight:700">Schedule</div>
                            <div id="scheduleDisplay" style="margin-top:6px">{{ $scheduleDisplay ?? 'Belum memilih tanggal/jam' }}</div>
                        </div>
                    </div>

                    <div style="width:320px">
                        <div style="padding:12px;border-radius:8px;background:#fff;border:1px solid #eee">
                            <h4 style="margin-top:0">Payment</h4>
                            <div style="margin-top:8px">Total</div>
                            <div id="totalAmount" style="font-weight:800;font-size:20px;margin-top:6px">Rp {{ $package ? number_format($package->price,0,',','.') : '0' }}</div>

                            <div style="margin-top:12px">
                                @if(! $hasPackage)
                                    <div class="alert alert-warning">Anda belum memiliki package yang memenuhi syarat. Silakan beli package yang diperlukan terlebih dahulu.</div>
                                @endif
                                <button id="payBtn" class="btn btn-primary w-100" {{ $hasPackage ? '' : 'disabled' }}>Pay with Midtrans</button>
                            </div>

                            <div style="margin-top:8px;font-size:12px;color:#666">Harga dapat diubah oleh Admin di Admin → Packages.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div style="min-height:50vh;display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:48px 16px;">
            <h2 style="font-size:20px;font-weight:800;color:#fff;max-width:900px;">Pastikan Anda sudah membeli salah satu package yang diperlukan sebelum membeli ticket coaching.</h2>
            <a href="{{ url('/registerclass') }}" class="btn btn-primary" style="margin-top:24px;padding:12px 28px;border-radius:8px;font-weight:700;text-decoration:none;">Kembali ke Dashboard</a>
        </div>
    @endif
</div>

@endsection

@push('scripts')
@php
    $midtransHost = config('services.midtrans.is_production') ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    $midtransClientKey = $midtrans['client_key'] ?? config('services.midtrans.client_key') ?? '';
@endphp
<script src="{{ $midtransHost }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const payBtn = document.getElementById('payBtn');
    payBtn && payBtn.addEventListener('click', async function(){
        payBtn.disabled = true;
        // create order on server
        const schedule = '{{ $scheduleDisplay ?? '' }}';
        const packageId = {{ $package ? $package->id : 'null' }};
        try {
            const res = await fetch('{{ route('coaching.checkout.create') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ schedule: schedule, package_id: packageId })
            });
            const json = await res.json();
            if (! res.ok) {
                alert(json.error || 'Failed to create order');
                payBtn.disabled = false; return;
            }

            // Request snap token from existing midtrans endpoint
            const snapRes = await fetch('/api/midtrans/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: json.order_id, gross_amount: json.gross_amount, package_id: json.package_id })
            });
            const snapJson = await snapRes.json();
            if (! snapRes.ok) {
                alert(snapJson.error || 'Midtrans create failed'); payBtn.disabled = false; return;
            }

            const token = snapJson.snap_token || snapJson.raw?.token;
            if (! token) { alert('Midtrans token not returned'); payBtn.disabled = false; return; }

            window.snap.pay(token, {
                onSuccess: function(result){ window.location.href = '/coaching'; },
                onPending: function(result){ window.location.href = '/coaching'; },
                onError: function(err){ alert('Payment failed'); payBtn.disabled = false; }
            });

        } catch (e) {
            console.error(e); alert('Unexpected error'); payBtn.disabled = false;
        }
    });
});
</script>
@endpush
