@extends('layouts.app')

@section('title', 'My Referrals')

@section('content')
<div style="max-width:900px;margin:40px auto;padding:20px;color:#fff">
    <h2 style="margin:0 0 12px 0">My Referrals</h2>
    <div style="margin-bottom:12px;color:rgba(255,255,255,0.75)">You have referred <strong>{{ $referred->count() }}</strong> users.</div>
    <div style="background:rgba(255,255,255,0.02);padding:14px;border-radius:10px">
        @if($referred->isEmpty())
            <div style="color:rgba(255,255,255,0.6)">No referrals yet. Share your invite link from your profile page to invite friends.</div>
        @else
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="text-align:left;color:rgba(255,255,255,0.8)">
                        <th style="padding:8px 6px">Name</th>
                        <th style="padding:8px 6px">Email</th>
                        <th style="padding:8px 6px">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referred as $r)
                        <tr style="border-top:1px solid rgba(255,255,255,0.03)">
                            <td style="padding:10px 6px">{{ $r->name }}</td>
                            <td style="padding:10px 6px">{{ $r->email }}</td>
                            <td style="padding:10px 6px">{{ $r->created_at ? $r->created_at->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div style="margin-top:12px">
        <a href="{{ route('profile') }}" class="btn-ghost">Back to profile</a>
    </div>
</div>
@endsection
