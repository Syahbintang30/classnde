@extends('layouts.app')

@section('title', isset($package) && $package ? ($package->name . ' - One Step Away!') : 'Complete Your Payment')

@section('content')


<div style="display:flex;justify-content:center;padding-top:18px;">
    <div class="steps" role="tablist" aria-label="Booking steps" style="display:flex;align-items:center;gap:12px;max-width:720px;width:100%;justify-content:center;">
        <div class="step active" aria-current="step" title="Info"><i class="fa fa-info" aria-hidden="true"></i><span class="sr-only">Info</span></div>
        <div class="line" aria-hidden="true"></div>
        <div class="step" title="Payment"><i class="fa fa-credit-card" aria-hidden="true"></i><span class="sr-only">Payment</span></div>
        <div class="line" aria-hidden="true"></div>
        <div class="step" title="Done"><i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">Done</span></div>
    </div>
</div>

<div style="padding:40px;color:#fff;">
    <div style="display:flex;gap:40px;align-items:flex-start;max-width:1100px;margin:0 auto;">
        <!-- Left: Order Summary -->
        <div style="flex:1;max-width:360px;border:1px solid rgba(255,255,255,0.06);padding:20px;border-radius:6px;background:rgba(0,0,0,0.2)">
            <h3 style="margin-bottom:8px">Your Order is Ready</h3>
            <div style="display:flex;justify-content:space-between;margin:16px 0">
                <div>
                    <div style="font-weight:600">{{ $package ? $package->name : $lesson->title }}</div>
                    <div style="opacity:0.7;font-size:13px">
                        {{ $package ? 'Package' : 'Lifetime Access' }}
                        @if(!empty($order['item_details'][0]['quantity']) && $order['item_details'][0]['quantity'] > 1)
                            &middot; Qty: {{ $order['item_details'][0]['quantity'] }}
                        @endif
                    </div>
                </div>
                <div style="font-weight:700">Rp {{ number_format($order['gross_amount'],0,',','.') }}</div>
            </div>

            <div style="height:18px;border-top:1px solid rgba(255,255,255,0.03);margin:24px 0"></div>

            <div style="font-size:13px;opacity:0.85">Note</div>
            @if(isset($package) && $package && isset($package->slug) && $package->slug === 'coaching-ticket')
                <div style="margin:12px 0 32px 0">You're one step closer to achieving your goals with our professional coach</div>
            @else
                <div style="margin:12px 0 32px 0">Lifetime access to all materials. Learn without limits!</div>
            @endif

            <div style="display:flex;justify-content:space-between;font-weight:600">
                <div>Subtotal (original price):</div>
                <div>Rp {{ number_format($order['original_amount'] ?? $order['gross_amount'],0,',','.') }}</div>
            </div>
            @if(!empty($order['applied_referral_percent']) && $order['applied_referral_percent'] > 0)
                <div style="display:flex;justify-content:space-between;color:#b8f0c6;margin-top:8px;font-weight:600">
                    <div>Referral Discount ({{ $order['applied_referral_percent'] }}%):</div>
                    <div>- Rp {{ number_format( max(0, ($order['original_amount'] ?? $order['gross_amount']) - $order['gross_amount']),0,',','.') }}</div>
                </div>
                @if(!empty($order['referral_code']))
                    <div style="margin-top:6px;font-size:13px;color:rgba(255,255,255,0.75)">Referral code used: <strong>{{ $order['referral_code'] }}</strong></div>
                @endif
            @endif
            <div style="display:flex;justify-content:space-between;color:rgba(255,255,255,0.7);margin-top:8px">
                <div>Tax:</div>
                <div>Rp 0</div>
            </div>

            <div style="height:1px;background:rgba(255,255,255,0.03);margin:22px 0"></div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div style="font-weight:700">Total Payment:</div>
                <div style="font-weight:800; font-size: 1.1em;">Rp {{ number_format($order['gross_amount'],0,',','.') }}</div>
            </div>
        </div>

        <!-- Right: Payment options -->
        <div style="flex:1;">
            <h3 style="margin-bottom:12px">Choose Your Payment Method</h3>
            <div class="payment-grid-container" id="payment-methods-list" data-total="{{ $order['gross_amount'] }}" data-order-id="{{ $order['order_id'] }}">
            @foreach($methods as $m)
                <label class="payment-option" for="payment-{{ $m->id }}" aria-label="{{ $m->display_name }}">
                <input
                    type="radio"
                    name="payment_method"
                    id="payment-{{ $m->id }}"
                    value="{{ $m->name }}"
                    class="sr-only"
                    data-details="{{ $m->account_details }}"
                    data-name="{{ $m->display_name }}"
                >
                <div class="payment-option-visual" title="{{ $m->display_name }}">
                    @if($m->logo_url)
                    <img src="{{ asset($m->logo_url) }}" alt="{{ $m->display_name }}" class="payment-logo" />
                    @else
                    <div class="payment-logo-fallback">{{ strtoupper(mb_substr($m->display_name, 0, 1)) }}</div>
                    @endif
                </div>
                </label>
            @endforeach
            </div>
            <div id="payment-details-display" class="mt-4">
                 <!-- Trust Signal Text -->
                 <p style="font-size: 14px; opacity: 0.8; margin-top: 24px;">
                    <i class="fa fa-lock" aria-hidden="true"></i>
                    Rest assured, your transaction is 100% secure and processed by Midtrans. <strong>Payments are verified automatically</strong>, no need to upload proof of transfer.
                </p>
            </div>

            <!-- Voucher code form -->
            <div style="margin-top:18px;">
                <label style="font-size:13px;opacity:0.85;display:block">Have a voucher code?</label>
                <div style="display:flex;gap:8px;align-items:center;max-width:420px;">
                    <input id="voucher_code_input" type="text" class="form-control" placeholder="Enter voucher code" style="flex:1;padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,0.08);background:transparent;color:#fff" />
                    <button id="voucher_validate_btn" class="btn btn-outline-light" style="padding:8px 12px;border-radius:6px">Apply</button>
                </div>
                <div id="voucher_feedback" style="margin-top:8px;font-size:13px;color:#ffd">&nbsp;</div>
            </div>

            <div style="margin-top:24px; margin-bottom:18px">
                <button id="pay-button" style="background:#007bff;border-radius:999px;padding:14px 32px;color:#fff;border:none;font-weight:700; font-size: 16px; cursor:pointer; width: 100%; max-width: 300px;">PAY & Start Learning</button>
            </div>

            <div style="margin-top:36px">
                <form id="payment-complete-form" method="POST" action="{{ route('kelas.payment.complete', ['lesson' => $lesson->id]) }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order['order_id'] }}" />
                    <input type="hidden" name="midtrans_result" id="midtrans_result" value="" />
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Back button moved to page bottom -->
<div style="max-width:1100px;margin:12px auto 40px;display:flex;justify-content:flex-end;">
    <a href="{{ url()->previous() ?: route('registerclass') }}" style="color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,0.06);padding:8px 12px;border-radius:8px;background:rgba(255,255,255,0.02)">Back</a>
</div>

@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $midtrans['client_key'] }}"></script>
<script>
    // Use app modal component: inject content into modal container and open
    function showInAppModal(html){
        const container = document.getElementById('payment-modal-content');
        if (!container) return alert('Modal container not found');
        container.innerHTML = html;
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'payment-method-modal' }));
    }

    // legacy manual handlers removed — all flows now use Midtrans via the PAY button

    document.getElementById('pay-button').addEventListener('click', function(){
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (! selected) {
            alert('Please select a payment method first.');
            return;
        }

        const paymentMethod = selected.value;
    const payload = { order_id: '{{ $order['order_id'] }}', gross_amount: {{ $order['gross_amount'] }}, payment_method: paymentMethod };
    // include referral code so server can validate and apply referral discount
    payload.referral = '{{ $order['referral_code'] ?? '' }}';
    @if(isset($package))
            payload.package_id = {{ $package->id }};
            payload.package_qty = {{ request()->input('package_qty') ?: 1 }};
            payload.package_unit_price = {{ $package->price }};
            let pkgInput = document.createElement('input'); pkgInput.type='hidden'; pkgInput.name='package_id'; pkgInput.value='{{ $package->id }}'; document.getElementById('payment-complete-form').appendChild(pkgInput);
            let qtyInput = document.createElement('input'); qtyInput.type='hidden'; qtyInput.name='package_qty'; qtyInput.value='{{ request()->input('package_qty') ?: 1 }}'; document.getElementById('payment-complete-form').appendChild(qtyInput);
        @else
            // if package_id was passed as query param (logged-in user flow)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('package_id')) {
                payload.package_id = urlParams.get('package_id');
                payload.package_qty = parseInt(urlParams.get('package_qty') || '1', 10);
                // do not set package_unit_price here — let the server lookup canonical package price
            }
        @endif

        fetch("/api/midtrans/create", {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(json => {
            if (json.snap_token) {
                snap.pay(json.snap_token, {
                    onSuccess: function(result){ document.getElementById('midtrans_result').value = JSON.stringify(result); document.getElementById('payment-complete-form').submit(); },
                    onPending: function(result){ document.getElementById('midtrans_result').value = JSON.stringify(result); document.getElementById('payment-complete-form').submit(); },
                    onError: function(err){ alert('Payment Failed. Please try again.'); }
                });
            } else {
                alert('Failed to process payment. Please try again in a moment.');
            }
        }).catch(e => { console.error(e); alert('A network error occurred. Please check your connection and try again.'); });
    });

    // voucher validation and attach to payload
    let appliedVoucher = null;
    document.getElementById('voucher_validate_btn').addEventListener('click', function(e){
        e.preventDefault();
        const code = document.getElementById('voucher_code_input').value.trim();
        if (! code) { document.getElementById('voucher_feedback').innerText = 'Please enter a voucher code.'; return; }
        document.getElementById('voucher_feedback').innerText = 'Checking...';
        fetch('/vouchers/validate', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ code }) })
            .then(r => r.json()).then(json => {
                if (json.valid) {
                    appliedVoucher = { code: code, id: json.voucher_id, discount_percent: json.discount_percent };
                    document.getElementById('voucher_feedback').innerText = 'Voucher applied: ' + json.discount_percent + '% off';
                    document.getElementById('voucher_feedback').style.color = '#b8f0c6';
                } else {
                    appliedVoucher = null;
                    document.getElementById('voucher_feedback').innerText = json.message || 'Invalid voucher';
                    document.getElementById('voucher_feedback').style.color = '#f8d7da';
                }
            }).catch(e => { appliedVoucher = null; document.getElementById('voucher_feedback').innerText = 'Validation error'; document.getElementById('voucher_feedback').style.color = '#f8d7da'; });
    });

    // ensure voucher code (if applied) is included in midtrans payload by intercepting fetch call — simpler: append hidden input to payment form when pay is clicked
    document.getElementById('pay-button').addEventListener('click', function(){
        // before original click handler runs, attach voucher hidden inputs if voucher applied
        if (appliedVoucher) {
            // ensure we don't duplicate inputs
            const form = document.getElementById('payment-complete-form');
            if (! form.querySelector('input[name="voucher_code"]')) {
                const vi = document.createElement('input'); vi.type='hidden'; vi.name='voucher_code'; vi.value=appliedVoucher.code; form.appendChild(vi);
            }
            if (! form.querySelector('input[name="voucher_id"]')) {
                const vii = document.createElement('input'); vii.type='hidden'; vii.name='voucher_id'; vii.value=appliedVoucher.id; form.appendChild(vii);
            }
        }
    }, { once: true });
</script>
@endpush

@push('styles')
<style>
    .buy-progress { position:relative; }
    .buy-progress .progress-line { flex:1;height:2px;background:rgba(255,255,255,0.06);border-radius:2px; }
    .buy-progress .circle { width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:18px }
    .buy-progress .circle.active { background:transparent;border-color:#fff;color:#fff }

        /* New steps component */
    .steps { display:flex;align-items:center;gap:12px; }
    .step { width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:20px;transition:transform .12s ease, box-shadow .12s ease, background .12s ease; }
    .step.active { background:#fff;color:#000;border-color:#fff; box-shadow:0 8px 20px rgba(0,0,0,0.45); transform: translateY(-4px); }
    .steps .line { flex:1;height:3px;background:rgba(255,255,255,0.06);border-radius:2px; }
    .step i { font-size:20px; line-height:1; }

    /* Style tambahan untuk tombol bayar agar lebih menonjol */
    #pay-button:hover {
        background: #0056b3; /* Warna lebih gelap saat hover */
        transform: scale(1.02);
        transition: all 0.2s ease-in-out;
    }
</style>
@endpush

@section('modals')
    <x-modal name="payment-method-modal" focusable>
        <div id="payment-modal-content" class="p-4">
            <!-- dynamic content injected here -->
        </div>
    </x-modal>
@endsection
