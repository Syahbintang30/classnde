@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px;background:#000;color:#fff;">
    <div style="width:900px;display:flex;gap:24px;">
        <div style="flex:1;padding:28px;border-radius:8px;background:#090909;border:1px solid #222;">
            <h2 style="margin:0 0 8px 0;font-size:22px">Create account</h2>
            <p style="opacity:0.7;margin-bottom:16px">Sign up to access lessons and booking features.</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px">Name</label>
                    <input name="name" type="text" value="{{ old('name') }}" required style="width:100%;padding:12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                    @error('name')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px">
                    <label style="display:block;margin-bottom:6px">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" required style="width:100%;padding:12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                    @error('email')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;gap:12px;margin-bottom:12px">
                    <div style="flex:1">
                        <label style="display:block;margin-bottom:6px">Password</label>
                        <div class="password-field-inline" style="position:relative">
                            <input name="password" type="password" required style="width:100%;padding:12px 40px 12px 12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;padding:6px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        @error('password')<div style="color:#ff6b6b;margin-top:6px">{{ $message }}</div>@enderror
                    </div>
                    <div style="flex:1">
                        <label style="display:block;margin-bottom:6px">Confirm</label>
                        <div class="password-field-inline" style="position:relative">
                            <input name="password_confirmation" type="password" required style="width:100%;padding:12px 40px 12px 12px;border-radius:6px;background:transparent;border:1px solid #333;color:#fff;" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;padding:6px;">
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
                    } else {
                        input.type = 'password';
                        btn.innerHTML = eye;
                    }
                });
            </script>

        </div>
</div>
@endsection
