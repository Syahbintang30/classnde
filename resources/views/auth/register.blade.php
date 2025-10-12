@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px;background:#000;color:#fff;">
    <div style="width:900px;display:flex;gap:24px;">
        <div style="flex:1;padding:28px;border-radius:8px;background:#090909;border:1px solid #222;">
            <h2 style="margin:0 0 8px 0;font-size:22px">Create account</h2>
            <p style="opacity:0.7;margin-bottom:16px">Sign up to access lessons and booking features.</p>

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf
                {{-- Alert area: show validation errors and helpful guidance --}}
                @if(session('status'))
                    <div style="background:#0b2f0b;padding:10px;border-radius:6px;margin-bottom:12px;color:#b6f2b6">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div style="background:#2b0b0b;padding:12px;border-radius:8px;margin-bottom:12px;color:#ffd9d9">
                        <strong>Registration issues:</strong>
                        <ul style="margin-top:8px;padding-left:18px">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <div style="margin-top:8px;font-size:13px;opacity:0.9">Common causes: email already registered, passwords don't match, or password does not meet complexity requirements.</div>
                    </div>
                @endif
                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px">Name</label>
                    <input name="name" type="text" value="{{ old('name') }}" required style="width:100%;padding:12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                    @error('name')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required style="width:100%;padding:12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                    @error('email')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;gap:12px;margin-bottom:12px">
                    <div style="flex:1">
                        <label style="display:block;margin-bottom:6px">Password</label>
                        <div class="password-field-inline" style="position:relative">
                            <input name="password" type="password" required style="width:100%;padding:12px 40px 12px 12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                            <button type="button" class="password-toggle" aria-label="Show password" title="Show password" aria-pressed="false" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;padding:6px;cursor:pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        @error('password')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                        <div id="passwordHelp" style="margin-top:8px;font-size:13px;opacity:0.85">
                            Password must be at least 8 characters and include a mix of letters and numbers.
                        </div>
                    </div>
                    <div style="flex:1">
                        <label style="display:block;margin-bottom:6px">Confirm</label>
                        <div class="password-field-inline" style="position:relative">
                            <input name="password_confirmation" type="password" required style="width:100%;padding:12px 40px 12px 12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                            <button type="button" class="password-toggle" aria-label="Show password" title="Show password" aria-pressed="false" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;padding:6px;cursor:pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="text-align:right">
                    <button type="submit" style="background:#fff;color:#000;padding:10px 20px;border-radius:24px;border:none;font-weight:700;">REGISTER</button>
                </div>
            </form>
        </div>

        <div style="width:340px;padding:28px;border-radius:8px;background:linear-gradient(180deg,#0a0a0a,#050505);border:1px solid #111;">
            <h3 style="margin-top:0">Already have an account?</h3>
            <p style="opacity:0.75">If you already registered, login to continue.</p>
            <a href="{{ route('login') }}" style="display:inline-block;margin-top:18px;padding:10px 18px;border-radius:22px;background:transparent;border:1px solid #fff;color:#fff;text-decoration:none;font-weight:600;">Login</a>
        </div>
            <script>
                // small toggle logic for register page
                document.addEventListener('click', function(e){
                    var btn = e.target.closest && e.target.closest('.password-toggle');
                    if(!btn) return;
                    var field = btn.closest('.password-field-inline');
                    if(!field) return;
                    var input = field.querySelector('input');
                    if(!input) return;
                    var eye = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                    var eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.7 21.7 0 0 1 5-5"></path><path d="M1 1l22 22"></path></svg>';
                    if(input.type === 'password'){
                        input.type = 'text';
                        btn.innerHTML = eyeOff;
                        btn.setAttribute('aria-label','Hide password');
                        btn.setAttribute('title','Hide password');
                        btn.setAttribute('aria-pressed','true');
                    } else {
                        input.type = 'password';
                        btn.innerHTML = eye;
                        btn.setAttribute('aria-label','Show password');
                        btn.setAttribute('title','Show password');
                        btn.setAttribute('aria-pressed','false');
                    }
                });
                // Client-side password confirmation and basic strength feedback
                (function(){
                    var form = document.getElementById('registerForm');
                    if(!form) return;
                    var pwd = form.querySelector('input[name=password]');
                    var confirm = form.querySelector('input[name=password_confirmation]');
                    var help = document.getElementById('passwordHelp');

                    function scorePassword(p){
                        var score = 0;
                        if(!p) return 0;
                        if(p.length >= 8) score += 1;
                        if(/[A-Z]/.test(p)) score += 1;
                        if(/[0-9]/.test(p)) score += 1;
                        if(/[^A-Za-z0-9]/.test(p)) score += 1;
                        return score;
                    }

                    function updateHelp(){
                        if(!pwd) return;
                        var s = scorePassword(pwd.value);
                        var text = 'Password must be at least 8 characters.';
                        if(s <= 1) { help.style.color = '#ffd3d3'; help.textContent = text + ' (too weak)'; }
                        else if(s === 2) { help.style.color = '#ffe7c4'; help.textContent = 'Fair — consider adding numbers or symbols.'; }
                        else if(s >= 3) { help.style.color = '#c9f7d6'; help.textContent = 'Good password.'; }
                    }

                    pwd && pwd.addEventListener('input', updateHelp);

                    form.addEventListener('submit', function(e){
                        if(pwd && confirm && pwd.value !== confirm.value){
                            e.preventDefault();
                            alert('Passwords do not match. Please confirm your password correctly.');
                            confirm.focus();
                            return false;
                        }
                        // basic client-side email duplicate hint: if email value looks like existing (server-side enforced)
                        return true;
                    });
                })();
            </script>

        </div>
</div>
@endsection
