@extends('layouts.admin')

@section('title','Caching Bookings')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Caching Bookings</h2>
        <div>
            <div class="btn-group" role="group" aria-label="Coaching admin nav">
                <a href="{{ url('/admin/coaching/bookings') }}" class="btn btn-sm {{ request()->is('admin/coaching/bookings*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}">Bookings</a>
                <a href="{{ url('/admin/caching-bookings') }}" class="btn btn-sm {{ request()->is('admin/caching-bookings*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}">Cached Bookings</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Booked At</th>
                        <th>Status</th>
                        <th>Booking ID</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>
                                @php $u = optional($r->booking)->user; @endphp
                                @if($u)
                                    <div class="fw-bold">{{ $u->name ?? 'User #' . $r->user_id }}</div>
                                    <div class="text-muted small">{{ $u->email ?? '-' }} {{ $u->phone ? '· ' . $u->phone : '' }}</div>
                                @else
                                    <div>{{ 'User #' . $r->user_id }}</div>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($r->date . ' ' . $r->time)->translatedFormat('j F Y, H:i') }}</td>
                            <td>{{ ucfirst($r->status) }}</td>
                            <td>{{ $r->booking_id }}</td>
                            <td>{{ $r->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                @if($r->status !== 'accepted')
                                    <form method="POST" action="{{ url('admin/caching-bookings/' . $r->id . '/accept') }}" style="display:inline">@csrf <button class="btn btn-sm btn-success me-1">Accept</button></form>
                                @endif
                                @if($r->status !== 'rejected')
                                    <form method="POST" action="{{ url('admin/caching-bookings/' . $r->id . '/reject') }}" style="display:inline">@csrf <button class="btn btn-sm btn-danger">Reject</button></form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $rows->links() }}</div>
@endsection
