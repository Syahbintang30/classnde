@extends('layouts.app')

@section('content')
<div class="container py-8">
    <h1 class="text-2xl font-bold mb-4">Pembayaran Berhasil</h1>

    <p class="mb-4">Terima kasih — pembayaran Anda telah dicatat.</p>

    @if(isset($transaction))
        <div class="bg-white shadow rounded p-4" id="txn-card" data-order-id="{{ $transaction->order_id }}">
            <p><strong>Order ID:</strong> <span id="txn-order">{{ $transaction->order_id }}</span></p>
            <p><strong>Jumlah:</strong> <span id="txn-amount">{{ number_format($transaction->amount,0,',','.') }}</span></p>
            <p><strong>Status:</strong> <span id="txn-status">{{ $transaction->status }}</span></p>
            @if($transaction->package_id)
                <p><strong>Package ID:</strong> <span id="txn-package">{{ $transaction->package_id }}</span></p>
            @endif
            <div style="margin-top:12px;display:flex;gap:8px;align-items:center">
                <button id="refresh-status" class="btn">Cek status ulang</button>
                <span id="refresh-hint" style="color:rgba(0,0,0,0.6)">Auto-refresh setiap 30s (hingga 10x)</span>
            </div>
            <div id="status-message" style="margin-top:10px;color:#333"></div>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('registerclass') }}" class="btn btn-primary">Kembali ke Beranda</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const card = document.getElementById('txn-card');
    if(!card) return;
    const orderId = card.dataset.orderId;
    const statusEl = document.getElementById('txn-status');
    const msgEl = document.getElementById('status-message');
    const refreshBtn = document.getElementById('refresh-status');

    let attempts = 0, maxAttempts = 10;

    async function fetchStatus(){
        if(!orderId) return;
        try{
            const res = await fetch('{{ route('payments.status') }}?order_id=' + encodeURIComponent(orderId));
            if(!res.ok){
                const body = await res.json().catch(()=>null);
                msgEl.textContent = 'Gagal mengambil status: ' + (body && body.error ? body.error : res.statusText);
                return;
            }
            const j = await res.json();
            if(j.status) statusEl.textContent = j.status;
            if(j.transaction && j.transaction.status) statusEl.textContent = j.transaction.status;
            if(['settlement','capture','success'].includes(String(j.status || j.transaction?.status).toLowerCase())){
                msgEl.textContent = 'Pembayaran telah berhasil.';
                return;
            }
            msgEl.textContent = 'Status saat ini: ' + (j.status || j.transaction?.status || 'unknown');
        }catch(e){
            msgEl.textContent = 'Error: ' + (e.message || e);
        }
    }

    refreshBtn.addEventListener('click', async function(){
        attempts = 0; // reset attempts when user triggers
        await fetchStatus();
    });

    // auto poll
    const interval = setInterval(async function(){
        attempts++;
        if(attempts > maxAttempts) { clearInterval(interval); return; }
        await fetchStatus();
    }, 30000);
});
</script>
@endpush
