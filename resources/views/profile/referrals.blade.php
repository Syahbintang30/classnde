@extends('layouts.app')

@section('title', 'My Referrals')

@section('content')
<div style="max-width:900px;margin:40px auto;padding:20px;color:#fff">
    <h2 style="margin:0 0 12px 0">My Referrals</h2>
    <div style="margin-bottom:12px;color:rgba(255,255,255,0.75)">You have referred <strong>{{ $referred->count() }}</strong> users.</div>
    <div style="background:rgba(255,255,255,0.02);padding:14px;border-radius:10px;margin-bottom:14px">
        <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between">
            <div style="min-width:220px">
                <div style="font-weight:800;margin-bottom:8px;color:rgba(255,255,255,0.9)">Discount summary</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;color:rgba(255,255,255,0.75)">
                    <div>Units available</div>
                    <div style="text-align:right">{{ $availableUnits ?? 0 }} × 25%</div>
                    <div>Discount available</div>
                    <div style="text-align:right">{{ $referralDiscountPercent ?? 0 }}%</div>
                    <div>Units redeemed</div>
                    <div style="text-align:right">{{ $redeemedUnits ?? 0 }}</div>
                </div>
            </div>
            <div style="flex:1;min-width:260px">
                <div style="font-weight:700;margin-bottom:6px;color:rgba(255,255,255,0.85)">Your invite link</div>
                @php $invite = url('/').'?ref='.(auth()->user()->referral_code ?? ''); @endphp
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" readonly value="{{ $invite }}" style="flex:1;padding:8px 10px;border-radius:8px;background:rgba(0,0,0,0.25);color:#ddd;border:1px solid rgba(255,255,255,0.07);font-family:monospace">
                    <button type="button" class="btn-ghost" onclick="navigator.clipboard.writeText('{{ $invite }}')">Copy</button>
                </div>
                <div style="margin-top:6px;color:rgba(255,255,255,0.55);font-size:12px">Each successful signup adds 25% towards your next coaching ticket (auto-applied, up to 100%).</div>
            </div>
        </div>
    </div>
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
