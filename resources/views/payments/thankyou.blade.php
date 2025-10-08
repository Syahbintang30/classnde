@extends('layouts.app')

@section('title','Pembayaran Berhasil')

@section('content')
<div class="pay-wrapper">
  <div class="pay-card success">
    <div class="icon-wrap" aria-hidden="true">
      <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="28" cy="28" r="28" fill="#10B981" fill-opacity="0.16"/>
        <path d="M40.2 21.4L26.64 35 19.8 28.16" stroke="#34D399" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <h1 class="title">Pembayaran Berhasil</h1>
    <p class="lead">Terima kasih! Transaksi dengan ID <strong>{{ $orderId }}</strong> telah <span class="status-pill ok">TERKONFIRMASI</span>. Akses paket Anda sudah aktif.</p>

    <div class="info-grid" style="margin-top:8px">
      <div class="info-item"><span>Order ID</span><strong>{{ $orderId }}</strong></div>
      @if(isset($txn) && $txn)
        <div class="info-item"><span>Status</span><strong class="status-pill ok">{{ strtoupper($txn->status) }}</strong></div>
        <div class="info-item"><span>Jumlah</span><strong>Rp {{ number_format($txn->amount ?? $txn->original_amount ?? 0,0,',','.') }}</strong></div>
      @endif
      @if(isset($package) && $package)
        <div class="info-item" style="grid-column: span 2;"><span>Paket</span><strong>{{ $package->name }}</strong></div>
      @endif
    </div>

    @if(isset($package) && $package)
      @php
        $slug = $package->slug ?? null;
        $courseSlugs = ['beginner','intermediate'];
        $coachingSlug = config('coaching.coaching_package_slug','coaching-ticket');
      @endphp
      <div class="section-block">
        @if(in_array($slug, $courseSlugs))
          <h3 class="section-head">Siap Belajar</h3>
          <p class="section-text">Anda sekarang memiliki akses penuh ke paket <strong>{{ $package->name }}</strong>. Mulai dari pelajaran pertama dan lanjutkan secara bertahap untuk hasil terbaik.</p>
          <div class="actions center">
            @if(isset($firstLesson) && $firstLesson)
              <a href="{{ route('kelas.show', $firstLesson->id) }}" class="btn-primary">Mulai Belajar</a>
            @else
              <a href="{{ route('registerclass') }}" class="btn-primary">Lihat Kelas</a>
            @endif
          </div>
        @elseif($slug === 'upgrade-intermediate')
          <h3 class="section-head">Upgrade Selesai</h3>
          <p class="section-text">Anda telah berhasil upgrade ke paket <strong>Intermediate</strong>. Eksplor materi baru untuk meningkatkan skill Anda.</p>
          <div class="actions center">
            <a href="{{ route('registerclass') }}" class="btn-primary">Masuk Ke Intermediate</a>
          </div>
        @elseif($slug === $coachingSlug)
          <h3 class="section-head">Tiket Coaching Aktif</h3>
          <p class="section-text">Tiket personal coaching Anda sudah siap. Jadwalkan sesi supaya dapat umpan balik langsung dari coach.</p>
          <div class="actions center">
            <a href="{{ route('coaching.index') }}" class="btn-primary">Jadwalkan Sesi</a>
          </div>
        @endif
      </div>
    @endif

    <div class="divider"></div>

    <div class="extras">
      <div class="extra-box">
        <div class="extra-head">Referral</div>
        <div class="extra-content">
          @php $user = auth()->user(); @endphp
          @if($user && !empty($user->referral_code))
            <div style="font-size:13px;opacity:.85;margin-bottom:6px">Bagikan kode referral Anda:</div>
            <div class="copy-row">
              <code id="refcode">{{ $user->referral_code }}</code>
              <button type="button" class="btn-copy" data-target="refcode">Copy</button>
            </div>
            <div class="tiny">Teman Anda mendapat diskon, Anda dapat bonus.</div>
          @else
            <div class="tiny" style="opacity:.7">Kode referral akan muncul setelah profil lengkap.</div>
          @endif
        </div>
      </div>
      <div class="extra-box">
        <div class="extra-head">Bantuan</div>
        <div class="extra-content">
          <div class="tiny" style="margin-bottom:8px">Ada kendala akses?</div>
          <a href="mailto:support@domain.com" class="link-text">Email Support</a>
        </div>
      </div>
    </div>

    <div class="actions" style="margin-top:34px;justify-content:center">
      <a href="{{ route('registerclass') }}" class="btn-outline">Kembali ke Beranda</a>
      @if(isset($txn) && $txn && isset($txn->user_id) && $txn->user_id && isset($package) && $package)
        <a href="{{ route('kelas.show', $firstLesson->id ?? ($lesson->id ?? 1)) }}" class="btn-outline">Masuk Materi</a>
      @endif
    </div>
  </div>
</div>

<style>
.pay-wrapper{min-height:74vh;display:flex;align-items:center;justify-content:center;padding:40px;background:#000;color:#fff}
.pay-card{width:100%;max-width:940px;padding:48px 52px;border:1px solid #151515;border-radius:22px;background:linear-gradient(180deg,#0c0c0c,#050505);box-shadow:0 10px 40px rgba(0,0,0,.55);position:relative;overflow:hidden}
.pay-card.success:before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 25%,rgba(16,185,129,0.12),transparent 60%) ;pointer-events:none}
.icon-wrap{width:90px;height:90px;border-radius:24px;display:flex;align-items:center;justify-content:center;margin:0 0 26px;background:rgba(255,255,255,0.05)}
.title{margin:0 0 14px;font-size:34px;font-weight:800;letter-spacing:.5px}
.lead{margin:0 0 26px;max-width:720px;font-size:16px;line-height:1.55;opacity:.92}
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin:4px 0 24px}
.info-item{padding:16px 18px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);border-radius:12px}
.info-item span{display:block;font-size:11px;letter-spacing:.5px;opacity:.55;text-transform:uppercase;margin-bottom:6px}
.status-pill{display:inline-block;padding:4px 10px;font-size:11px;border-radius:40px;background:rgba(255,255,255,0.12);letter-spacing:.5px}
.status-pill.ok{background:rgba(16,185,129,0.18);color:#34d399}
.section-block{margin:18px 0 8px}
.section-head{margin:0 0 8px;font-size:20px;font-weight:700}
.section-text{margin:0 0 14px;line-height:1.55;font-size:14px;opacity:.85}
.actions{display:flex;flex-wrap:wrap;gap:14px}
.btn-primary,.btn-outline{padding:14px 26px;border-radius:12px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:.18s ease;border:1px solid transparent;cursor:pointer}
.btn-primary{background:#fff;color:#000}
.btn-primary:hover{background:#e9e9e9}
.btn-outline{background:transparent;color:#fff;border-color:rgba(255,255,255,0.22)}
.btn-outline:hover{background:rgba(255,255,255,0.12)}
.divider{height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.25),transparent);margin:34px 0 30px}
.extras{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:18px}
.extra-box{border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);border-radius:14px;padding:18px 20px}
.extra-head{font-size:13px;letter-spacing:.6px;font-weight:600;opacity:.75;text-transform:uppercase;margin-bottom:10px}
.copy-row{display:flex;align-items:center;gap:10px}
.copy-row code{background:rgba(255,255,255,0.08);padding:6px 10px;border-radius:8px;font-size:13px}
.btn-copy{background:#1e1e1e;border:1px solid rgba(255,255,255,0.18);color:#fff;padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer}
.btn-copy:hover{background:#2a2a2a}
.link-text{color:#fff;text-decoration:underline;font-weight:600;font-size:14px}
.tiny{font-size:12px;line-height:1.45;opacity:.75}
@media (max-width:720px){.pay-card{padding:38px 26px}.title{font-size:28px}.lead{font-size:15px}.info-grid{grid-template-columns:repeat(auto-fit,minmax(140px,1fr))}}
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-copy').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-target');
        const el = document.getElementById(id);
        if(!el) return;
        const text = el.textContent.trim();
        navigator.clipboard.writeText(text).then(()=>{
          const old = btn.textContent; btn.textContent = 'Copied';
          setTimeout(()=>{ btn.textContent = old; }, 1600);
        });
      });
    });
  });
</script>
@endsection
