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
                    <div class="alert alert-success">
                        <div class="alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22,4 12,14.01 9,11.01"></polyline>
                            </svg>
                        </div>
                        <div class="alert-content">{{ session('status') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <div class="alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="alert-content">{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <div class="alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Login gagal. Periksa email atau password.</div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    <label class="field">
                        <span class="label-text">Email</span>
                        <input name="email" type="email" value="{{ old('email') }}" required autofocus 
                               class="input @error('email') input-error @enderror" 
                               placeholder="Masukkan email Anda" />
                        @error('email')
                            <div class="field-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="label-text">Password</span>
                        <div class="password-field">
                            <input name="password" type="password" required 
                                   class="input @error('password') input-error @enderror" 
                                   placeholder="Masukkan password Anda" />
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                                <!-- eye (visible) icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </label>

                    <div class="form-meta">
                        <label class="remember">
                            <input type="checkbox" name="remember" />
                            <span>Ingat saya</span>
                        </label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot">Lupa kata sandi?</a>
                        @endif
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sign-in-alt"></i>
                            Masuk Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="signup-card">
            <div>
                <h2>Pengguna Baru?</h2>
                <p class="muted">Buat akun gratis untuk mulai belajar dan memesan sesi coaching.</p>
                <a href="{{ route('registerclass') }}" class="btn btn-outline">
                    <i class="fas fa-user-plus"></i>
                    Daftar Sekarang
                </a>
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

    /* Alert styles */
    .alert{display:flex;align-items:flex-start;gap:12px;padding:16px;border-radius:12px;margin-bottom:20px;border:1px solid transparent}
    .alert-success{background:rgba(34,197,94,0.1);border-color:rgba(34,197,94,0.2);color:#22c55e}
    .alert-error{background:rgba(239,68,68,0.1);border-color:rgba(239,68,68,0.2);color:#ef4444}
    .alert-icon{flex-shrink:0;margin-top:1px}
    .alert-content{flex:1;line-height:1.5}
    .alert-title{font-weight:600;margin-bottom:8px;font-size:14px}
    .error-list{margin:8px 0;padding-left:16px;font-size:13px;opacity:0.9}
    .error-list li{margin-bottom:4px}
    .alert-hint{margin-top:12px;font-size:12px;opacity:0.8;line-height:1.4}
    .alert-link{color:inherit;text-decoration:underline;font-weight:500}
    .alert-link:hover{opacity:0.8}

    /* Form fields */
    .field{display:block;margin-bottom:20px}
    .label-text{display:block;font-size:14px;margin-bottom:8px;font-weight:500;color:#fff}
    .input{width:100%;padding:14px 40px 14px 16px;border-radius:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#fff;outline:none;font-size:15px;transition:all 0.2s ease}
    .input::placeholder{color:rgba(255,255,255,0.4)}
    .input:focus{border-color:rgba(255,255,255,0.3);background:rgba(255,255,255,0.08);box-shadow:0 0 0 3px rgba(255,255,255,0.1)}
    .input-error{border-color:rgba(239,68,68,0.4);background:rgba(239,68,68,0.05)}
    .input-error:focus{border-color:rgba(239,68,68,0.6);box-shadow:0 0 0 3px rgba(239,68,68,0.1)}
    
    /* Field error messages */
    .field-error{display:flex;align-items:center;gap:6px;margin-top:8px;color:#ef4444;font-size:13px;font-weight:500}
    .field-error svg{flex-shrink:0}

    /* password toggle */
    .password-field{position:relative}
    .password-toggle{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#fff;cursor:pointer;padding:6px;border-radius:6px;display:flex;align-items:center;justify-content:center}
    .password-toggle svg{width:18px;height:18px;opacity:0.95}
    .password-toggle:focus{outline:none;box-shadow:0 0 0 3px rgba(255,255,255,0.06)}

    .form-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;margin-top:8px}
    .remember{opacity:0.9;font-size:14px;display:flex;align-items:center;gap:8px;cursor:pointer}
    .remember input[type="checkbox"]{width:16px;height:16px;accent-color:#fff}
    .forgot{color:#fff;opacity:0.75;text-decoration:underline;font-size:14px;transition:opacity 0.2s ease}
    .forgot:hover{opacity:1}

    /* Buttons */
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 24px;border-radius:10px;font-weight:600;text-decoration:none;cursor:pointer;transition:all 0.2s ease;font-size:15px;border:none;min-height:48px}
    .btn-primary{background:#fff;color:#000;box-shadow:0 2px 8px rgba(255,255,255,0.1)}
    .btn-primary:hover{background:#f0f0f0;transform:translateY(-1px);box-shadow:0 4px 16px rgba(255,255,255,0.2)}
    .btn-primary:active{transform:translateY(0);box-shadow:0 2px 8px rgba(255,255,255,0.1)}

    .btn-outline{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.2)}
    .btn-outline:hover{background:rgba(255,255,255,0.1);border-color:rgba(255,255,255,0.4);transform:translateY(-1px)}

    .actions{text-align:right;margin-top:8px}

    /* Enhanced responsive design */
    @media (max-width: 768px) {
        .main-container{flex-direction:column;min-height:100vh}
        .login-card,.signup-card{width:100%;border-radius:0}
        .login-card{padding:32px 24px}
        .signup-card{padding:24px;background:rgba(255,255,255,0.05)}
        .title{font-size:24px}
        .subtitle{font-size:16px}
    }

    /* Loading state and accessibility */
    .btn:disabled{opacity:0.6;cursor:not-allowed;transform:none}
    .btn.loading{position:relative;color:transparent}
    .btn.loading::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:20px;height:20px;border:2px solid currentColor;border-top:2px solid transparent;border-radius:50%;animation:spin 1s linear infinite}

    @keyframes spin{0%{transform:translate(-50%,-50%) rotate(0deg)}100%{transform:translate(-50%,-50%) rotate(360deg)}}

    .form-group input:focus,.btn:focus{outline:2px solid rgba(255,255,255,0.5);outline-offset:2px}

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

        // Add loading state to form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn-primary');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    
                    // Reset after timeout as fallback
                    setTimeout(function() {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    }, 10000);
                });
            }
        });
    </script>

    @endsection
