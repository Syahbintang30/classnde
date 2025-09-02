@extends('layouts.admin')

@section('title','Referral Leaderboard')

@section('content')
<div class="content-wrapper">
    <div class="mb-4 header">
        <h2>Referral Leaderboard</h2>
        <p>Top users by number of successful referrals.</p>
    </div>

    <div class="card-table">
        <table class="custom-table">
            <thead>
                <tr><th>Rank</th><th>User</th><th>Referrals</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @forelse($rows as $row)
                    @php $u = $users->get($row->referred_by); @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $u ? $u->name : ('User ID ' . $row->referred_by) }}</td>
                        <td>{{ $row->referrals }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.users.packages') }}?user_id={{ $u->id }}" class="d-inline">
                                <button class="icon-btn ph-duotone ph-identification-card" 
                                onmouseover="this.style.color='#296EB4'"onmouseout="this.style.color=''"
                                onclick="event.stopPropagation()"></button>
                            </form>
                            @if($u)
                                <a href="{{ route('admin.users.packages') }}?user_id={{ $u->id }}" class="btn btn-sm btn-outline-primary">View user</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr style="pointer-events: none; background: transparent;">
                        <td colspan="4" class="text-center pt-5">Belum ada leaderboard</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
