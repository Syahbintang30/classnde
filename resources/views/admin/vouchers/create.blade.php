@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Create Voucher</h1>
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.vouchers.store') }}">
            @csrf
            <div class="mb-3"><label>Code</label><input name="code" class="form-control" required value="{{ old('code') }}"></div>
            <div class="mb-3"><label>Discount Percent</label><input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', 0) }}" min="0" max="100" required></div>
            <div class="mb-3"><label>Usage Limit (optional)</label><input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit') }}"></div>
            <div class="mb-3"><label>Expires At (optional)</label><input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}"></div>
            <div class="mb-3"><label><input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}> Active</label></div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
