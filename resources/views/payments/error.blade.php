@extends('layouts.app')

@section('title','Pembayaran Gagal')

@section('content')
<div class="pay-wrapper">
    <div class="pay-card error">
        <div class="icon-wrap" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <h1 class="title">Pembayaran Gagal / Belum Terselesaikan</h1>
        <p class="lead">{{ $message ?? 'Transaksi belum berhasil dikonfirmasi. Jika Anda sudah membayar, silakan coba cek status atau hubungi support.' }}</p>

        <div class="actions">
            <a href="{{ url()->previous() ?: route('registerclass') }}" class="btn-outline">Kembali</a>
            <a href="{{ route('registerclass') }}" class="btn-primary">Pilih Paket Lain</a>
            <a href="mailto:support@guitarclassbynde.id?subject=Support%20Pembayaran" class="btn-ghost">Hubungi Support</a>
        </div>
        <div class="hint">Jika Anda sudah melakukan transfer/scan QR namun status masih belum berubah setelah beberapa menit, kirimkan bukti pembayaran ke email support beserta <strong>Order ID</strong>.</div>
    </div>
</div>

<style>
    .pay-wrapper{min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px;background:#000;color:#fff}
    .pay-card{width:100%;max-width:640px;padding:40px;border:1px solid #151515;border-radius:16px;background:linear-gradient(180deg,#0b0b0b,#050505);box-shadow:0 10px 40px rgba(0,0,0,.6);text-align:center}
    .pay-card.error{border-color:rgba(239,68,68,0.3)}
    .icon-wrap{width:74px;height:74px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;background:rgba(239,68,68,0.12);color:#ef4444}
    .icon-wrap svg{width:36px;height:36px}
    .title{margin:0 0 12px;font-size:26px;font-weight:700;letter-spacing:.5px}
    .lead{margin:0 auto 24px;max-width:520px;font-size:15px;line-height:1.6;opacity:.9}
    .actions{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-bottom:24px}
    .btn-primary,.btn-outline,.btn-ghost{padding:12px 22px;border-radius:10px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:.18s ease;border:1px solid transparent;cursor:pointer}
    .btn-primary{background:#fff;color:#000}
    .btn-primary:hover{background:#e9e9e9}
    .btn-outline{background:transparent;color:#fff;border-color:rgba(255,255,255,0.2)}
    .btn-outline:hover{background:rgba(255,255,255,0.12)}
    .btn-ghost{background:rgba(255,255,255,0.06);color:#fff}
    .btn-ghost:hover{background:rgba(255,255,255,0.12)}
    .hint{font-size:12px;line-height:1.5;opacity:.65}
    @media (max-width:640px){.pay-card{padding:30px 22px}.title{font-size:22px}.icon-wrap{width:64px;height:64px;margin-bottom:18px}.icon-wrap svg{width:30px;height:30px}}
</style>
@endsection
