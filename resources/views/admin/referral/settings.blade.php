@extends('layouts.admin')

@section('title', 'Referral Settings')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="mb-4 header">
            <h2>Referral Settings</h2>
            <p>Configure referral behaviour and discounts. Changes here affect how referral codes apply during purchase and registration.</p>
        </div>
        <form method="POST" action="{{ route('admin.referral.settings.save') }}">
            @csrf
            <div class="mb-3">
                <label class="label">Referral discount percent</label>
                <input type="number" name="discount_percent" class="form-control input" value="{{ old('discount_percent', $discount) }}" min="0" max="100" />
                <small>Percent discount applied to purchases when a valid referral code is used. Default: {{ config('referral.discount_percent', 2) }}%.</small>
            </div>

            <div class="mb-3 form-check">
                <input type="hidden" name="auto_grant_ticket" value="0" />
                <input type="checkbox" name="auto_grant_ticket" value="1" class="form-check-input" id="autoGrant" {{ $autoGrantTicket == '1' ? 'checked' : '' }} />
                <label class="form-check-label label" for="autoGrant">Auto-grant 1 coaching ticket to referrer on successful referred registration</label>
                <small>When enabled, the user who referred someone will automatically receive 1 coaching ticket credited to their account when the referred user completes registration/purchase.</small>
            </div>

            <button class="btn-submit">Save</button>
        </form>
    </div>
    <div class="col-md-4 header mb-4">
        <h2 class="mb-2">Benefits of referral settings</h2>
        <ul style="color: #666">
            <li>Control referral discount without code changes (marketing flexibility).</li>
            <li>Auto-grant tickets reduces manual intervention for rewarding referrers.</li>
            <li>Exportable data for promo tracking (export option below).</li>
        </ul>
        <div class="mt-3">
            <a href="{{ route('admin.referral.export') }}" class="btn btn-outline-secondary">Export referral usage CSV</a>
        </div>
    </div>
</div>
@endsection
