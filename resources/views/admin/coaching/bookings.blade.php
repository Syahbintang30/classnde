    @extends('layouts.admin')

@section('title', 'Coaching Bookings')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('compro/css/admin-bookings.css') }}" />
    @endpush
    <div class="container-fluid py-4">
        @include('admin.coaching._nav')

    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4 header">
            <div>
                <h2>Coaching Bookings</h2>
                <p style="color:#666; font-size:14px">Manage and review user bookings, create video rooms, accept or reject requests.</p>
            </div>

            <div style="min-width:320px;">
                <form method="GET" class="d-flex gap-2">
                    <input name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search user, email, order id..." />

                    <select name="status" class="form-select form-select-sm text-white" style="background-color: #1a1a1a; border:1px solid #333;">
                        <option value="">All status</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="accepted" {{ request('status')=='accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button class="btn btn-sm btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover light-custom-table mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Notes</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Order / Payment</th>
                                <th>Twilio</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td style="min-width:180px">
                                    <div style="font-weight:700">{{ optional($b->user)->name }}</div>
                                    <div class="text-muted" style="font-size:13px">{{ optional($b->user)->email }} · {{ optional($b->user)->phone ?? '-' }}</div>
                                </td>
                                <td style="max-width:360px;white-space:pre-wrap;word-break:break-word">{{ $b->notes ?: '-' }}</td>
                                <td style="min-width:180px">
                                    @php
                                        $bt = \Carbon\Carbon::parse($b->booking_time);
                                        $slotTime = $bt->format('H:i');
                                        $slotDate = $bt->toDateString();
                                        $key = $slotDate . ' ' . $slotTime;
                                        $taken = isset($slotCounts[$key]) ? $slotCounts[$key] : 0;
                                    @endphp
                                    <div>{{ $bt->translatedFormat('j F Y') }}</div>
                                    <div class="text-muted" style="font-size:13px">{{ $bt->format('H:i') }} · Taken: {{ $taken }}</div>
                                    <div style="margin-top:6px"><span class="countdown" data-time-ms="{{ \Carbon\Carbon::parse($b->booking_time)->getTimestamp() * 1000 }}">-</span></div>
                                </td>
                                <td>
                                    @php $s = strtolower($b->status); @endphp
                                    @if($s === 'accepted')
                                        <span class="badge bg-success">Accepted</span>
                                    @elseif($s === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td style="min-width:200px">
                                    @php
                                        $paymentInfo = null;
                                        if ($b->ticket && $b->ticket->source) {
                                            if (str_starts_with($b->ticket->source, 'midtrans:')) {
                                                $order = substr($b->ticket->source, strlen('midtrans:'));
                                                $txn = \App\Models\Transaction::where('order_id', $order)->latest()->first();
                                                if ($txn) {
                                                    $paymentInfo = ['order' => $order, 'amount' => $txn->amount, 'id' => $txn->id];
                                                } else {
                                                    $paymentInfo = ['order' => $order];
                                                }
                                            } else {
                                                // non-midtrans source: use the raw source value as the order identifier
                                                $order = $b->ticket->source;
                                                $paymentInfo = ['order' => $order];
                                            }
                                        } else {
                                            // be defensive: ticket may be null
                                            $paymentInfo = ['info' => $b->ticket->source ?? null];
                                        }
                                    @endphp
                                    @if($paymentInfo)
                                        @if(isset($paymentInfo['id']))
                                            <div>{{ $paymentInfo['order'] }}</div>
                                            <div class="text-muted" style="font-size:13px">Rp {{ number_format($paymentInfo['amount'] ?? 0,0,',','.') }}</div>
                                            <div class="mt-1">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.transactions.index') }}?q={{ $paymentInfo['order'] }}">View Transaction</a>
                                            </div>
                                        @elseif(isset($paymentInfo['order']))
                                            {{ $paymentInfo['order'] }}
                                        @else
                                            {{ $paymentInfo['info'] }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            <td style="min-width:180px">
                                @php $sessionUrl = url('/coaching/session/'.$b->id); $btLocal = \Carbon\Carbon::parse($b->booking_time)->format('Y-m-d H:i:s'); @endphp
                                @if($b->twilio_room_sid)
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <div style="font-size:13px">{{ $b->twilio_room_sid }}</div>
                                        <div>
                                            <a class="btn btn-sm btn-outline-primary open-session-btn" data-booking-time="{{ $btLocal }}" data-href="{{ $sessionUrl }}" target="_blank" href="#">Open Session</a>
                                        </div>
                                    </div>
                                @else
                                    <form method="POST" action="{{ url('/admin/coaching/bookings/'.$b->id.'/create-room') }}" style="display:inline">@csrf
                                        <button class="btn btn-sm btn-outline-secondary">Create Room</button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(strtolower($b->status) === 'pending')
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <form method="POST" action="{{ url('/admin/coaching/bookings/'.$b->id.'/accept') }}" style="display:inline">@csrf
                                            <button class="btn btn-sm btn-success" title="Accept" style="padding:4px 8px;">✓</button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-danger reject-open-btn" data-action="{{ url('/admin/coaching/bookings/'.$b->id.'/reject') }}" title="Reject" style="padding:4px 8px;">✕</button>
                                    </div>
                                @else
                                    <span class="">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr style="pointer-events: none; background: transparent;">
                            <td colspan="7" class="text-center pt-5">No bookings found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="mt-3">{{ $bookings->appends(request()->query())->links() }}</div>
    </div>
    <!-- Reject reason modal -->
    <div id="rejectModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;align-items:center;justify-content:center;z-index:2000;">
        <div class="modal-inner" style="padding:18px;border-radius:8px;max-width:520px;margin:40px auto;position:relative;">
            <button class="reject-modal-close" style="position:absolute;right:8px;top:8px;border:none;background:transparent;font-size:18px;">&times;</button>
            <h4 style="margin-top:0">Reason for rejection</h4>
            <form id="rejectModalForm">
                <div style="margin-bottom:8px;"><textarea name="reason" rows="4" class="form-control" placeholder="Short reason (visible to user)"></textarea></div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary reject-modal-close">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit Rejection</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            function formatDelta(ms) {
                if (ms <= 0) return 'Dimulai';
                const total = Math.floor(ms / 1000);
                const days = Math.floor(total / 86400);
                const hours = Math.floor((total % 86400) / 3600);
                const mins = Math.floor((total % 3600) / 60);
                const parts = [];
                if (days) parts.push(days + ' hari');
                if (hours) parts.push(hours + ' jam');
                parts.push(mins + ' menit');
                return parts.join(' ');
            }

            function updateCountdowns() {
                document.querySelectorAll('.countdown').forEach(function (el) {
                    const msAttr = el.getAttribute('data-time-ms');
                    if (!msAttr) return;
                    const target = parseInt(msAttr, 10);
                    if (isNaN(target)) return;
                    const now = Date.now();
                    const delta = target - now;
                    el.textContent = formatDelta(delta);
                });
            }

            // Enable Open Session buttons when within 10 minutes before start (and up to 60 minutes after)
            // Also show a dynamic remaining-time label: "Open Session (Xm)" where applicable
            function updateOpenSessionButtons() {
                const now = Date.now();
                document.querySelectorAll('.open-session-btn').forEach(function (btn) {
                    const dtStr = btn.getAttribute('data-booking-time');
                    const href = btn.getAttribute('data-href');
                    if (!dtStr) return;
                    const iso = dtStr.replace(' ', 'T');
                    const dt = new Date(iso);
                    if (isNaN(dt.getTime())) return;
                    const startMs = dt.getTime();
                    const startWindow = startMs - (10 * 60 * 1000);
                    const endWindow = startMs + (60 * 60 * 1000);

                    const minutesUntilStart = Math.ceil((startMs - now) / 60000);

                    // update label: if session already started or within 10m window show plain Open Session (or with minutes if >0)
                    if (minutesUntilStart > 0) {
                        // show minutes remaining
                        btn.textContent = 'Open Session (' + minutesUntilStart + 'm)';
                    } else {
                        btn.textContent = 'Open Session';
                    }

                    // enable only when within the open window
                    if (now >= startWindow && now <= endWindow) {
                        btn.classList.remove('disabled');
                        btn.removeAttribute('aria-disabled');
                        if (href) btn.setAttribute('href', href);
                        btn.style.pointerEvents = '';
                        btn.style.opacity = '';
                    } else {
                        btn.classList.add('disabled');
                        btn.setAttribute('aria-disabled', 'true');
                        btn.removeAttribute('href');
                        btn.style.pointerEvents = 'none';
                        btn.style.opacity = '0.6';
                    }
                });
            }

            // Reject modal handling: open modal, collect reason, then submit
            let currentRejectAction = null;
            function openRejectModal(actionUrl) {
                currentRejectAction = actionUrl;
                const modal = document.getElementById('rejectModal');
                modal.style.display = 'block';
                modal.querySelector('textarea[name="reason"]').value = '';
                modal.querySelector('textarea[name="reason"]').focus();
            }

            function closeRejectModal() {
                const modal = document.getElementById('rejectModal');
                modal.style.display = 'none';
                currentRejectAction = null;
            }

            document.addEventListener('click', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('reject-open-btn')) {
                    const action = e.target.getAttribute('data-action');
                    openRejectModal(action);
                }
                if (e.target && e.target.classList && e.target.classList.contains('reject-modal-close')) {
                    closeRejectModal();
                }
            });

            document.getElementById && document.getElementById('rejectModalForm') && document.getElementById('rejectModalForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const ta = this.querySelector('textarea[name="reason"]');
                if (!ta || !ta.value.trim()) {
                    alert('Please provide a short reason for rejection');
                    ta && ta.focus();
                    return;
                }
                if (!currentRejectAction) {
                    alert('Missing action URL');
                    return;
                }
                // create a form and submit to action URL
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = currentRejectAction;
                f.style.display = 'none';
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const inputToken = document.createElement('input'); inputToken.type = 'hidden'; inputToken.name = '_token'; inputToken.value = token; f.appendChild(inputToken);
                const inputReason = document.createElement('input'); inputReason.type = 'hidden'; inputReason.name = 'reason'; inputReason.value = ta.value; f.appendChild(inputReason);
                document.body.appendChild(f);
                f.submit();
            });

            updateCountdowns();
            updateOpenSessionButtons();
            setInterval(function(){ updateCountdowns(); updateOpenSessionButtons(); }, 30 * 1000);
        })();
    </script>
@endsection
