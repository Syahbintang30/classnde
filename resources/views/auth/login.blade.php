@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="login-hero">
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-card-inner">
                <h1>Welcome back</h1>
                <p class="muted">Sign in to access your classes and coaching sessions.</p>

                {{-- Consolidated alerts: success, error, and validation list --}}
                @if(session('status'))
                    <div class="alert success">{{ session('status') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert error">
                        <strong>Login failed — please check the following:</strong>
                        <ul style="margin-top:8px;padding-left:18px">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <div style="margin-top:10px;font-size:13px;opacity:0.85">
                            Possible reasons: incorrect password, unregistered email, or too many failed attempts. If you forgot your password, use the <a href="{{ route('password.request') }}" style="color:#fff;text-decoration:underline">password reset</a> link.
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    <label class="field">
                        <span class="label-text">Email</span>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus class="input" />
                        @error('email')<div class="error">{{ $message }}</div>@enderror
                    </label>

                    <label class="field">
                        <span class="label-text">Password</span>
                        <div class="password-field">
                            <input name="password" type="password" required class="input" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <!-- eye (visible) icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')<div class="error">{{ $message }}</div>@enderror
                    </label>

                    <div class="form-meta">
                        <label class="remember"><input type="checkbox" name="remember" /> Remember me</label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                        @endif
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="signup-card">
            <div>
                <h2>New here?</h2>
                <p class="muted">Create a free account to start learning and book coaching sessions.</p>
                <a href="{{ route('registerclass') }}" class="btn btn-outline">Create account</a>
            </div>
        </aside>
    </div>
</div>

<style>
    /* Page layout */
    .login-hero{min-height:72vh;display:flex;align-items:center;justify-content:center;padding:48px;background:#000;color:#fff}
    .login-wrap{width:960px;display:flex;gap:28px;align-items:stretch}
    .login-card{flex:1;border-radius:12px;background:linear-gradient(180deg,#0a0a0a,#050505);border:1px solid #151515;box-shadow:0 8px 30px rgba(0,0,0,0.6);overflow:hidden}
    .login-card-inner{padding:36px}
    .signup-card{width:360px;padding:36px;border-radius:12px;background:transparent;border:1px solid #151515;display:flex;align-items:center;justify-content:center}

    h1{margin:0 0 6px;font-size:26px;font-weight:700}
    h2{margin:0 0 10px;font-size:20px}
    .muted{opacity:0.75;margin-bottom:18px}

    .alert{background:#111;padding:10px;border-radius:8px;margin-bottom:14px}

    /* Form fields */
    .field{display:block;margin-bottom:14px}
    .label-text{display:block;font-size:13px;margin-bottom:8px;opacity:0.85}
    .input{width:100%;padding:12px 40px 12px 14px;border-radius:10px;background:transparent;border:1px solid #2a2a2a;color:#fff;outline:none}
    .input:focus{border-color:#fff;box-shadow:0 6px 18px rgba(255,255,255,0.04)}
    .error{color:#ff6b6b;margin-top:8px;font-size:13px}

    /* password toggle */
    .password-field{position:relative}
    .password-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;cursor:pointer;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center}
    .password-toggle svg{width:18px;height:18px;opacity:0.95}
    .password-toggle:focus{outline:none;box-shadow:0 0 0 3px rgba(255,255,255,0.06)}

    .form-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
    .remember{opacity:0.95;font-size:14px}
    .forgot{color:#fff;opacity:0.85;text-decoration:underline;font-size:14px}

    /* Buttons (monochrome) */
    .btn{display:inline-flex;align-items:center;gap:10px;padding:10px 18px;border-radius:999px;font-weight:700;text-decoration:none;cursor:pointer;transition:transform .14s ease,box-shadow .14s ease,opacity .14s ease}
    .btn-primary{background:#fff;color:#000;border:1px solid rgba(255,255,255,0.06)}
    .btn-primary:hover{background:#000;color:#fff;box-shadow:0 18px 40px rgba(0,0,0,0.6);transform:translateY(-4px)}
    .btn-primary:active{transform:translateY(-2px)}

    .btn-outline{background:transparent;color:#fff;border:1px solid #fff;padding:10px 18px;border-radius:999px}
    .btn-outline:hover{background:#fff;color:#000;box-shadow:0 18px 40px rgba(0,0,0,0.6);transform:translateY(-4px)}

    .actions{text-align:right}

    @media (max-width:980px){.login-wrap{width:92%;flex-direction:column}.signup-card{width:100%}}
    @media (max-width:480px){.login-card-inner{padding:20px}.signup-card{padding:20px}}
</style>

    <script>
        // Toggle password visibility for any .password-toggle inside this page
        document.addEventListener('click', function(e){
            var btn = e.target.closest && e.target.closest('.password-toggle');
            if(!btn) return;
            var field = btn.closest('.password-field');
            if(!field) return;
            var input = field.querySelector('input');
            if(!input) return;
            // eye (visible) and eye-off (hidden) svgs
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

    @endsection
