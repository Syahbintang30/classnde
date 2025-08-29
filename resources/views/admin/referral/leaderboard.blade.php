@extends('layouts.admin')

@section('title','Referral Leaderboard')

@section('content')
    <h3>Referral Leaderboard</h3>
    <p>Top users by number of successful referrals.</p>

    <table class="table table-striped">
        <thead>
            <tr><th>Rank</th><th>User</th><th>Referrals</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($rows as $row)
                @php $u = $users->get($row->referred_by); @endphp
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $u ? $u->name : ('User ID ' . $row->referred_by) }}</td>
                    <td>{{ $row->referrals }}</td>
                    <td>
                        @if($u)
                            <a href="{{ route('admin.users.packages') }}?user_id={{ $u->id }}" class="btn btn-sm btn-outline-primary">View user</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
