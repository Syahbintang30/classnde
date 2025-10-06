<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('compro/img/ndelogo.png') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS -->
    <!-- Load Bootstrap first so admin.css can override Bootstrap defaults -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('compro/css/admin.css') }}" rel="stylesheet" type="text/css" media="all" />
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/duotone/style.css"
    />
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body, input, textarea, select, button { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }</style>
    {{-- page-specific styles --}}
    @stack('styles')
</head>
<body style="background: #111">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-xl admin-nav">
        <div class="nav-wrap w-100">
            <div class="container nav-inner">
                <a class="navbar-brand" href="{{ route('admin.lessons.index') }}">Admin Panel</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="adminNavbar">
                    <ul class="navbar-nav ms-auto nav-links">
                        @php
                            $user = auth()->user();
                            $coachingSlug = config('coaching.coaching_package_slug');
                            $coachingPkg = null;
                            try {
                                if ($coachingSlug) {
                                    $coachingPkg = \App\Models\Package::where('slug', $coachingSlug)->first();
                                }
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        @endphp

                        @if($user && $user->is_superadmin)
                            {{-- Superadmin: show full navbar (existing items) --}}
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/lessons*') ? 'active' : '' }}" href="{{ route('admin.lessons.index') }}">Lessons</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/packages*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}">Packages</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/transactions*') ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">Transactions</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/vouchers*') ? 'active' : '' }}" href="{{ route('admin.vouchers.index') }}">Vouchers</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/payment-methods*') ? 'active' : '' }}" href="{{ route('admin.payment-methods.index') }}">Payment</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminReferralMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Referral</a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminReferralMenu">
                                    <li><a class="dropdown-item" href="{{ route('admin.referral.settings.form') }}">Settings</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.referral.leaderboard') }}">Leaderboard</a></li>
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.packages') }}">Users</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminCoachingMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Coaching</a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminCoachingMenu">
                                    <li><a class="dropdown-item {{ request()->is('admin/coaching/bookings*') ? 'active' : '' }}" href="{{ url('/admin/coaching/bookings') }}">Bookings</a></li>
                                    <li><a class="dropdown-item {{ request()->is('admin/coaching/slot-capacities*') ? 'active' : '' }}" href="{{ url('/admin/coaching/slot-capacities') }}">Slot Capacities</a></li>
                                    @if($coachingPkg)
                                        <li><a class="dropdown-item" href="{{ route('admin.packages.edit', $coachingPkg->id) }}">Edit Coaching Price</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">Settings</a></li>
                        @elseif($user && $user->is_admin)
                            {{-- Regular admin: limited navbar per requirements --}}
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/lessons*') ? 'active' : '' }}" href="{{ route('admin.lessons.index') }}">Lessons</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminCoachingMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Coaching</a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminCoachingMenu">
                                    <li><a class="dropdown-item {{ request()->is('admin/coaching/bookings*') ? 'active' : '' }}" href="{{ url('/admin/coaching/bookings') }}">Bookings</a></li>
                                    <li><a class="dropdown-item {{ request()->is('admin/coaching/slot-capacities*') ? 'active' : '' }}" href="{{ url('/admin/coaching/slot-capacities') }}">Slot Capacities</a></li>
                                    @if($coachingPkg)
                                        <li><a class="dropdown-item" href="{{ route('admin.packages.edit', $coachingPkg->id) }}">Edit Coaching Price</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.packages') }}">Users</a></li>
                        @endif

                        @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminUserMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ auth()->user()->name }}</a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserMenu">
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="dropdown-item p-0 m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-decoration-none w-100 text-start">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <div aria-live="polite" aria-atomic="true" class="position-relative">
      <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>
    </div>
    <script>
        document.addEventListener('click', function(e){
            const target = e.target;
            if (target.matches('.btn-accept') || target.matches('.btn-reject')){
                e.preventDefault();
                const form = target.closest('form');
                const action = target.classList.contains('btn-accept') ? 'accept' : 'reject';
                if (!confirm('Are you sure you want to ' + action + ' this booking?')) return;
                form.submit();
            }
        });

        // show toast from session
        window.addEventListener('DOMContentLoaded', function(){
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
        });

        function showToast(message, type='info'){
            const container = document.getElementById('toastContainer');
            const toastElem = document.createElement('div');
            toastElem.className = 'toast align-items-center text-bg-' + (type==='success' ? 'success' : 'secondary') + ' border-0';
            toastElem.role = 'alert';
            toastElem.ariaLive = 'assertive';
            toastElem.ariaAtomic = 'true';
            toastElem.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
            container.appendChild(toastElem);
            const t = new bootstrap.Toast(toastElem, { delay: 4000 });
            t.show();
        }
    </script>
    <script>
        const optionMenu = document.querySelector(".select-menu"),
            selectBtn = optionMenu.querySelector(".select-btn"),
            options = optionMenu.querySelectorAll(".option"),
            btn_text = optionMenu.querySelector(".btn-text"),
            hiddenInput = optionMenu.querySelector("input[type='hidden']");

        // toggle dropdown
        selectBtn.addEventListener("click", () => optionMenu.classList.toggle("active"));

        // pilih option
        options.forEach(option => {
            option.addEventListener("click", () => {
                let value = option.getAttribute("data-value");
                let text = option.querySelector(".option-text").innerText;

                btn_text.innerText = text;      
                hiddenInput.value = value;     
                optionMenu.classList.remove("active");
            });
        });
        
        // Tutup dropdown jika klik di luar
        document.addEventListener("click", (e) => {
            if (!optionMenu.contains(e.target)) {
                optionMenu.classList.remove("active");
            }
        });

    </script>

    {{-- section for page scripts --}}
    @yield('scripts')
</body>
</html>
