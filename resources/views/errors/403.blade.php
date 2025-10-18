@extends('layouts.app')

@section('title', 'Session expired')

@section('content')
    <div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 16px">
        <div style="max-width:760px;text-align:center;color:#fff;padding:28px;border-radius:8px;">
            <h1 style="font-size:28px;margin-bottom:8px">Sesi Anda berakhir atau terdeteksi masalah keamanan sesi</h1>
            <p style="opacity:0.85;margin-bottom:18px;font-size:16px">Kami mendeteksi perubahan yang membuat sesi Anda tidak lagi aman (mis. perubahan jaringan/IP atau sudah login di perangkat lain). Untuk melindungi akun Anda, Anda telah dikeluarkan.</p>

            <div style="text-align:left;background:rgba(255,255,255,0.02);padding:16px;border-radius:6px; margin-bottom:18px">
                <strong>Langkah cepat yang bisa Anda lakukan</strong>
                <ol style="margin-top:8px;padding-left:18px;">
                    <li>Silakan <a href="{{ route('login') }}">masuk kembali</a> untuk melanjutkan aktivitas.</li>
                    <li>Jika Anda sedang menggunakan jaringan/ VPN publik, coba beralih ke jaringan pribadi.</li>
                    <li>Jika Anda mengakses dari perangkat lain, pastikan Anda dan tim tidak saling menggunakan akun yang sama secara bersamaan.</li>
                </ol>
            </div>

            <div style="display:flex;gap:10px;justify-content:center;margin-bottom:12px">
                <a href="{{ route('login') }}" class="btn btn-primary" style="padding:10px 18px;border-radius:8px;background:#007bff;color:#fff;text-decoration:none;">Masuk kembali</a>
                <a href="{{ url('/') }}" style="padding:10px 18px;border-radius:8px;background:transparent;border:1px solid rgba(255,255,255,0.06);color:#fff;text-decoration:none;">Kembali ke Beranda</a>
            </div>

            <div style="opacity:0.75;font-size:13px">
                Jika masalah berlanjut, silakan hubungi support melalui 
                <a href="https://wa.me/+6281273796646" style="color:#b8f0c6" target="_blank" rel="noopener noreferrer">kontak di atas</a> 
                dan sertakan detail kronologi (mis. apa yang Anda lakukan sebelum pesan ini muncul).
            </div>
        </div>
    </div>
@endsection
