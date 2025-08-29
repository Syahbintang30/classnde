<nav style="background:#000;color:#fff;padding:14px 20px;border-bottom:1px solid #111;position:sticky;top:0;z-index:60;">
    <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:18px">
            <a href="{{ route('registerclass') }}" style="display:flex;align-items:center;text-decoration:none;color:inherit;">
                <img src="{{ asset('compro/img/ndelogo.png') }}" alt="logo" style="height:30px;" />
            </a>
            <a href="{{ route('registerclass') }}" style="color:#fff;text-decoration:none;font-weight:600;">Home</a>
            <a href="{{ isset($lesson) ? route('kelas.show', $lesson->id) : '#' }}" style="color:#fff;text-decoration:none;font-weight:500;">Class</a>
            @php
                $coachingLink = route('registerclass');
                if (auth()->check()) {
                    $unused = \App\Models\CoachingTicket::where('user_id', auth()->id())->where('is_used', false)->count();
                    if ($unused > 0) $coachingLink = route('coaching.upcoming');
                }
            @endphp
            <a href="{{ $coachingLink }}" style="color:#fff;text-decoration:none;font-weight:500;">Coaching</a>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            @auth
                <span style="opacity:0.9;margin-right:8px">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
                    <button type="submit" style="background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.08);padding:6px 10px;border-radius:6px;">Logout</button>
                </form>
            @else
                {{-- login/register hidden globally --}}
            @endauth
        </div>
    </div>
</nav>
