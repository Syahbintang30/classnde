@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div style="max-width:900px;margin:40px auto;padding:20px;color:#fff">
        <h2 style="margin:0 0 18px 0">Edit Profile</h2>

        {{-- Flash/messages --}}
        @if(session('status') || session('success') || session('error') || $errors->any())
            <div style="margin-bottom:18px">
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

        <div style="display:grid;grid-template-columns:1fr;gap:22px">
            {{-- Profile Information --}}
            <section style="background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:12px;border:1px solid rgba(255,255,255,0.03)">
                <h3 style="margin:0 0 12px 0">Profile Information</h3>
                <p style="margin:0 0 16px;color:rgba(255,255,255,0.65)">Update your account's profile information and email address.</p>

                <form method="post" action="{{ route('profile.update') }}" style="max-width:520px">
                    @csrf
                    @method('patch')

                    <div style="margin-bottom:12px">
                        <label for="name" style="display:block;font-weight:700;margin-bottom:6px">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" style="width:100%;padding:10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                        @if($errors->has('name'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('name') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="email" style="display:block;font-weight:700;margin-bottom:6px">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" style="width:100%;padding:10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                        @if($errors->has('email'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('email') }}</div>@endif
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;margin-top:6px">
                        <button type="submit" style="background:#111;border:1px solid rgba(255,255,255,0.06);padding:10px 14px;border-radius:8px;color:#fff;font-weight:700;cursor:pointer">Save</button>
                        <a href="{{ route('profile') }}" style="color:rgba(255,255,255,0.6);text-decoration:none;padding:8px">Cancel</a>
                    </div>
                </form>
            </section>

            {{-- Password update --}}
            <section id="password" style="background:linear-gradient(180deg,#070707,#0c0c0c);padding:22px;border-radius:12px;border:1px solid rgba(255,255,255,0.03)">
                <h3 style="margin:0 0 12px 0">Update Password</h3>
                <p style="margin:0 0 16px;color:rgba(255,255,255,0.65)">Ensure your account is using a long, random password to stay secure.</p>

                <form method="post" action="{{ route('password.update') }}" style="max-width:520px">
                    @csrf
                    @method('put')

                    <div style="margin-bottom:12px">
                        <label for="current_password" style="display:block;font-weight:700;margin-bottom:6px">Current Password</label>
                        <div style="position:relative">
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="current_password" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('current_password'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('current_password') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="password" style="display:block;font-weight:700;margin-bottom:6px">New Password</label>
                        <div style="position:relative">
                            <input id="password" name="password" type="password" required autocomplete="new-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="password" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('password'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('password') }}</div>@endif
                    </div>

                    <div style="margin-bottom:12px">
                        <label for="password_confirmation" style="display:block;font-weight:700;margin-bottom:6px">Confirm Password</label>
                        <div style="position:relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" style="width:100%;padding:10px 40px 10px 10px;border-radius:6px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);color:#fff">
                            <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Toggle password visibility" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;padding:6px 8px;cursor:pointer;color:rgba(255,255,255,0.85);display:flex;align-items:center;justify-content:center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"></path>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.4"></circle>
                            </svg>
                            </button>
                        </div>
                        @if($errors->has('password_confirmation'))<div style="color:#ffb4b4;margin-top:6px">{{ $errors->first('password_confirmation') }}</div>@endif
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;margin-top:6px">
                        <button type="submit" style="background:#111;border:1px solid rgba(255,255,255,0.06);padding:10px 14px;border-radius:8px;color:#fff;font-weight:700;cursor:pointer">Save</button>
                        <a href="{{ route('profile') }}" style="color:rgba(255,255,255,0.6);text-decoration:none;padding:8px">Cancel</a>
                    </div>
                </form>
            </section>

            {{-- Delete account removed per request --}}
        </div>
    </div>

    <script>
        // If user clicked "Change Password" link which points to #password, scroll and focus
        (function(){
            if (window.location.hash === '#password') {
                var el = document.getElementById('password');
                if (el) {
                    setTimeout(function(){ el.scrollIntoView({behavior:'smooth', block:'start'}); var input = el.querySelector('input[name="current_password"]'); if (input) input.focus(); }, 80);
                }
            }
        })();
        // password visibility toggles
        (function(){
            var toggles = document.querySelectorAll('.pw-toggle');
            toggles.forEach(function(btn){
                btn.addEventListener('click', function(){
                    var targetId = btn.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    if (! input) return;
                    if (input.type === 'password') {
                        input.type = 'text';
                        btn.style.opacity = '1';
                    } else {
                        input.type = 'password';
                        btn.style.opacity = '0.85';
                    }
                });
            });
        })();
    </script>
@endsection
