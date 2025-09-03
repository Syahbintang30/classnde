@extends('layouts.admin')

@section('title','Edit User')

@section('content')
<div class="header mb-4">
    <h2>Edit User {{ $user->name }}</h2>
</div>

<form method="POST" action="{{ route('admin.users.update', $user->id) }}">
    @csrf
    <div class="select-menu">
        <label class="label">Package</label>
        <input type="hidden" name="package_id" id="packageInput" value="{{ $user->package_id ?? '' }}">

        <!-- Custom dropdown -->
        <div class="select-btn">
            <span class="btn-text">
                @if($user->package_id)
                    {{ $packages->firstWhere('id', $user->package_id)->name }}
                @else
                    - none -
                @endif
            </span>
            <i class="ph ph-caret-down"></i>
        </div>

        <ul class="options">
            <li class="option" data-value="">
                <span class="option-text">- none -</span>
            </li>
            @foreach($packages as $p)
                <li class="option" data-value="{{ $p->id }}">
                    <span class="option-text">{{ $p->name }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mb-3">
        <label class="label">Available Tickets</label>
        <input type="number" name="available_tickets" class="form-control input" value="{{ old('available_tickets', $available) }}" min="0" />
        <small class="form-text">Reducing available tickets will mark the newest unused tickets as "used" for audit (they will not be deleted).</small>
    </div>
    <div class="mb-3">
        <label class="label">Total Tickets</label>
        <input type="number" class="form-control input" value="{{ $total ?? 0 }}" disabled />
        <small class="form-text">Total tickets (used + unused). To change how many are available for use, edit "Available Tickets" above.</small>
    </div>
    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Update</button>
        <a href="{{ route('admin.users.packages') }}" class="btn-back">Kembali</a>
    </div>
</form>

@endsection
