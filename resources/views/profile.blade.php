@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="profile-view-container">
        <style>
            .profile-view-container{max-width:1000px;margin:40px auto;padding:20px;color:#fff}
            @media (max-width:640px){ .profile-view-container{margin:18px auto;padding:12px} }

            .profile-grid{display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start}
            @media (max-width:900px){ .profile-grid{grid-template-columns:1fr;gap:16px} }

            .profile-sidebar{flex:0 0 260px;background:linear-gradient(180deg,#0b0b0b,#0f0f0f);padding:20px;border-radius:14px;border:1px solid rgba(255,255,255,0.04);box-shadow:0 12px 40px rgba(0,0,0,0.6)}
            @media (max-width:640px){ .profile-sidebar{padding:14px} }

            .profile-main{background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:14px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 12px 40px rgba(0,0,0,0.5)}
            @media (max-width:640px){ .profile-main{padding:14px} }

            .avatar-wrap{width:120px;height:120px;border-radius:999px;overflow:hidden;border:4px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;background:#111}
            .avatar-wrap img{width:100%;height:100%;object-fit:cover;filter:grayscale(0.03);opacity:0.98;display:block}
            @media (max-width:900px){ .avatar-wrap{width:110px;height:110px} }
            @media (max-width:420px){ .avatar-wrap{width:92px;height:92px} }

            .account-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
            @media (max-width:720px){ .account-grid{grid-template-columns:1fr} }

            .info-card{background:rgba(255,255,255,0.02);padding:12px;border-radius:10px}
            .info-label{color:rgba(255,255,255,0.8);font-weight:700}
            .info-value{color:rgba(255,255,255,0.6)}

            .actions-row{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
            .referral-row{margin-top:18px;display:flex;gap:10px;align-items:center}

            /* Make ghost buttons full width on tiny screens */
            @media (max-width:420px){ .actions-row .btn-ghost{flex:1 1 100%;text-align:center} }
        </style>
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
    <div class="profile-grid">
        <div class="profile-sidebar">
            <div style="display:flex;flex-direction:column;align-items:center;gap:12px">
                    <div class="avatar-wrap">
                    @php $avatar = auth()->user()->photoUrl(); @endphp
                    @if($avatar)
                        <img src="{{ $avatar }}" alt="avatar">
                    @else
                        {{-- Default profile SVG icon --}}
                        <svg width="72" height="72" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" fill="#ffffff" />
                            <path d="M3 21c0-3.866 3.582-7 9-7s9 3.134 9 7v1H3v-1z" fill="#ffffff" opacity="0.12" />
                        </svg>
                    @endif
                </div>
                <div style="font-weight:800;font-size:18px">{{ auth()->user()->name }}</div>
                <div style="color:rgba(255,255,255,0.65);font-size:13px">{{ auth()->user()->email }}</div>
                <div style="width:100%;margin-top:10px">
                    <a href="{{ route('registerclass') }}" class="btn-ghost" style="width:100%;display:inline-block;text-align:center">Browse Courses</a>
                </div>
            </div>
        </div>

        <div class="profile-main">
            <h2 style="margin:0 0 12px 0">Account</h2>
            <div class="account-grid">
                <div class="info-card">
                    <div class="info-label">Name</div>
                    <div class="info-value">{{ auth()->user()->name }}</div>
                </div>
                <div class="info-card">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ auth()->user()->email }}</div>
                </div>
                <div style="grid-column:1 / -1" class="info-card">
                    <div class="info-label">Subscription</div>
                    <div class="info-value">
                        @php
                            $pid = auth()->user()->package_id;
                            $pkgName = null;
                            if ($pid == 1) { $pkgName = 'Beginner'; }
                            elseif ($pid == 2) { $pkgName = 'Intermediate'; }
                            elseif ($pid == 3) { $pkgName = 'Intermediate'; }
                            elseif (isset($package) && $package) { $pkgName = $package->name; }
                        @endphp
                        {{ $pkgName ?? 'None' }}
                    </div>
                </div>
            </div>

            <div class="actions-row">
                <a href="{{ route('profile.edit') }}" class="btn-ghost">Edit Profile</a>
                <a href="{{ route('profile.edit') }}#password" class="btn-ghost">Change Password</a>
                <a href="{{ route('profile.referrals') }}" class="btn-ghost">My Referrals</a>
            </div>


            <div class="referral-row">
                <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <div style="font-weight:700;color:rgba(255,255,255,0.85)">Referral code</div>
                    <div style="color:rgba(255,255,255,0.6);font-family:monospace;padding:6px 10px;background:rgba(0,0,0,0.25);border-radius:6px">{{ auth()->user()->referral_code ?? '\u2014' }}</div>
                    <div style="color:rgba(255,255,255,0.55);font-size:12px">Full referral details are now in <a href="{{ route('profile.referrals') }}" style="color:#fff;text-decoration:underline">My Referrals</a>.</div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>
@endsection
