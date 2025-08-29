@extends('layouts.app')

@section('content')
<div class="container py-8">
    <h1 class="text-2xl font-bold mb-4">Pembayaran Gagal / Belum Terselesaikan</h1>

    <div class="bg-white shadow rounded p-4">
        <p>{{ $message ?? 'Terjadi masalah pada proses pembayaran.' }}</p>
    </div>

    <div class="mt-6">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
