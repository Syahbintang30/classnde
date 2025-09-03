@extends('layouts.admin')

@section('title','Referred Users')

@section('content')
<div class="header mb-4">
    <h2>Users referred by {{ $referrer->name }}</h2>
    <p>List of users who used {{ $referrer->name }}'s referral code.</p>
</div>

<div class="card-table">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Registered At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr style="pointer-events: none; background: transparent;">
                    <td colspan="3" class="text-center pt-5">Belum ada users</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $users->links('pagination::bootstrap-5') }}
</div>

@endsection
