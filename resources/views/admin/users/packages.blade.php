@extends('layouts.admin')

@section('title','User Packages & Coaching Tickets')

@section('content')
<h3>User Packages & Coaching Tickets</h3>
<p>List of users, their selected package (if any) and available coaching tickets.</p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Package</th>
            <th>Available / Total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                    @if($u->package_id && isset($rolePackages[$u->package_id]))
                        {{ $rolePackages[$u->package_id]->name }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @php
                        $avail = $u->available_tickets_count ?? 0;
                        $total = $u->total_tickets_count ?? 0;
                        $badgeClass = $avail > 0 ? 'badge bg-success' : 'badge bg-danger';
                    @endphp
                    <span class="{{ $badgeClass }}">{{ $avail }} / {{ $total }}</span>
                </td>
                <td>
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $users->links() }}

@endsection
