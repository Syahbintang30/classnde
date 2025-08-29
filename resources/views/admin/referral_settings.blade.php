@extends('layouts.app')

@section('title','Referral Settings')

@section('content')
<div style="max-width:800px;margin:40px auto;color:#fff;padding:20px">
    <h2>Referral Settings</h2>
    @if(isset($metrics))
    <div style="display:flex;gap:12px;margin:12px 0 18px">
        <div style="background:#111;padding:12px;border-radius:6px">
            <div style="font-size:12px;color:#999">Total referred users</div>
            <div style="font-size:20px;margin-top:6px">{{ $metrics['total_referrals'] }}</div>
        </div>
        <div style="background:#111;padding:12px;border-radius:6px">
            <div style="font-size:12px;color:#999">Referral tickets granted</div>
            <div style="font-size:20px;margin-top:6px">{{ $metrics['total_referral_tickets'] }}</div>
        </div>
        <div style="background:#111;padding:12px;border-radius:6px">
            <div style="font-size:12px;color:#999">Total discount given (IDR)</div>
            <div style="font-size:20px;margin-top:6px">{{ number_format($metrics['total_discount'],0,',','.') }}</div>
        </div>
    </div>
    @endif
    @if(session('success'))<div style="background:linear-gradient(90deg,#0b7a44,#11998e);padding:10px;border-radius:6px;margin-bottom:10px">{{ session('success') }}</div>@endif
    <form method="post" action="{{ route('admin.referral.save') }}">
        @csrf
        <div style="margin-bottom:12px">
            <label style="display:block;margin-bottom:6px">Default referral discount percent (0-100)</label>
            <input name="discount_percent" value="{{ old('discount_percent',$discount) }}" required style="padding:10px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff" />
        </div>
        <div>
            <button type="submit" class="btn-ghost">Save settings</button>
        </div>
    </form>
    <div style="margin-top:12px">
        <a href="{{ route('admin.referral.export') }}" class="btn-ghost">Export referral transactions (CSV)</a>
    </div>
</div>
@endsection
