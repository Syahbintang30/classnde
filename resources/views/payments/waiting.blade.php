@extends('layouts.app')

@section('content')
<div class="container py-8">
    <h1 class="text-2xl font-bold mb-4">Menunggu Pembayaran</h1>

    <div class="bg-white shadow rounded p-4">
        <p>Transaksi Anda sedang menunggu konfirmasi pembayaran. Silakan selesaikan pembayaran menggunakan instruksi pembayaran yang muncul di Midtrans.</p>

        @if(isset($transaction))
            <div style="margin-top:12px">
                <p><strong>Order ID:</strong> {{ $transaction->order_id }}</p>
                <p><strong>Status saat ini:</strong> <span id="txn-status">{{ $transaction->status }}</span></p>
                <p><strong>Jumlah:</strong> Rp {{ number_format($transaction->amount ?? $transaction->original_amount ?? 0,0,',','.') }}</p>
                @if(!empty($transaction->midtrans_response))
                    @php $resp = is_string($transaction->midtrans_response) ? json_decode($transaction->midtrans_response, true) : (array) $transaction->midtrans_response; @endphp
                    @if(!empty($resp['va_numbers']) || !empty($resp['permata_va_number']) || !empty($resp['payment_type']))
                        <div style="margin-top:10px;padding:10px;border:1px dashed rgba(0,0,0,0.06);border-radius:6px;background:#fafafa">
                            <div style="font-weight:600;margin-bottom:6px">Instruksi Pembayaran Midtrans</div>
                            @if(!empty($resp['payment_type']))
                                <div>Metode: <strong>{{ $resp['payment_type'] }}</strong></div>
                            @endif
                            @if(!empty($resp['permata_va_number']))
                                <div>Virtual Account (Permata): <strong>{{ $resp['permata_va_number'] }}</strong></div>
                            @endif
                            @if(!empty($resp['va_numbers']) && is_array($resp['va_numbers']))
                                @foreach($resp['va_numbers'] as $va)
                                    <div>{{ $va['bank'] }} VA: <strong>{{ $va['va_number'] }}</strong></div>
                                @endforeach
                            @endif
                            @if(!empty($resp['payment_code']))
                                <div>Kode pembayaran: <strong>{{ $resp['payment_code'] }}</strong></div>
                            @endif
                            @if(!empty($resp['actions']) && is_array($resp['actions']))
                                @foreach($resp['actions'] as $act)
                                    @if(!empty($act['url']))
                                        <div style="margin-top:8px"><a target="_blank" href="{{ $act['url'] }}" class="btn btn-outline-secondary">Buka instruksi pembayaran</a></div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    @endif
                @endif

                <div style="margin-top:12px">
                    <button id="check-status" class="btn">Cek status pembayaran</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary" style="margin-left:8px">Kembali</a>
                </div>

                <div id="status-message" style="margin-top:10px;color:#333"></div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('check-status');
    const msg = document.getElementById('status-message');
    const txnStatus = document.getElementById('txn-status');
    const orderId = '{{ isset($transaction) ? $transaction->order_id : (request()->query('order_id') ?: '') }}';
    if(!btn) return;
    btn.addEventListener('click', async function(){
        if(!orderId) return;
        msg.textContent = 'Memeriksa status...';
        try{
            const res = await fetch('{{ route('payments.status') }}?order_id=' + encodeURIComponent(orderId));
            if(!res.ok){ msg.textContent = 'Gagal memeriksa status: ' + res.status; return; }
            const j = await res.json();
            msg.textContent = 'Status saat ini: ' + (j.status || j.transaction?.status || 'unknown');
            if(txnStatus && (j.status || j.transaction?.status)) txnStatus.textContent = j.status || j.transaction.status;
            if(['settlement','capture','success'].includes(String(j.status || j.transaction?.status).toLowerCase())){
                // if settled, redirect to finish page to show success flow
                window.location = '{{ route('payments.finish') }}?order_id=' + encodeURIComponent(orderId);
            }
        }catch(e){ msg.textContent = 'Error: ' + (e.message || e); }
    });
});
</script>
@endpush
