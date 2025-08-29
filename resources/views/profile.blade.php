@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div style="max-width:1000px;margin:40px auto;padding:20px;color:#fff">
    {{-- Flash messages --}}
    @if(session('status') || session('success') || session('error') || $errors->any())
        <div style="margin-bottom:14px">
            @if(session('status') === 'profile-updated' || session('success'))
                <div style="background:linear-gradient(90deg,#0b7a44,#11998e);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(12,120,68,0.18)">
                    {{ session('success') ?? 'Profile updated.' }}
                </div>
            @endif

            @if(session('status') === 'password-updated')
                <div style="background:linear-gradient(90deg,#0b7a44,#11998e);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(12,120,68,0.18)">
                    Password updated.
                </div>
            @endif

            @if(session('error'))
                <div style="background:linear-gradient(90deg,#c0392b,#e74c3c);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(224,67,67,0.12)">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:linear-gradient(90deg,#c0392b,#e74c3c);padding:12px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 30px rgba(224,67,67,0.12)">
                    <ul style="margin:0;padding-left:18px">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
    <div style="display:flex;gap:24px;align-items:flex-start">
        <div style="flex:0 0 260px;background:linear-gradient(180deg,#0b0b0b,#0f0f0f);padding:20px;border-radius:14px;border:1px solid rgba(255,255,255,0.04);box-shadow:0 12px 40px rgba(0,0,0,0.6)">
            <div style="display:flex;flex-direction:column;align-items:center;gap:12px">
                <div style="width:120px;height:120px;border-radius:999px;overflow:hidden;border:4px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;background:#111">
                    <img src="{{ asset('compro/img/ndelogo.png') }}" alt="avatar" style="width:92px;height:92px;object-fit:cover;filter:grayscale(0.05);opacity:0.98">
                </div>
                <div style="font-weight:800;font-size:18px">{{ auth()->user()->name }}</div>
                <div style="color:rgba(255,255,255,0.65);font-size:13px">{{ auth()->user()->email }}</div>
                <div style="width:100%;margin-top:10px">
                    <a href="{{ route('registerclass') }}" class="btn-ghost" style="width:100%;display:inline-block;text-align:center">Browse Courses</a>
                </div>
            </div>
        </div>

        <div style="flex:1;background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:14px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 12px 40px rgba(0,0,0,0.5)">
            <h2 style="margin:0 0 12px 0">Account</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:10px">
                    <div style="color:rgba(255,255,255,0.8);font-weight:700">Name</div>
                    <div style="color:rgba(255,255,255,0.6)">{{ auth()->user()->name }}</div>
                </div>
                <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:10px">
                    <div style="color:rgba(255,255,255,0.8);font-weight:700">Email</div>
                    <div style="color:rgba(255,255,255,0.6)">{{ auth()->user()->email }}</div>
                </div>
                <div style="grid-column:1 / -1;background:rgba(255,255,255,0.02);padding:12px;border-radius:10px">
                    <div style="color:rgba(255,255,255,0.8);font-weight:700">Subscription</div>
                    <div style="color:rgba(255,255,255,0.6)">
                        @if(auth()->user()->package_id)
                            Paket ID: {{ auth()->user()->package_id }}
                        @else
                            None
                        @endif
                    </div>
                </div>
            </div>

            <div style="margin-top:18px;display:flex;gap:10px">
                <a href="{{ route('profile.edit') }}" class="btn-ghost">Edit Profile</a>
                <a href="{{ route('profile.edit') }}#password" class="btn-ghost">Change Password</a>
                <a href="{{ route('profile.referrals') }}" class="btn-ghost">My Referrals</a>
            </div>

            <div style="margin-top:18px;display:flex;gap:10px;align-items:center">
                <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:10px;display:flex;gap:10px;align-items:center">
                    <div style="font-weight:700;color:rgba(255,255,255,0.85)">Referral code</div>
                    <div style="color:rgba(255,255,255,0.6);font-family:monospace;padding:6px 10px;background:rgba(0,0,0,0.25);border-radius:6px">{{ auth()->user()->referral_code ?? '—' }}</div>
                    <button id="copy-invite" class="btn-ghost" data-code="{{ auth()->user()->referral_code }}">Copy invite link</button>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function(){
                    var btn = document.getElementById('copy-invite');
                    if(!btn) return;
                    btn.addEventListener('click', function(){
                        var code = btn.getAttribute('data-code');
                        if(!code){ alert('No referral code'); return; }
                        var full = window.location.origin + '/r?ref=' + encodeURIComponent(code);
                        navigator.clipboard?.writeText(full).then(function(){
                            alert('Invite link copied to clipboard');
                        }).catch(function(){
                            // fallback: show prompt
                            window.prompt('Copy this invite link', full);
                        });
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection
