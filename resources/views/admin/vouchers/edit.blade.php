@extends('layouts.admin')

@section('content')
<div class="header mb-4">
    <h2>Edit Voucher</h2>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

        <form method="POST" action="{{ route('admin.vouchers.update', $voucher->id) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="label">Code</label>
                <input name="code" class="form-control input" value="{{ old('code', $voucher->code) }}" required>
            </div>
            <div class="mb-3">
                <label class="label">Discount Percent</label>
                <input type="number" name="discount_percent" class="form-control input" value="{{ old('discount_percent', $voucher->discount_percent) }}" min="0" max="100" required>
            </div>
            <div class="mb-3">
                <label class="label">Usage Limit (optional)</label>
                <input type="number" name="usage_limit" class="form-control input" value="{{ old('usage_limit', $voucher->usage_limit) }}">
            </div>
            <div class="mb-3">
                <label class="label">Expires At (optional)</label>
                <input type="datetime-local" name="expires_at" class="form-control input" value="{{ old('expires_at', optional($voucher->expires_at)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="mb-3">
                <label class="label"><input type="checkbox" name="active" value="1" {{ old('active', $voucher->active) ? 'checked' : '' }}> Active</label>
            </div>
            <div class="d-flex justify-content-end mt-3 gap-3">
                <button class="btn-submit">Simpan</button>
                <a href="{{ route('admin.vouchers.index') }}" class="btn-back">Kembali</a>
            </div>
        </form>
    </div>
@endsection
