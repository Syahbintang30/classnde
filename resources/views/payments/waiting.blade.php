@extends('layouts.app')

@section('title','Menunggu Pembayaran')

@section('content')
<div class="pay-wrapper">
    <div class="pay-card waiting">
        <div class="icon-wrap" aria-hidden="true">
            <div class="spinner"></div>
        </div>
        <h1 class="title">Menunggu Konfirmasi Pembayaran</h1>
        <p class="lead">Kami telah menerima detail transaksi Anda. Sistem sedang menunggu konfirmasi dari penyedia pembayaran. Halaman ini akan <strong>memperbarui otomatis</strong> atau Anda bisa cek manual.</p>

        @if(isset($transaction))
            <div class="info-grid">
                <div class="info-item"><span>Order ID</span><strong id="order-id">{{ $transaction->order_id }}</strong></div>
                <div class="info-item"><span>Status</span><strong id="txn-status" class="status-pill waiting">{{ strtoupper($transaction->status) }}</strong></div>
                <div class="info-item"><span>Jumlah</span><strong>Rp {{ number_format($transaction->amount ?? $transaction->original_amount ?? 0,0,',','.') }}</strong></div>
            </div>

            @php $resp = is_string($transaction->midtrans_response ?? null) ? json_decode($transaction->midtrans_response, true) : (array) ($transaction->midtrans_response ?? []); @endphp
            @if(!empty($resp) && ( !empty($resp['va_numbers']) || !empty($resp['permata_va_number']) || !empty($resp['payment_type']) || !empty($resp['payment_code']) ))
                <div class="pay-instructions">
                    <div class="instr-head">Instruksi Pembayaran</div>
                    @if(!empty($resp['payment_type']))<div class="instr-row">Metode: <strong>{{ strtoupper($resp['payment_type']) }}</strong></div>@endif
                    @if(!empty($resp['permata_va_number']))<div class="instr-row">Permata VA: <strong>{{ $resp['permata_va_number'] }}</strong></div>@endif
                    @if(!empty($resp['va_numbers']) && is_array($resp['va_numbers']))
                        @foreach($resp['va_numbers'] as $va)
                            <div class="instr-row">{{ strtoupper($va['bank']) }} VA: <strong>{{ $va['va_number'] }}</strong></div>
                        @endforeach
                    @endif
                    @if(!empty($resp['payment_code']))<div class="instr-row">Kode Pembayaran: <strong>{{ $resp['payment_code'] }}</strong></div>@endif
                    @if(!empty($resp['actions']) && is_array($resp['actions']))
                        @foreach($resp['actions'] as $act)
                            @if(!empty($act['url']))<a target="_blank" href="{{ $act['url'] }}" class="btn-outline small" style="margin-top:8px">Buka Instruksi Lengkap</a>@endif
                        @endforeach
                    @endif
                </div>
            @endif
        @endif

        <div class="actions">
            <button id="check-status" class="btn-primary">Cek Status Sekarang</button>
            <a href="{{ route('registerclass') }}" class="btn-outline">Kembali ke Beranda</a>
        </div>
        <div id="status-message" class="hint" aria-live="polite"></div>
        <div class="hint" style="margin-top:14px">Jika status belum berubah setelah Anda menyelesaikan pembayaran, tunggu beberapa detik atau klik tombol cek status.</div>
    </div>
</div>

<style>
    .pay-wrapper{min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px;background:#000;color:#fff}
    .pay-card{width:100%;max-width:760px;padding:42px 46px;border:1px solid #151515;border-radius:18px;background:linear-gradient(180deg,#0c0c0c,#050505);box-shadow:0 10px 40px rgba(0,0,0,.55);text-align:center;position:relative;overflow:hidden}
    .pay-card.waiting:before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 20%,rgba(255,255,255,0.05),transparent 60%);pointer-events:none}
    .icon-wrap{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 26px;background:rgba(255,255,255,0.06)}
    .spinner{width:36px;height:36px;border:4px solid rgba(255,255,255,0.18);border-top-color:#fff;border-radius:50%;animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .title{margin:0 0 14px;font-size:28px;font-weight:700;letter-spacing:.4px}
    .lead{margin:0 auto 24px;max-width:560px;font-size:15px;line-height:1.6;opacity:.9}
    .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin:10px 0 24px}
    .info-item{padding:14px 16px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);border-radius:10px;text-align:left}
    .info-item span{display:block;font-size:11px;letter-spacing:.5px;opacity:.55;text-transform:uppercase;margin-bottom:4px}
    .status-pill{display:inline-block;padding:4px 10px;font-size:11px;border-radius:40px;background:rgba(255,255,255,0.08);letter-spacing:.5px}
    .status-pill.waiting{background:rgba(250,204,21,0.15);color:#facc15}
    .pay-instructions{border:1px dashed rgba(255,255,255,0.12);padding:18px 20px;border-radius:12px;background:rgba(255,255,255,0.03);text-align:left;margin:0 0 26px}
    .instr-head{font-weight:600;margin-bottom:10px;letter-spacing:.5px;font-size:13px}
    .instr-row{font-size:14px;margin-bottom:4px}
    .actions{display:flex;flex-wrap:wrap;gap:14px;justify-content:center;margin-top:4px}
    .btn-primary,.btn-outline{padding:13px 26px;border-radius:12px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:.18s ease;border:1px solid transparent;cursor:pointer}
    .btn-primary{background:#fff;color:#000}
    .btn-primary:hover{background:#eaeaea}
    .btn-outline{background:transparent;color:#fff;border-color:rgba(255,255,255,0.22)}
    .btn-outline:hover{background:rgba(255,255,255,0.12)}
    .btn-outline.small{padding:8px 16px;font-size:12px}
    .hint{font-size:12px;line-height:1.55;opacity:.65}
    #status-message{margin-top:18px}
    @media (max-width:640px){.pay-card{padding:34px 24px}.title{font-size:24px}.info-grid{grid-template-columns:repeat(auto-fit,minmax(140px,1fr))}}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('check-status');
    const msg = document.getElementById('status-message');
    const statusEl = document.getElementById('txn-status');
    const orderId = '{{ isset($transaction) ? $transaction->order_id : (request()->query('order_id') ?: '') }}';
    let polling = true;
    let attempt = 0;
    const maxAttempts = 60; // ~5 min if interval escalates
    let interval = 3000;

        async function checkStatus(manual=false){
        if(!orderId) return;
        if(manual){ attempt = 0; interval = 3000; }
        try {
            msg.textContent = manual ? 'Memeriksa status...' : 'Memeriksa...';
            const res = await fetch('{{ route('payments.status') }}?order_id=' + encodeURIComponent(orderId));
            const j = await res.json();
            const rawStatus = (j.status || (j.transaction ? j.transaction.status : null) || 'unknown');
            if(statusEl) statusEl.textContent = rawStatus.toUpperCase();
            msg.textContent = 'Status: ' + rawStatus;
            if(['settlement','capture','success'].includes(String(rawStatus).toLowerCase())){
                polling = false;
                msg.textContent = 'Pembayaran terkonfirmasi. Mengarahkan...';
                    // if autologin token present (guest flow), redirect via autologin endpoint
                    if(j.autologin_token){
                        setTimeout(function(){ window.location.href = '/payments/autologin?token=' + encodeURIComponent(j.autologin_token) + '&order_id=' + encodeURIComponent(orderId); }, 600);
                    } else {
                        setTimeout(function(){ window.location.href = '/payments/thankyou?order_id=' + encodeURIComponent(orderId); }, 800);
                    }
                return;
            }
        } catch(e){ msg.textContent = 'Gagal memeriksa (' + (e.message||e) + ')'; }
    }

    async function loop(){
        if(!polling) return;
        attempt++;
        await checkStatus(false);
        if(attempt >= maxAttempts){ polling=false; msg.textContent += ' (Berhenti otomatis)'; return; }
        // backoff sampai 10s
        interval = Math.min(10000, interval + 1000);
        setTimeout(loop, interval);
    }

    if(btn){ btn.addEventListener('click', ()=>checkStatus(true)); }
    loop();
});
</script>
@endpush
