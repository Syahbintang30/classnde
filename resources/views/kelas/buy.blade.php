@extends('layouts.app')

@section('title', 'Register Class')

@section('content')
<style>
/* Responsive tweaks for /registerclass (keeps existing visual UI but adapts layout on smaller screens)
     We use specific selectors and !important to override inline styles only where necessary. */

/* make container centered and cap width on large screens */
.container { box-sizing: border-box; max-width: 1180px; margin: 0 auto; }

/* Desktop / tablet breakpoint adjustments */
@media (max-width: 1024px) {
    .container { padding: 28px 24px !important; }
    /* the main two-column wrapper: force wrapping and smaller column widths */
    .container > div[style*="display:flex"] { flex-wrap: wrap !important; gap: 18px !important; }
    /* left content (packages) becomes a bit narrower than full; allow shrinking */
    .container > div[style*="display:flex"] > div[style*="flex:1"] { max-width: 66% !important; width: 100% !important; }
    /* right column (form) becomes narrower */
    .container > div[style*="display:flex"] > div[style*="width:460px"] { width: 32% !important; }
    .packages-grid { padding-left: 8px !important; padding-right: 8px !important; }
}

/* Mobile - stack columns vertically */
@media (max-width: 768px) {
    .container { padding: 20px 16px !important; }
    .container > div[style*="display:flex"] { flex-direction: column !important; align-items: stretch !important; }
    .container > div[style*="display:flex"] > div[style*="flex:1"] { order: 1 !important; width: 100% !important; max-width: none !important; }
    .container > div[style*="display:flex"] > div[style*="width:460px"] { order: 2 !important; width: 100% !important; }

    /* packages: show stacked cards on mobile (keeps card look) */
    .packages-grid { display: flex !important; flex-direction: column !important; gap: 12px !important; overflow-x: visible !important; -webkit-overflow-scrolling: auto !important; padding-left: 6px !important; padding-right: 6px !important; }
    .class-card { width: 100% !important; min-width: 0 !important; }
    .class-card img { width: 100% !important; height: auto !important; max-height: 320px !important; object-fit: cover !important; }

    /* selected package preview: stack content */
    #selected_package_preview { display: flex !important; flex-direction: column !important; align-items: flex-start !important; gap: 8px !important; }
    #selected_package_preview > div:last-child { width: 100% !important; }

    /* shrink step indicator on small screens */
    .steps { max-width: 520px !important; padding: 0 6px !important; }
}

/* Small phones */
@media (max-width: 480px) {
    .container { padding: 16px 12px !important; }
    h2 { font-size: 18px !important; }
    .class-card img { max-height: 220px !important; }
    input, textarea, select, button { font-size: 15px !important; }
    .pkg-description, .pkg-benefits-list { font-size: 13px !important; }
}

</style>
<!-- Steps indicator: info -> payment -> done -->
<div style="display:flex;justify-content:center;padding-top:18px;">
    <div class="steps" role="tablist" aria-label="Booking steps" style="display:flex;align-items:center;gap:12px;max-width:720px;width:100%;justify-content:center;">
        <div class="step active" aria-current="step" title="Info"><i class="fa fa-info" aria-hidden="true"></i><span class="sr-only">Info</span></div>
        <div class="line" aria-hidden="true"></div>
    <div class="step" title="Payment"><i class="fa-solid fa-credit-card" aria-hidden="true"></i><span class="sr-only">Payment</span></div>
        <div class="line" aria-hidden="true"></div>
        <div class="step" title="Done"><i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">Done</span></div>
    </div>
</div>

<div class="container" style="padding:40px 60px;color:#fff">
    <div style="display:flex;gap:40px;align-items:flex-start;overflow:visible;">
        <!-- Left: Class selection -->
    <div style="flex:1;max-width:680px;overflow:visible;">
            <h2 style="font-size:20px;margin-bottom:12px">Start Your Guitar Journey</h2>
            <p style="opacity:0.7;margin-bottom:18px">From basic chords to improvisation, start your journey with our expert-led classes.</p>

            <div class="packages-grid" style="display:flex;gap:20px;align-items:stretch;overflow-x:auto;-webkit-overflow-scrolling:touch;padding-top:24px;padding-bottom:8px;padding-left:12px;padding-right:12px;">
                @foreach($packages as $pkg)
                    <div class="class-card" data-package-id="{{ $pkg->id }}" data-package-price="{{ $pkg->price }}" data-package-slug="{{ $pkg->slug }}" style="flex:1;border:1px solid rgba(255,255,255,0.06);padding:12px;border-radius:8px;background:#0b0b0b;transition:transform .18s ease, box-shadow .18s ease;cursor:pointer;">
                        <div style="overflow:hidden;border-radius:6px;">
                            @php
                                // prefer admin-uploaded image stored on the public disk; fallback to bundled static pictures
                                if (!empty($pkg->image)) {
                                    $imgSrc = asset('storage/' . $pkg->image);
                                } else {
                                    $img = 'intermediate';
                                    if ($pkg->slug == 'beginner') $img = 'beginner';
                                    if ($pkg->slug == config('coaching.coaching_package_slug', 'coaching-ticket')) $img = 'coaching-ticket';
                                    $imgSrc = asset('pictures/' . $img . '.jpg');
                                }
                            @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $pkg->name }}" style="width:100%;height:220px;object-fit:cover;display:block;">
                        </div>
                        <h3 style="margin:12px 0 8px 0">{{ $pkg->name }}</h3>
                        @if(!empty($pkg->description))
                            <p class="pkg-description" style="margin:0 0 8px 0;color:#cfcfcf;font-size:13px;">{{ \Illuminate\Support\Str::limit($pkg->description, 160) }}</p>
                        @endif

                        <div style="font-size:13px;opacity:0.85">
                            <strong>Benefits :</strong>
                            @if(!empty($pkg->benefits))
                                @php
                                    // split benefits by newline and filter empty lines
                                    $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $pkg->benefits)));
                                @endphp
                                @if(count($lines))
                                    <ul class="pkg-benefits-list">
                                        @foreach($lines as $line)
                                            <li>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                {{-- fallback to legacy static content if benefits not set --}}
                                @if($pkg->slug == 'beginner')
                                    <ul class="pkg-benefits-list">
                                        <li>Understand the parts of the guitar and their functions.</li>
                                        <li>Learn how to tune your guitar properly.</li>
                                        <li>Master basic chords (C, G, D, Am, Em).</li>
                                        <li>Practice simple strumming patterns.</li>
                                    </ul>
                                @elseif($pkg->slug == config('coaching.coaching_package_slug', 'coaching-ticket'))
                                    <ul class="pkg-benefits-list">
                                        <li>One free coaching ticket redeemable for a live coaching session.</li>
                                        <li>Priority booking for coaching slots.</li>
                                        <li>Personalized feedback from a coach.</li>
                                    </ul>
                                @else
                                    <ul class="pkg-benefits-list">
                                        <li>Master barre chords and chord variations.</li>
                                        <li>Learn the basics of fingerstyle playing.</li>
                                        <li>Use scales for improvisation.</li>
                                        <li>Rhythm and syncopation.</li>
                                        <li>Perform songs with your own interpretation.</li>
                                    </ul>
                                @endif
                            @endif
                        </div>
                        <div style="margin-top:8px;font-weight:700">Rp <span class="pkg-price">{{ number_format($pkg->price,0,',','.') }}</span></div>
                        @if(!empty($pkg->slug) && $pkg->slug === 'upgrade-intermediate')
                            @php
                                $beginner = \App\Models\Package::where('slug','beginner')->first();
                                $intermediate = \App\Models\Package::where('slug','intermediate')->first();
                            @endphp
                            <div style="margin-top:8px;font-size:13px;opacity:0.85">Catatan: Hanya untuk pemilik paket <strong>Beginner</strong>.</div>
                            @if($beginner && $intermediate)
                                <div style="margin-top:6px;font-size:13px;opacity:0.85">Harga Beginner: Rp {{ number_format($beginner->price,0,',','.') }} &nbsp;•&nbsp; Harga Intermediate: Rp {{ number_format($intermediate->price,0,',','.') }}</div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Registration or Purchase column -->
        <div style="width:460px;">
            <div id="package_validation" style="display:none;color:#ffb3b3;font-size:13px;margin-bottom:10px;"></div>
            <div id="pay_error" style="display:none;color:#ffb3b3;font-size:13px;margin-bottom:10px;"></div>
            <!-- ensure a global package qty hidden input exists for both guest and logged-in flows -->
            <input type="hidden" name="package_qty" value="1" id="selected_package_qty_input" />
                @guest
            <h2 style="font-size:20px;margin-bottom:12px">Buat Akun untuk Melanjutkan</h2>
            <p style="opacity:0.7;margin-bottom:12px">Pilih kelas di kiri, lalu buat akun atau login untuk melanjutkan ke pembayaran yang aman.</p>

            <!-- hidden values for selection (kept for JS compatibility) -->
            <input type="hidden" name="selected_package" value="" id="selected_package_input" />
            <input type="hidden" name="selected_package_price" value="" id="selected_package_price_input" />
            <input type="hidden" name="referral" id="hidden_referral_input" value="{{ old('referral') ?? session('referral') ?? '' }}" />

            <div style="margin-bottom:12px">
                <label style="display:block;margin-bottom:6px">Kode Referral (opsional)</label>
                <input id="referral_code_input" name="referral" value="{{ old('referral') ?? session('referral') ?? '' }}" placeholder="Masukkan kode referral atau kosongkan" style="width:100%;padding:12px;background:transparent;border:1px solid #333;color:#fff !important;border-radius:4px;" />
                @error('referral') <div style="color:#ffb3b3;margin-top:6px;font-size:13px">{{ $message }}</div> @enderror
                <div id="referral_hint" style="margin-top:6px;color:rgba(255,255,255,0.6);font-size:13px">Jika Anda punya kode referral, masukkan untuk mendapatkan diskon.</div>
            </div>

            <!-- Selected package preview for guests -->
            <div id="selected_package_preview" style="margin-bottom:14px;padding:12px;border-radius:8px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:13px;opacity:0.8">Kelas dipilih</div>
                    <div id="selected_package_name" style="font-weight:700;margin-top:4px">-</div>
                    <div id="selected_package_price_display" style="font-size:13px;opacity:0.85;margin-top:4px">Rp -</div>
                    <div id="selected_package_original_price_display" style="font-size:12px;opacity:0.6;margin-top:6px;display:none">Harga asli: Rp -</div>
                    <div id="selected_package_discount_display" style="font-size:12px;color:#b8f0c6;margin-top:6px;display:none">Diskon referral: -</div>
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,0.6)">Klik kartu lain untuk ganti kelas</div>
            </div>

            <div style="text-align:right;display:flex;gap:10px;justify-content:flex-end">
                <a id="guest_register_btn" href="{{ route('register') }}" style="background:#fff;color:#000;padding:10px 20px;border-radius:24px;font-weight:700;text-decoration:none">DAFTAR</a>
                <a id="guest_login_btn" href="{{ route('login') }}" style="background:transparent;border:1px solid #fff;color:#fff;padding:10px 20px;border-radius:24px;font-weight:700;text-decoration:none">LOGIN</a>
            </div>
                @else
            <h2 style="font-size:20px;margin-bottom:12px">Personal Details</h2>
            <p style="opacity:0.7;margin-bottom:12px">You're logged in. Click below to continue to the secure payment step.</p>

            <div style="text-align:right;margin-bottom:12px">
                {{-- link updated by JS to include selected package id as query param; guard when no lesson exists --}}
                @php $paymentBase = isset($lesson) && $lesson ? route('kelas.payment', $lesson->id) : null; @endphp
                @if($paymentBase)
                    <a id="continue_payment_btn" href="{{ $paymentBase }}" style="background:#fff;color:#000;padding:10px 26px;border-radius:24px;font-weight:700;border:none;display:inline-block;text-decoration:none;">CONTINUE TO PAYMENT</a>
                @else
                    <div style="padding:10px 14px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);font-size:13px;color:#ffd9d9;">Belum ada materi tersedia untuk pembayaran. Silakan kembali lagi nanti.</div>
                @endif
            </div>

            <!-- Logged-in selected package preview + qty (mirrors guest preview) -->
            <div id="selected_package_preview_logged" style="margin-top:12px;padding:12px;border-radius:8px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:13px;opacity:0.8">Selected package</div>
                    <div id="selected_package_name_logged" style="font-weight:700;margin-top:4px">-</div>
                    <div id="selected_package_price_display_logged" style="font-size:13px;opacity:0.85;margin-top:4px">Rp -</div>
                    <div id="selected_package_qty_container_logged" style="margin-top:8px;display:none;align-items:center;gap:8px;font-size:13px">
                        <label style="opacity:0.8;margin-right:8px">Quantity</label>
                        <button type="button" id="qty_decrease_logged" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:#fff;padding:6px 8px;border-radius:6px">-</button>
                        <span id="selected_package_qty_display_logged" style="min-width:28px;display:inline-block;text-align:center">1</span>
                        <button type="button" id="qty_increase_logged" style="background:transparent;border:1px solid rgba(255,255,255,0.06);color:#fff;padding:6px 8px;border-radius:6px">+</button>
                    </div>
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,0.6)">Change package by clicking a card on the left</div>
            </div>
            </div>
            @endguest
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* typography */
    body { font-family: 'Inter', Arial, sans-serif; }
    h2 { font-weight:700; }
    .class-card h3 { font-size:18px; margin-bottom:6px; }

    /* hover/selection effects */
    .class-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 12px 30px rgba(0,0,0,0.6); }
    /* make card positionable for glow pseudo-element */
    .class-card { position: relative; }
    /* visible selected state with brighter outline and lift */
    .class-card.selected {
        outline: 2px solid rgba(255,255,255,0.12);
        box-shadow: 0 20px 50px rgba(0,0,0,0.65), 0 0 18px rgba(255,255,255,0.08);
        transform: translateY(-8px);
        z-index: 10001;
        filter: brightness(1.04);
    }
    /* stronger glow for selected card - adds soft outer halo */
    .class-card.glow {
        border:1px solid rgba(255,255,255,0.18);
        box-shadow: 0 8px 28px rgba(255,255,255,0.08), 0 24px 60px rgba(0,0,0,0.6), 0 0 22px rgba(255,255,255,0.16);
        transition: box-shadow .18s ease, transform .18s ease, filter .18s ease;
        z-index: 9999; /* ensure glow is rendered above neighboring cards */
    }
    /* outer halo using pseudo-element for a smoother glow */
    .class-card.glow::after {
        content: '';
        position: absolute;
        left: -10px; right: -10px; top: -8px; bottom: -8px;
        border-radius: 10px;
        pointer-events: none;
        box-shadow: 0 0 36px rgba(255,255,255,0.16);
        opacity: 0.98;
        z-index: 9998;
    }

    input, textarea { font-family:inherit; }

    /* progress indicator */
    /* legacy buy-progress kept for compatibility (hidden when using .steps) */
    .buy-progress { position:relative; display:none; }
    .buy-progress .progress-line { flex:1;height:2px;background:rgba(255,255,255,0.06);border-radius:2px; }
    .buy-progress .circle { width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:18px }
    .buy-progress .circle.active { background:transparent;border-color:#fff;color:#fff }

    /* New steps component */
    .steps { display:flex;align-items:center;gap:12px; }
    .step { width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.12);background:transparent;color:#fff;font-size:20px;transition:transform .12s ease, box-shadow .12s ease, background .12s ease; }
    .step.active { background:#fff;color:#000;border-color:#fff; box-shadow:0 8px 20px rgba(0,0,0,0.45); transform: translateY(-4px); }
    .steps .line { flex:1;height:3px;background:rgba(255,255,255,0.06);border-radius:2px; }
    .step i { font-size:20px; line-height:1; }

    @media (max-width: 600px) {
        .step { width:44px;height:44px;font-size:16px }
        .step i { font-size:16px }
    }
    /* package card responsive text */
    /* allow vertical overflow so outer halo/glow isn't clipped while keeping horizontal scroll */
    .packages-grid { align-items:stretch; overflow-x:auto; overflow-y:visible; position:relative; z-index:1000; }
    /* make cards fixed-width so they sit horizontally and scroll if overflow */
    .class-card { display:flex;flex-direction:column; box-sizing:border-box; flex:0 0 320px; max-width:320px; min-width:260px; overflow:visible; }
    .class-card .pkg-description { word-break:break-word; white-space:normal; color:#cfcfcf }
    .pkg-benefits-list { margin:8px 0 0 18px; padding:0; }
    .pkg-benefits-list li { margin-bottom:6px; word-break:break-word; white-space:normal; overflow-wrap:break-word }

    /* Ensure images scale nicely while keeping aspect ratio */
    .class-card img { width:100%; height:220px; object-fit:cover; display:block }

    /* on medium and small screens keep horizontal scroll but adjust image height */
    @media (max-width:900px) {
        .class-card img { height:200px }
    }
    @media (max-width:600px) {
        .class-card img { height:180px }
        .pkg-benefits-list { font-size:13px }
    }
    /* hide scrollbar visually but keep scroll functionality */
    .packages-grid::-webkit-scrollbar { height:8px; }
    .packages-grid::-webkit-scrollbar-thumb { background: transparent; }
    .packages-grid { -ms-overflow-style: none; scrollbar-width: none; }
    .packages-grid::-webkit-scrollbar { display: none; }
    /* tidy packages container look */
    .packages-grid-container { padding:14px;border-radius:10px;background:rgba(0,0,0,0.18);border:1px solid rgba(255,255,255,0.03); }
    /* referral input visual tweaks */
    input#referral_code_input { color: #fff !important; }
    input#referral_code_input::placeholder { color: rgba(255,255,255,0.6); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const toggle = document.getElementById('toggle-password');
    const pwd = document.getElementById('register-password');
    const toggleConfirm = document.getElementById('toggle-password-confirm');
    const pwdConfirm = document.getElementById('register-password-confirm');
    function wireToggle(btn, input){
        if(!btn || !input) return;
        
        // SVG icons
        var eye = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        var eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.7 21.7 0 0 1 5-5"></path><path d="M1 1l22 22"></path></svg>';
        
        btn.addEventListener('click', function(){
            if(input.type === 'password'){
                input.type = 'text';
                btn.setAttribute('aria-label','Hide password');
                btn.title = 'Hide password';
                btn.innerHTML = eyeOff;
            } else {
                input.type = 'password';
                btn.setAttribute('aria-label','Show password');
                btn.title = 'Show password';
                btn.innerHTML = eye;
            }
        });
    }
    wireToggle(toggle, pwd);
    wireToggle(toggleConfirm, pwdConfirm);
});
</script>
@endpush

@push('scripts')
        <script>
    (function(){
    // lazy-query DOM elements after DOMContentLoaded to avoid null accesses
    let cards = [];
    let pkgHidden = null;
    let pkgPriceHidden = null;
    let pkgQtyHidden = null;
    let pkgIdInput = null;
    let pkgPriceInput = null;
    let previewName = null;
    let previewPrice = null;
    let previewBox = null;
    let validationBox = null;
    let qtyContainer = null;
    let qtyDisplay = null;
    let qtyInc = null;
    let qtyDec = null;
    // logged-in preview controls
    let qtyContainerLogged = null;
    let qtyDisplayLogged = null;
    let qtyIncLogged = null;
    let qtyDecLogged = null;
    let previewNameLogged = null;
    let previewPriceLogged = null;

    // coaching package slug from server-side config
    const coachingSlug = '{{ config('coaching.coaching_package_slug', 'coaching-ticket') }}';

        function applySelection(card){
            cards.forEach(x => { x.classList.remove('selected'); x.classList.remove('glow'); });
            card.classList.add('selected'); card.classList.add('glow');
            const pid = card.dataset.packageId || '';
            const pprice = card.dataset.packagePrice || '';
            if(pkgHidden) pkgHidden.value = pid;
            if(pkgPriceHidden) pkgPriceHidden.value = pprice;
            if(pkgQtyHidden) pkgQtyHidden.value = 1;
            if(pkgIdInput) pkgIdInput.value = pid;
            if(pkgPriceInput) pkgPriceInput.value = pprice;
            // update preview box
            if(previewName) previewName.textContent = card.querySelector('h3')?.textContent || '-';
            if(previewPrice) previewPrice.textContent = 'Rp ' + (card.querySelector('.pkg-price')?.textContent || '-');
            if(validationBox) { validationBox.style.display = 'none'; validationBox.textContent = ''; }
            // update continue to payment link for logged-in users
            const continueBtn = document.getElementById('continue_payment_btn');
            if (continueBtn) {
                const base = @json(isset($lesson) && $lesson ? route('kelas.payment', $lesson->id) : null);
                const pid = card.dataset.packageId || '';
                const qty = pkgQtyHidden ? parseInt(pkgQtyHidden.value || '1', 10) : 1;
                // append package_id, qty and referral as query params so server can pick them up for logged-in flow
                const ref = document.getElementById('referral_code_input') ? document.getElementById('referral_code_input').value.trim() : (document.getElementById('hidden_referral_input') ? document.getElementById('hidden_referral_input').value.trim() : '');
                if (base) {
                    const params = pid ? ('?package_id=' + encodeURIComponent(pid) + '&package_qty=' + encodeURIComponent(qty)) : '';
                    continueBtn.href = base + params + (ref ? (params ? '&referral=' + encodeURIComponent(ref) : '?referral=' + encodeURIComponent(ref)) : '');
                }
            }
            // update guest register/login deep links with selection and redirect target
            const regBtn = document.getElementById('guest_register_btn');
            const logBtn = document.getElementById('guest_login_btn');
            if (regBtn || logBtn) {
                const pid = card.dataset.packageId || '';
                const ref = document.getElementById('referral_code_input') ? document.getElementById('referral_code_input').value.trim() : '';
                const basePay = @json(isset($lesson) && $lesson ? route('kelas.payment', $lesson->id) : null);
                const payUrl = basePay ? basePay + (pid ? ('?package_id=' + encodeURIComponent(pid)) : '') : null;
                if (regBtn) {
                    let q = '';
                    if (pid || ref) {
                        q = '?';
                        if (pid) q += 'package_id=' + encodeURIComponent(pid);
                        if (ref) q += (pid ? '&' : '') + 'referral=' + encodeURIComponent(ref);
                    }
                    // add redirect_to param so after register we can continue to payment page
                    if (payUrl) regBtn.href = '{{ route('register') }}' + q + (q ? '&' : '?') + 'redirect_to=' + encodeURIComponent(payUrl);
                    else regBtn.href = '{{ route('register') }}' + q;
                }
                if (logBtn) {
                    if (payUrl) logBtn.href = '{{ route('login') }}' + '?redirect_to=' + encodeURIComponent(payUrl);
                    else logBtn.href = '{{ route('login') }}';
                }
            }
            // update logged-in preview display
            if (previewNameLogged) previewNameLogged.textContent = card.querySelector('h3')?.textContent || '-';
            if (previewPriceLogged) previewPriceLogged.textContent = 'Rp ' + ((parseInt(card.dataset.packagePrice||'0',10) * (pkgQtyHidden?parseInt(pkgQtyHidden.value||'1',10):1)).toLocaleString('id-ID'));
            if (card.dataset.packageSlug === coachingSlug) {
                if (qtyContainerLogged) qtyContainerLogged.style.display = 'flex';
                if (qtyDisplayLogged) qtyDisplayLogged.textContent = pkgQtyHidden ? String(parseInt(pkgQtyHidden.value||'1',10)) : '1';
            } else {
                if (qtyContainerLogged) qtyContainerLogged.style.display = 'none';
                if (qtyDisplayLogged) qtyDisplayLogged.textContent = '1';
            }
            // refresh referral discount preview when package changes (no page reload)
            try {
                const refInputEl = document.getElementById('referral_code_input') || document.getElementById('hidden_referral_input');
                const code = refInputEl ? (refInputEl.value||'').trim() : '';
                if (code) {
                    if (window.lastReferralCode && window.lastReferralCode === code && typeof window.updatePricesWithDiscount === 'function') {
                        window.updatePricesWithDiscount(window.lastReferralDiscountPercent || 0);
                    } else if (typeof window.validateReferralCode === 'function') {
                        // re-validate in background and update preview
                        window.validateReferralCode(code);
                    }
                } else if (typeof window.updatePricesWithDiscount === 'function') {
                    window.updatePricesWithDiscount(0);
                }
            } catch (e) { console.error('refresh referral preview', e); }
        }

        // initialize after DOM ready
        document.addEventListener('DOMContentLoaded', function(){
            cards = Array.from(document.querySelectorAll('.class-card'));
            pkgHidden = document.getElementById('selected_package_input');
            pkgPriceHidden = document.getElementById('selected_package_price_input');
            pkgQtyHidden = document.getElementById('selected_package_qty_input');
            pkgIdInput = document.getElementById('package_id_input');
            pkgPriceInput = document.getElementById('package_price_input');
            previewName = document.getElementById('selected_package_name');
            previewPrice = document.getElementById('selected_package_price_display');
            previewBox = document.getElementById('selected_package_preview');
            validationBox = document.getElementById('package_validation');
            qtyContainer = document.getElementById('selected_package_qty_container');
            qtyDisplay = document.getElementById('selected_package_qty_display');
            qtyInc = document.getElementById('qty_increase');
            qtyDec = document.getElementById('qty_decrease');
            qtyContainerLogged = document.getElementById('selected_package_qty_container_logged');
            qtyDisplayLogged = document.getElementById('selected_package_qty_display_logged');
            qtyIncLogged = document.getElementById('qty_increase_logged');
            qtyDecLogged = document.getElementById('qty_decrease_logged');
            previewNameLogged = document.getElementById('selected_package_name_logged');
            previewPriceLogged = document.getElementById('selected_package_price_display_logged');

            // pre-select by package passed in query or session pre_register; fallback to first card
            const preselectedPackageId = '{{ request()->input("package_id") ?? session("pre_register.package_id") ?? "" }}';
            let prePicked = null;
            if (preselectedPackageId) {
                prePicked = cards.find(c => (c.dataset.packageId || '') === String(preselectedPackageId));
            }
            if (prePicked) {
                applySelection(prePicked);
            } else if (cards.length) {
                applySelection(cards[0]);
            }

            cards.forEach(c => {
                c.addEventListener('click', () => applySelection(c));
                c.addEventListener('mouseenter', () => { c.style.zIndex = 5; });
                c.addEventListener('mouseleave', () => { c.style.zIndex = ''; });
            });

            // attach qty handlers now that elements exist
            if (qtyInc) {
                qtyInc.addEventListener('click', function(){
                    if (!pkgQtyHidden) return;
                    let q = parseInt(pkgQtyHidden.value || '1',10) || 1; q = q + 1; pkgQtyHidden.value = q; if (qtyDisplay) qtyDisplay.textContent = q;
                    const continueBtn = document.getElementById('continue_payment_btn');
                    if (continueBtn) {
                        const url = new URL(continueBtn.href, window.location.origin);
                        url.searchParams.set('package_qty', String(q));
                        continueBtn.href = url.toString();
                    }
                    if (qtyDisplayLogged) qtyDisplayLogged.textContent = String(q);
                    const sel = document.querySelector('.class-card.selected');
                    if (sel && previewPriceLogged) {
                        const unit = parseInt(sel.dataset.packagePrice || '0', 10) || 0;
                        previewPriceLogged.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID');
                    }
                });
            }
            if (qtyDec) {
                qtyDec.addEventListener('click', function(){
                    if (!pkgQtyHidden) return;
                    let q = parseInt(pkgQtyHidden.value || '1',10) || 1; q = Math.max(1, q - 1); pkgQtyHidden.value = q; if (qtyDisplay) qtyDisplay.textContent = q;
                    const continueBtn = document.getElementById('continue_payment_btn');
                    if (continueBtn) {
                        const url = new URL(continueBtn.href, window.location.origin);
                        url.searchParams.set('package_qty', String(q));
                        continueBtn.href = url.toString();
                    }
                    if (qtyDisplayLogged) qtyDisplayLogged.textContent = String(q);
                    const sel = document.querySelector('.class-card.selected');
                    if (sel && previewPriceLogged) {
                        const unit = parseInt(sel.dataset.packagePrice || '0', 10) || 0;
                        previewPriceLogged.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID');
                    }
                });
            }
            if (qtyIncLogged) {
                qtyIncLogged.addEventListener('click', function(){
                    if (!pkgQtyHidden) return;
                    let q = parseInt(pkgQtyHidden.value || '1',10) || 1; q = q + 1; pkgQtyHidden.value = q; if (qtyDisplay) qtyDisplay.textContent = q; if (qtyDisplayLogged) qtyDisplayLogged.textContent = String(q);
                    const continueBtn = document.getElementById('continue_payment_btn');
                    if (continueBtn) { const url = new URL(continueBtn.href, window.location.origin); url.searchParams.set('package_qty', String(q)); continueBtn.href = url.toString(); }
                    const sel = document.querySelector('.class-card.selected'); if (sel && previewPriceLogged) { const unit = parseInt(sel.dataset.packagePrice || '0', 10) || 0; previewPriceLogged.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID'); if (previewPrice) previewPrice.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID'); }
                });
            }
            if (qtyDecLogged) {
                qtyDecLogged.addEventListener('click', function(){
                    if (!pkgQtyHidden) return;
                    let q = parseInt(pkgQtyHidden.value || '1',10) || 1; q = Math.max(1, q - 1); pkgQtyHidden.value = q; if (qtyDisplay) qtyDisplay.textContent = q; if (qtyDisplayLogged) qtyDisplayLogged.textContent = String(q);
                    const continueBtn = document.getElementById('continue_payment_btn');
                    if (continueBtn) { const url = new URL(continueBtn.href, window.location.origin); url.searchParams.set('package_qty', String(q)); continueBtn.href = url.toString(); }
                    const sel = document.querySelector('.class-card.selected'); if (sel && previewPriceLogged) { const unit = parseInt(sel.dataset.packagePrice || '0', 10) || 0; previewPriceLogged.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID'); if (previewPrice) previewPrice.textContent = 'Rp ' + (unit * q).toLocaleString('id-ID'); }
                });
            }
        });

        // Quantity controls: only visible when coaching-ticket slug is selected
        function updateQtyControls(card){
            if (!card) return;
            const slug = card.dataset?.packageSlug || '';
            if (slug === coachingSlug) {
                if (qtyContainer) qtyContainer.style.display = 'flex';
            } else {
                if (qtyContainer) qtyContainer.style.display = 'none';
                if (pkgQtyHidden) pkgQtyHidden.value = 1;
                if (qtyDisplay) qtyDisplay.textContent = '1';
            }
        }

    // Quantity handlers are attached inside DOMContentLoaded initialization so elements exist

        // ensure qty controls updated when selection changes
        (function observeSelection(){
            const grid = document.querySelector('.packages-grid');
            if (!grid) return;
            const observer = new MutationObserver(() => {
                const sel = document.querySelector('.class-card.selected');
                if (sel) updateQtyControls(sel);
            });
            observer.observe(grid, { attributes:true, childList:true, subtree:true });
            // initial
            const initialSel = document.querySelector('.class-card.selected'); if(initialSel) updateQtyControls(initialSel);
        })();

    // form validation: ensure a package is selected before submit
    // avoid referencing $lesson (may be undefined on this page). Select forms by the hidden inputs we added.
    const guestForm = document.getElementById('selected_package_input')?.closest('form') || document.querySelector('form[action="{{ route('registerclass.register') }}"]');
    const purchaseForm = document.getElementById('package_id_input')?.closest('form') || null;
        function requirePackageOnSubmit(e){
            const hasPkg = (pkgHidden && pkgHidden.value) || (pkgIdInput && pkgIdInput.value);
            if(!hasPkg){
                e.preventDefault();
                if(validationBox){ validationBox.style.display = 'block'; validationBox.textContent = 'Please select a package before continuing.'; }
                // scroll preview into view on small screens
                previewBox?.scrollIntoView({behavior:'smooth', block:'center'});
                return false;
            }
            return true;
        }
        if(guestForm) guestForm.addEventListener('submit', requirePackageOnSubmit);
        if(purchaseForm) purchaseForm.addEventListener('submit', requirePackageOnSubmit);
        // client-side guard: ensure password and confirmation match before submit
        const registerForm = document.querySelector('form[action="{{ route('registerclass.register') }}"]');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e){
                const nameInput = document.getElementById('fullname_input');
                const nameError = document.getElementById('name_error');
                const pwd = document.getElementById('register-password');
                const pwdc = document.getElementById('register-password-confirm');
                const confirmError = document.getElementById('confirm_password_error');

                // validate name: required + max 255
                if (nameInput) {
                    const v = String(nameInput.value || '').trim();
                    if (!v) {
                        e.preventDefault();
                        if (nameError) { nameError.style.display = 'block'; nameError.textContent = 'Nama wajib diisi.'; }
                        nameInput.focus();
                        return false;
                    }
                    if (v.length > 255) {
                        e.preventDefault();
                        if (nameError) { nameError.style.display = 'block'; nameError.textContent = 'Nama terlalu panjang (maks 255 karakter).'; }
                        nameInput.focus();
                        return false;
                    }
                }
                if (nameError) { nameError.style.display = 'none'; nameError.textContent = ''; }

                // password confirmation
                if (pwd && pwdc && pwd.value !== pwdc.value) {
                    e.preventDefault();
                    if (confirmError) { confirmError.style.display = 'block'; confirmError.textContent = 'Konfirmasi password tidak cocok.'; }
                    pwd.focus();
                    return false;
                }
                if (confirmError) { confirmError.style.display = 'none'; confirmError.textContent = ''; }
                return true;
            });
        }
    })();
</script>
<script>
    (function(){
        // Midtrans integration + referral quick-validation
        const payBtn = document.getElementById('pay_button');
        const spinner = document.getElementById('pay_spinner');
        const errorBox = document.getElementById('pay_error');
        const selectedMethodText = document.getElementById('selected_method_text');
        let selectedMethod = null;

        const methodEls = Array.from(document.querySelectorAll('.payment-method') || []);
        methodEls.forEach(el => {
            el.addEventListener('click', () => {
                methodEls.forEach(x => x.style.outline='');
                el.style.outline = '2px solid rgba(255,255,255,0.12)';
                selectedMethod = el.dataset.method;
                if (selectedMethodText) selectedMethodText.textContent = el.textContent.trim();
            });
        });
        if(methodEls.length && !selectedMethod){ methodEls[0].click(); }

        // referral validation and client-side discounted price preview
        (function(){
            const input = document.getElementById('referral_code_input');
            const hint = document.getElementById('referral_hint');
            const discountPercent = {{ config('referral.discount_percent', 2) }};
            function updatePricesWithDiscount(pct){
                const sel = document.querySelector('.class-card.selected');
                if(!sel) return;
                const unit = parseInt(sel.dataset.packagePrice||'0',10) || 0;
                const qty = parseInt(document.getElementById('selected_package_qty_input')?.value || '1',10) || 1;
                const raw = unit * qty;
                const discounted = Math.round(raw * (100 - pct)/100);
                const disp = document.getElementById('selected_package_price_display');
                const orig = document.getElementById('selected_package_original_price_display');
                const disc = document.getElementById('selected_package_discount_display');
                if(disp) disp.textContent = 'Rp ' + discounted.toLocaleString('id-ID');
                if(orig) { orig.style.display = 'block'; orig.textContent = 'Harga asli: Rp ' + raw.toLocaleString('id-ID'); }
                if(pct && pct > 0){
                    const amount = raw - discounted;
                    if(disc){ disc.style.display = 'block'; disc.textContent = 'Diskon referral ('+pct+'%): - Rp ' + amount.toLocaleString('id-ID'); }
                } else {
                    if(disc){ disc.style.display = 'none'; disc.textContent = ''; }
                }
                // store last applied percent so other code can reuse without revalidating
                try { window.lastReferralDiscountPercent = pct; } catch(e){}
            }

            async function validate(code){
                if(!code) { if(hint) hint.textContent = 'Jika Anda punya kode referral, masukkan untuk mendapatkan diskon.'; updatePricesWithDiscount(0); return; }
                try{
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('/referral/validate', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify({ code }) });
                    const body = await res.json();
                    if(body && body.valid){
                        const pct = body.discount_percent || discountPercent;
                        if(hint) hint.textContent = 'Kode valid. Diskon referral: ' + pct + '%';
                        // persist last validated code and percent
                        try { window.lastReferralCode = code; window.lastReferralDiscountPercent = pct; } catch(e){}
                        updatePricesWithDiscount(pct);
                    } else {
                        // clear last validated state when invalid
                        try { window.lastReferralCode = null; window.lastReferralDiscountPercent = 0; } catch(e){}
                        if(hint) hint.textContent = 'Kode referral tidak valid.';
                        updatePricesWithDiscount(0);
                    }
                } catch (err) {
                    console.error('referral validate error', err);
                    if(hint) hint.textContent = 'Terjadi kesalahan saat memvalidasi kode referral';
                    updatePricesWithDiscount(0);
                }
            }

            // expose helpers globally so selection changes can refresh the price preview
            try { window.updatePricesWithDiscount = updatePricesWithDiscount; window.validateReferralCode = validate; } catch(e){}

            if(input){
                let timeout = null;
                input.addEventListener('input', function(e){
                    clearTimeout(timeout);
                    const code = e.target.value.trim();
                    timeout = setTimeout(()=> validate(code), 600);
                });
                // validate initial value
                if(input.value) validate(input.value.trim());
            }
        })();

        function loadSnapJs() {
            const isProd = {{ config('services.midtrans.is_production') ? 'true' : 'false' }};
            const clientKey = '{{ config('services.midtrans.client_key') }}';
            const src = isProd ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
            return new Promise((res, rej) => {
                if (window.snap) return res();
                const s = document.createElement('script');
                s.src = src;
                s.setAttribute('data-client-key', clientKey);
                s.onload = () => res();
                s.onerror = () => rej(new Error('Failed to load Midtrans snap.js'));
                document.head.appendChild(s);
            });
        }

        async function createSnapToken(payload) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('/api/midtrans/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            });
            const body = await res.json().catch(() => null);
            return { ok: res.ok, status: res.status, body };
        }

        async function onPayClick(e){
            e.preventDefault();
            if (errorBox) errorBox.style.display = 'none';
            if(!selectedMethod){ if(errorBox){ errorBox.textContent = 'Please choose a payment method.'; errorBox.style.display = 'block'; } return; }
            const pkgId = document.getElementById('package_id_input')?.value || document.getElementById('selected_package_input')?.value;
            const gross = document.getElementById('package_price_input')?.value || document.getElementById('selected_package_price')?.value;
            if(!pkgId || !gross){ if(errorBox){ errorBox.textContent = 'Please select a package.'; errorBox.style.display = 'block'; } return; }

            if(payBtn) payBtn.disabled = true; if(spinner) spinner.style.display = 'inline-block';
            try {
                const payload = {
                    gross_amount: parseInt(gross,10),
                    payment_method: selectedMethod,
                    package_id: pkgId,
                    package_qty: parseInt(document.getElementById('selected_package_qty_input')?.value || '1', 10) || 1,
                    package_unit_price: parseInt(document.querySelector('.class-card.selected')?.dataset.packagePrice || '0', 10) || 0,
                    referral: document.getElementById('referral_code_input') ? document.getElementById('referral_code_input').value : null
                };

                const tokenResp = await createSnapToken(payload);
                if(!tokenResp.ok){
                    const message = (tokenResp.body && (tokenResp.body.message || tokenResp.body.error)) || 'Server returned an error';
                    if(errorBox){ errorBox.textContent = 'Failed to create snap token: ' + message; errorBox.style.display = 'block'; }
                    return;
                }

                await loadSnapJs();
                const snapToken = tokenResp.body && tokenResp.body.snap_token ? tokenResp.body.snap_token : null;
                const serverOrderId = tokenResp.body && tokenResp.body.order_id ? tokenResp.body.order_id : null;
                if(!snapToken){ if(errorBox){ errorBox.textContent = 'No snap token returned from server.'; errorBox.style.display = 'block'; } return; }

                window.snap.pay(snapToken, {
                    onSuccess: function(result){
                        const q = serverOrderId ? ('?order_id=' + encodeURIComponent(serverOrderId)) : '';
                        window.location = '{{ route('payments.thankyou') }}' + q;
                    },
                    onPending: function(result){
                        const q = serverOrderId ? ('?order_id=' + encodeURIComponent(serverOrderId)) : '';
                        window.location = '{{ route('payments.thankyou') }}' + q;
                    },
                    onError: function(err){
                        const msg = (err && err.message) || 'Payment failed';
                        if(errorBox){ errorBox.textContent = 'Payment failed: ' + msg; errorBox.style.display = 'block'; }
                    }
                });

            } catch (err) {
                const msg = (err && err.message) || String(err);
                if(errorBox){ errorBox.textContent = 'Unexpected error: ' + msg; errorBox.style.display = 'block'; }
            } finally {
                if(payBtn) payBtn.disabled = false; if(spinner) spinner.style.display = 'none';
            }
        }

        if (payBtn) payBtn.addEventListener('click', onPayClick);
    })();
</script>
@endpush
