<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Nde Official')</title>
    <meta name="description" content="@yield('meta_description', 'Nde Official - Exclusive Guitar Sessions')">
    <meta name="keywords" content="@yield('meta_keywords', 'Nde Official, guitar sessions, content creator, Indonesia')">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', 'Nde Official')">
    <meta property="og:description" content="@yield('og_description', 'Nde Official - Exclusive Guitar Sessions')">
    <meta property="og:image" content="@yield('og_image', asset('compro/img/ndelogo.png'))">
    <meta property="og:url" content="@yield('og_url', url()->current())">

    <!-- Additional head content -->
    @stack('head')

    <!-- Css -->
    <link href="{{ asset('compro/css/bootstrap.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/base.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/main.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/flexslider.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/magnific-popup.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link href="{{ asset('compro/css/fonts.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet"
        media="print" onload="this.media='all'">
    <link rel="icon" type="image/png" href="{{ asset('compro/img/ndelogo.png') }}">
    {{-- Prefer local Font Awesome if present, otherwise fallback to CDN --}}
    {{-- <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}"> --}}
    <!-- Google font fallback and local font override -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/google-fonts.css') }}" rel="stylesheet" type="text/css" media="all" />
    <!-- Payment grid styles -->
    <link href="{{ asset('css/payment-grid.css') }}" rel="stylesheet" type="text/css" media="all" />
    <style>
        /* prevent horizontal scrollbar site-wide (keeps coaching layout clean) */
        html,
        body {
            overflow-x: hidden;
        }
    </style>
    @stack('styles')
</head>

<body>
    {{-- hide the global app navbar on the company profile page (route name: compro), on coaching session pages, on song tutorial viewer, and on admin audit pages --}}
    @if (Route::currentRouteName() !== 'compro' &&
            !request()->routeIs('coaching.session') &&
            !request()->routeIs('song.tutorial.show') &&
            !request()->routeIs('admin.audit.*') &&
            !request()->is('admin/audit*'))
        <!-- Top navigation (modern glass) -->
        <nav class="global-nav" aria-label="Main navigation">
            <style>
                .global-nav {
                    position: sticky;
                    top: 0;
                    z-index: 9999;
                }

                .global-nav .nav-inner {
                    max-width: 1200px;
                    margin: 0 auto;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 14px 22px;
                    gap: 18px;
                }

                /* glass background with subtle gradient and shadow */
                .global-nav .nav-wrap {
                    background: linear-gradient(90deg, rgba(6, 6, 6, 0.92), rgba(14, 14, 14, 0.86));
                    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
                    box-shadow: 0 8px 32px rgba(2, 6, 23, 0.55);
                    backdrop-filter: blur(6px);
                    -webkit-backdrop-filter: blur(6px);
                }

                /* Logo: larger and bolder presence */
                .nav-logo {
                    height: 64px;
                    transition: transform .18s ease, filter .18s ease;
                    display: block
                }

                .nav-logo:hover {
                    transform: translateY(-4px) scale(1.02);
                    filter: drop-shadow(0 12px 30px rgba(0, 0, 0, 0.6));
                }

                /* Primary links */
                .nav-links {
                    display: flex;
                    align-items: center;
                    gap: 18px
                }

                .nav-links a {
                    color: #fff;
                    text-decoration: none;
                    font-weight: 600;
                    padding: 6px 4px;
                    position: relative;
                    opacity: 0.95;
                    transition: color .14s ease, transform .14s ease;
                }

                .nav-links a:before {
                    content: '';
                    position: absolute;
                    left: 0;
                    bottom: -6px;
                    width: 0;
                    height: 3px;
                    border-radius: 3px;
                    background: linear-gradient(90deg, #ffd166, #ff6b6b);
                    transition: width .22s cubic-bezier(.2, .9, .2, 1);
                }

                .nav-links a:hover {
                    transform: translateY(-4px);
                    color: #fff;
                }

                .nav-links a:hover:before {
                    width: 100%;
                }

                /* subtle badge for active/important links */
                .nav-links a.active {
                    color: #fff;
                }

                /* right-side controls */
                .nav-actions {
                    display: flex;
                    align-items: center;
                    gap: 10px
                }

                .nav-username {
                    color: rgba(255, 255, 255, 0.92);
                    font-weight: 600;
                    opacity: 0.95;
                }

                .btn-ghost {
                    background: transparent;
                    color: #fff;
                    border: 1px solid rgba(255, 255, 255, 0.06);
                    padding: 8px 12px;
                    border-radius: 10px;
                    font-weight: 700;
                    transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
                }

                .btn-ghost:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.5);
                    background: rgba(255, 255, 255, 0.03);
                }

                /* modern login button variant */
                .nav-login-button {
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    background: transparent;
                    color: #fff;
                    padding: 10px 14px;
                    border-radius: 12px;
                    text-decoration: none;
                    font-weight: 700;
                    border: 1px solid rgba(255, 255, 255, 0.06);
                    transition: transform .14s ease, box-shadow .14s ease, opacity .14s ease
                }

                .nav-login-button svg {
                    width: 18px;
                    height: 18px
                }

                /* mobile: compact layout with toggle */
                .nav-toggle {
                    display: none;
                    background: transparent;
                    border: none;
                    color: #fff;
                    padding: 8px;
                    border-radius: 8px
                }

                @media (max-width:900px) {
                    .nav-links {
                        display: none;
                        position: absolute;
                        left: 16px;
                        right: 16px;
                        top: 84px;
                        flex-direction: column;
                        gap: 10px;
                        background: linear-gradient(180deg, rgba(10, 10, 10, 0.96), rgba(8, 8, 8, 0.98));
                        border-radius: 12px;
                        padding: 14px;
                        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
                    }

                    .nav-links.show {
                        display: flex;
                    }

                    .nav-logo {
                        height: 52px
                    }

                    .nav-inner {
                        padding: 10px 16px
                    }

                    .nav-toggle {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center
                    }
                }
            </style>

            <div class="nav-wrap">
                <div class="nav-inner">
                    <div style="display:flex;align-items:center;gap:16px">
                        {{-- hide the center navbar logo on kelas pages only (sidebar logo remains) --}}
                        @if (!request()->is('kelas*') && !request()->routeIs('kelas.*'))
                            <a href="{{ url('/ndeofficial') }}" aria-label="NDE Home"
                                style="display:inline-flex;align-items:center;">
                                <img class="nav-logo" src="{{ asset('compro/img/ndelogo.png') }}" alt="NDE logo" />
                            </a>
                        @endif
                        <button class="nav-toggle" aria-expanded="false" aria-controls="main-nav"
                            aria-label="Open menu">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 7H20M4 12H20M4 17H20" stroke="white" stroke-width="1.6"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        @php
                            $firstLessonId = \App\Models\Lesson::orderBy('position')->value('id');
                        @endphp
                    </div>

                    <div id="main-nav" class="nav-links" role="navigation">
                        @php
                            // Coaching navbar should always go to Upcoming first; from there user can book if they have tickets or buy if not.
                            $coachingLink = auth()->check() ? route('coaching.upcoming') : route('login');

                            $showSongTutorial = false;
                            $hasPackage = false;
                            if (auth()->check()) {
                                $u = auth()->user();
                                // Use configurable method instead of hardcoded package_id == 2
                                $showSongTutorial = $u->hasIntermediateAccess();
                                $hasPackage = !empty($u->package_id);
                            }
                        @endphp

                        {{-- If the user is authenticated but does not have a package, only show the Courses link. 
                             Keep the profile menu on the right so they can access Profile / Logout. --}}
                        @if (auth()->check() && !$hasPackage)
                            <a href="{{ route('registerclass') }}"
                                class="{{ request()->routeIs('registerclass') ? 'active' : '' }}">Courses</a>
                        @else
                            <a href="{{ url('/ndeofficial') }}"
                                class="{{ request()->is('ndeofficial*') ? 'active' : '' }}">Home</a>
                            <a href="{{ route('registerclass') }}"
                                class="{{ request()->routeIs('registerclass') ? 'active' : '' }}">Courses</a>

                            {{-- Only show lesson/coaching/song tutorial links to authenticated users who have a package --}}
                            @auth
                                @if ($firstLessonId)
                                    <a href="{{ route('kelas.show', $firstLessonId) }}"
                                        class="{{ request()->routeIs('kelas.show') ? 'active' : '' }}">Lesson</a>
                                @else
                                    <a href="{{ route('registerclass') }}">Lesson</a>
                                @endif
                                <a href="{{ $coachingLink }}"
                                    class="{{ request()->routeIs('coaching.*') ? 'active' : '' }}">Coaching</a>
                                @if ($showSongTutorial)
                                    <a href="{{ route('song.tutorial.index') }}"
                                        class="{{ request()->routeIs('song.tutorial.index') ? 'active' : '' }}">Song
                                        Tutorial</a>
                                @endif
                            @endauth
                        @endif
                    </div>

                    <div class="nav-actions">
                        @auth
                            <div style="position:relative">
                                <button id="profile-toggle" aria-haspopup="true" aria-expanded="false"
                                    style="display:inline-flex;align-items:center;gap:10px;background:transparent;border:none;padding:6px;border-radius:10px;cursor:pointer">
                                    @php $avatar = auth()->user()->photoUrl(); @endphp
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" alt="avatar"
                                            style="width:36px;height:36px;border-radius:999px;border:2px solid rgba(255,255,255,0.04);object-fit:cover">
                                    @else
                                        {{-- Default white avatar icon (inline SVG) --}}
                                        <svg width="36" height="36" viewBox="0 0 24 24" aria-hidden="true"
                                            focusable="false"
                                            style="border-radius:999px;border:2px solid rgba(255,255,255,0.04);background:transparent;">
                                            <defs></defs>
                                            <circle cx="12" cy="8" r="4" fill="#ffffff" />
                                            <path d="M4 20c0-4 4-6 8-6s8 2 8 6" fill="#ffffff" />
                                        </svg>
                                    @endif
                                </button>
                                <div id="profile-menu" role="menu"
                                    style="display:none;position:absolute;right:0;margin-top:8px;background:linear-gradient(180deg,#0b0b0b,#0e0e0e);border-radius:10px;padding:8px;border:1px solid rgba(255,255,255,0.04);box-shadow:0 18px 40px rgba(0,0,0,0.6);min-width:180px;z-index:999">
                                    <div style="padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.02)">
                                        <div style="font-weight:700">{{ auth()->user()->name }}</div>
                                        <div style="font-size:12px;color:rgba(255,255,255,0.65)">
                                            {{ auth()->user()->email }}</div>
                                    </div>
                                    <a href="{{ route('profile') }}"
                                        style="display:block;padding:8px 10px;color:#fff;text-decoration:none">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            style="width:100%;text-align:left;padding:8px 10px;border:none;background:transparent;color:#fff;cursor:pointer">Logout</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            @if (Route::currentRouteName() !== 'login')
                                <a href="{{ route('login') }}" class="nav-login-button" aria-label="Login">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true" focusable="false">
                                        <path d="M15 12H3" stroke="white" stroke-width="1.6" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                        <path d="M10 17L15 12L10 7" stroke="white" stroke-width="1.6"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="18.5" cy="7.5" r="3" stroke="white" stroke-width="1.6" />
                                    </svg>
                                    <span>Login</span>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <script>
                (function() {
                    const toggle = document.querySelector('.global-nav .nav-toggle');
                    const navLinks = document.getElementById('main-nav');
                    if (!toggle || !navLinks) return;
                    toggle.addEventListener('click', function() {
                        const expanded = this.getAttribute('aria-expanded') === 'true';
                        this.setAttribute('aria-expanded', String(!expanded));
                        navLinks.classList.toggle('show');
                    });
                })();
            </script>
        </nav>
    @endif

    {{-- Admin sub-navbar (only on admin routes, for authenticated admin users) --}}
    @auth
        @php
            $isAdminVisible = auth()->user()->is_admin || auth()->user()->is_superadmin;
            $onAdminRoute = request()->routeIs('admin.*') || request()->is('admin*');
        @endphp
        @if ($isAdminVisible && $onAdminRoute)
            <nav style="background:#0b1220;color:#fff;padding:8px 20px;border-bottom:1px solid #0f1724;">
                <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:18px;">
                    <a href="{{ route('admin.dashboard') ?? url('/admin') }}"
                        style="color:#fff;text-decoration:none;font-weight:500;">Admin Dashboard</a>
                    <a href="{{ url('/admin/coaching/bookings') }}"
                        style="color:#fff;text-decoration:none;font-weight:500;">Coaching Bookings</a>
                    <a href="{{ url('/coaching') }}" style="color:#fff;text-decoration:none;font-weight:500;">Public
                        Coaching Page</a>
                </div>
            </nav>
        @endif
    @endauth

    @yield('content')

    <!-- Scripts -->

    <script src="{{ asset('compro/js/jquery-1.12.4.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.flexslider-min.js') }}"></script>
    <script src="{{ asset('compro/js/smooth-scroll.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('compro/js/twitterFetcher_min.js') }}"></script>
    <script src="{{ asset('compro/js/instafeed.min.js') }}"></script>
    <script src="{{ asset('compro/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('compro/js/placeholders.min.js') }}"></script>
    <script src="{{ asset('compro/js/script.js') }}"></script>

    <!-- Payment grid behavior (safe to include globally; it no-ops when grid absent) -->
    <script src="{{ asset('js/payment-grid.js') }}"></script>

    @stack('scripts')
    
    <script>
        (function() {
            const btn = document.getElementById('profile-toggle');
            const menu = document.getElementById('profile-menu');
            if (btn && menu) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const open = menu.style.display !== 'none';
                    menu.style.display = open ? 'none' : 'block';
                    btn.setAttribute('aria-expanded', String(!open));
                });
                document.addEventListener('click', function() {
                    if (menu) menu.style.display = 'none';
                });
            }
        })();
    </script>
</body>

</html>
