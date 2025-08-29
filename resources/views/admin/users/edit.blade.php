@extends('layouts.admin')

@section('title','Edit User')

@section('content')
<h3>Edit User: {{ $user->name }}</h3>

<form method="POST" action="{{ route('admin.users.update', $user->id) }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Package</label>
        <select name="package_id" class="form-control">
            <option value="">- none -</option>
            @foreach($packages as $p)
                <option value="{{ $p->id }}" {{ $user->package_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Available Tickets</label>
        <input type="number" name="available_tickets" class="form-control" value="{{ old('available_tickets', $available) }}" min="0" />
        <small class="form-text text-muted">Reducing available tickets will mark the newest unused tickets as "used" for audit (they will not be deleted).</small>
    </div>
    <div class="mb-3">
        <label class="form-label">Total Tickets</label>
        <input type="number" class="form-control" value="{{ $total ?? 0 }}" disabled />
        <small class="form-text text-muted">Total tickets (used + unused). To change how many are available for use, edit "Available Tickets" above.</small>
    </div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('admin.users.packages') }}" class="btn btn-secondary">Cancel</a>
</form>

@endsection
