@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/coaching.css') }}">

    <style>
        /* Local styles for the refactored step-by-step booking flow */
        .booking-container {
            max-width: 720px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .booking-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .booking-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .booking-header p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.75);
            max-width: 500px;
            margin: 0 auto;
        }

        .booking-step {
            background: rgba(255, 255, 255, 0.02);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 24px;
        }

        .booking-step h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        /* Initially hide steps 2 and 3 */
        #step2-time, #step3-details {
            display: none;
        }

        /* Calendar Styles */
        .calendar { padding: 0; }
        .calendar-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .calendar .month-name { font-weight: 600; font-size: 18px; }
        .calendar .days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .calendar .weekday { text-align: center; font-size: 12px; color: rgba(255,255,255,0.6); }
        .calendar .day { width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.08); background: transparent; color: inherit; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; position: relative; transition: all .15s ease; }
        .calendar .day:hover:not(.disabled) { border-color: rgba(255,255,255,0.25); background: rgba(255,255,255,0.04); }
        .calendar .day.active { border-color: #ffffff; background: #ffffff; color: #000000; font-weight: 700; transform: scale(1.05); }
        .calendar .day.disabled { opacity: 0.35; cursor: not-allowed; }
        .calendar .day-placeholder { visibility: hidden; }
        .calendar .nav-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.1); background: transparent; display: inline-flex; align-items: center; justify-content: center; color: #fff; }
        .calendar .nav-btn:hover { background: rgba(255,255,255,0.06); }
        .calendar .date-badge { position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; line-height: 16px; padding: 0 5px; border-radius: 9px; background: #ffffff; color: #001219; font-size: 10px; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,0.35); }

        /* Time Selection Styles */
        #timeSuggestions { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; }
        .times .time { display: inline-flex; align-items: center; justify-content: center; background: transparent; color: #ffffff; border: 1px solid rgba(255,255,255,0.12); padding: 12px 8px; border-radius: 8px; width: 100%; min-height: 56px; font-size: 16px; font-weight: 700; text-align: center; cursor: pointer; transition: all .12s ease; }
        .times .time:hover:not(.disabled) { background: rgba(255,255,255,0.05); transform: translateY(-2px); }
        .times .time.selected { background: #ffffff; color: #000000; border-color: #ffffff; box-shadow: 0 8px 20px rgba(0,0,0,0.4); transform: translateY(-2px) scale(1.02); }
        .times .time.disabled { opacity: 0.35; cursor: not-allowed; }

        /* Details & Confirmation Styles */
        .summary-box { background: rgba(0,0,0,0.2); padding: 16px; border-radius: 10px; margin-top: 16px; border: 1px solid rgba(255,255,255,0.08); }
        .summary-box h4 { margin: 0 0 12px 0; font-size: 16px; }
        .summary-box p { margin: 0; font-size: 14px; color: rgba(255,255,255,0.8); }
        .summary-box strong { color: #fff; font-weight: 600; }

        .feedback-textarea { width:100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.10); color: #fff; padding: 12px; border-radius: 10px; outline: none; transition: all .12s ease; font-size:14px; line-height:1.5; min-height:120px; resize:vertical; }
        .feedback-textarea::placeholder { color: rgba(255,255,255,0.45); }
        .feedback-textarea:focus { border-color: dodgerblue; background: #000; }
        .feedback-note { color: rgba(255,255,255,0.6); font-size:13px; margin-top:8px; }

        .btn.primary { border-radius: 12px !important; padding: 14px 24px !important; font-weight: 700; transition: all .12s ease; width: 100%; margin-top: 20px; }
        .btn.primary:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.35); }
        .btn.primary:disabled { background: rgba(255,255,255,0.1); border-color: transparent !important; color: rgba(255,255,255,0.4); cursor: not-allowed; }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .booking-step { padding: 16px; }
            .calendar .day { width: 40px; height: 40px; }
            .calendar .weekday { font-size: 11px; }
        }
    </style>
@endpush


@section('content')
    @if(auth()->check() && !$hasAvailableTicket && ($tickets->where('is_used', true)->count() > 0) && ($bookings->count() == 0))
        {{-- This is the "empty state" for users who have used tickets but have no upcoming bookings. It's good as is. --}}
        <div class="coaching-page" style="display:flex;align-items:center;justify-content:center;min-height:60vh;padding:40px 16px;">
            <div style="text-align:center;max-width:760px;width:100%;color:rgba(255,255,255,0.95);">
                <h1 style="font-weight:600;font-size:28px;margin-bottom:12px">You don't have any sessions yet</h1>
                <p style="opacity:0.85;margin-bottom:22px;line-height:1.5">Choose your preferred date and time to book a session. Secure your spot and start your learning journey today. Pick a date, lock your spot, and get ready to play!</p>
                <div>
                    <a href="{{ route('coaching.checkout') }}" class="btn primary" style="display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:24px;"> <i class="fa fa-ticket" aria-hidden="true"></i> BUY A TICKET</a>
                </div>
            </div>
        </div>
    @else
    {{-- This is the main booking flow --}}
    <div class="coaching-page">
        <div class="booking-container">

            <div class="booking-header">
                <h1>Book Your Coaching Session</h1>
                <p>Follow the steps below to schedule your one-on-one session. Your available tickets will be applied automatically.</p>
                <div style="margin-top: 16px; font-weight: 600; color: rgba(255,255,255,0.9);">Available Tickets: <span id="availableCount">{{ $tickets->where('is_used', false)->count() }}</span></div>
            </div>

            <!-- STEP 1: SELECT DATE -->
            <div id="step1-date" class="booking-step">
                <h3>1. Select a Date</h3>
                <div class="calendar">
                    <div class="calendar-header">
                        <button id="prevMonth" class="nav-btn">&lt;</button>
                        <div class="month-name" id="monthName"></div>
                        <button id="nextMonth" class="nav-btn">&gt;</button>
                    </div>
                    <div class="days" id="calendarDays">
                        {{-- Calendar days will be rendered by JS --}}
                    </div>
                </div>
            </div>

            <!-- STEP 2: SELECT TIME -->
            <div id="step2-time" class="booking-step">
                <h3>2. Select a Time</h3>
                <div class="times" id="timesContainer">
                    <div id="timeSuggestions"></div>
                </div>
            </div>

            <!-- STEP 3: CONFIRMATION & DETAILS -->
            <div id="step3-details" class="booking-step">
                <h3>3. Confirm & Add a Note</h3>
                <form id="bookingOrCheckoutForm" method="POST" action="{{ route('coaching.book') }}">
                    @csrf
                    <input type="hidden" id="booking_time" name="booking_time" value="" />

                    <div>
                        <label for="session_notes" style="font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">What would you like to focus on? (Optional)</label>
                        <textarea id="session_notes" name="notes" placeholder="e.g., Flamenco techniques, strumming patterns, music theory..." class="feedback-textarea"></textarea>
                        <p class="feedback-note">Your notes will be sent to the coach when you book.</p>
                    </div>

                    <div class="summary-box">
                        <h4>Your Selection</h4>
                        <p id="selectionSummaryText">—</p>
                        <p id="ticketInfoText" style="margin-top: 8px;"></p>
                        <div id="priceLine" style="margin-top:10px;font-size:14px;color:rgba(255,255,255,0.9);display: {{ $hasAvailableTicket ? 'none' : 'block' }};">
                            Price: <strong>Rp {{ $coachingPkg ? number_format($coachingPkg->price,0,',','.') : '0' }}</strong>
                        </div>
                    </div>

                    <button id="submitBtn" class="btn primary" type="submit" disabled data-has-ticket="{{ $hasAvailableTicket ? '1' : '0' }}">
                        Select a Date & Time
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection


@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements for step-by-step flow
        const step1El = document.getElementById('step1-date');
        const step2El = document.getElementById('step2-time');
        const step3El = document.getElementById('step3-details');

        // Calendar elements
        const monthNameEl = document.getElementById('monthName');
        const daysEl = document.getElementById('calendarDays');
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');

        // Time and details elements
        const timeSuggestions = document.getElementById('timeSuggestions');
        const selectionSummaryTextEl = document.getElementById('selectionSummaryText');
        const ticketInfoTextEl = document.getElementById('ticketInfoText');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('bookingOrCheckoutForm');

        // Allowed slot times provided by server (admin-configured). If empty, fallback to availability response keys.
        const allowedSlotTimes = {!! json_encode($slotTimes ?? []) !!};

        let current = new Date();
        let selectedDate = null;
        let selectedTime = null;
        let today = new Date();
        let todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());

        // --- UTILITY FUNCTIONS (Your existing logic, mostly unchanged) ---
        function formatDateLocal(d) {
            const Y = d.getFullYear();
            const M = ('0' + (d.getMonth() + 1)).slice(-2);
            const D = ('0' + d.getDate()).slice(-2);
            return `${Y}-${M}-${D}`;
        }

        function formatDateTimeLocal(dt) {
            const Y = dt.getFullYear();
            const M = ('0' + (dt.getMonth() + 1)).slice(-2);
            const D = ('0' + dt.getDate()).slice(-2);
            const h = ('0' + dt.getHours()).slice(-2);
            const m = ('0' + dt.getMinutes()).slice(-2);
            const s = ('0' + dt.getSeconds()).slice(-2);
            return `${Y}-${M}-${D} ${h}:${m}:${s}`;
        }

        async function fetchMonthAvailability(year, month) {
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const start = formatDateLocal(first);
            const end = formatDateLocal(last);
            try {
                const url = '{{ route('coaching.availability.range') }}?start=' + start + '&end=' + end;
                const resp = await fetch(url, { credentials: 'same-origin' });
                if (!resp.ok) return {};
                const json = await resp.json();
                return json.days || {};
            } catch (e) {
                console.error("Failed to fetch month availability:", e);
                return {};
            }
        }

        // --- UI RENDERING & LOGIC ---
        async function renderMonth(dt) {
            const year = dt.getFullYear();
            const month = dt.getMonth();
            const first = new Date(year, month, 1);
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            monthNameEl.textContent = first.toLocaleString('default', { month: 'long', year: 'numeric' });
            daysEl.innerHTML = '';

            const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            weekdays.forEach(w => {
                const h = document.createElement('div');
                h.className = 'weekday';
                h.textContent = w;
                daysEl.appendChild(h);
            });

            const firstDayIndex = first.getDay();
            for (let i = 0; i < firstDayIndex; i++) {
                const ph = document.createElement('div');
                ph.className = 'day-placeholder';
                daysEl.appendChild(ph);
            }

            const availMap = await fetchMonthAvailability(year, month);

            for (let d = 1; d <= daysInMonth; d++) {
                const date = new Date(year, month, d);
                const btn = document.createElement('button');
                btn.className = 'day';
                btn.dataset.date = formatDateLocal(date);
                btn.textContent = d;

                const dateStart = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                if (dateStart < todayStart) {
                    btn.classList.add('disabled');
                    btn.disabled = true;
                } else {
                    const remaining = (availMap[btn.dataset.date] || 0);
                    if (remaining <= 0) {
                        btn.classList.add('disabled');
                        btn.disabled = true;
                        // do not attach click handler for fully booked days
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'date-badge';
                        badge.textContent = remaining > 9 ? '9+' : remaining;
                        btn.appendChild(badge);

                        btn.addEventListener('click', async () => {
                            document.querySelectorAll('.calendar .day.active').forEach(x => x.classList.remove('active'));
                            btn.classList.add('active');
                            selectedDate = btn.dataset.date;
                            selectedTime = null; // Reset time when date changes

                            // Show step 2 and hide step 3
                            step2El.style.display = 'block';
                            step3El.style.display = 'none';
                            step2El.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            await loadTimesForDate(selectedDate);
                            updateSummaryAndButtonState();
                        });
                    }
                }
                daysEl.appendChild(btn);
            }
        }

        async function loadTimesForDate(dateStr) {
            timeSuggestions.innerHTML = '<p style="opacity: 0.7;">Loading available times...</p>';
            try {
                const resp = await fetch('{{ route('coaching.availability') }}?date=' + dateStr, { credentials: 'same-origin' });
                if (!resp.ok) throw new Error('Network response was not ok');
                const json = await resp.json();
                timeSuggestions.innerHTML = ''; // Clear loading message

                // Determine the list of times to render. Prefer admin-configured allowedSlotTimes when provided.
                let times = [];
                if (Array.isArray(allowedSlotTimes) && allowedSlotTimes.length > 0) {
                    times = allowedSlotTimes.slice();
                } else {
                    times = Object.keys(json.slots || {}).sort();
                }

                if (times.length === 0) {
                    timeSuggestions.innerHTML = '<p style="opacity: 0.7;">No available times for this date.</p>';
                    return;
                }

                times.forEach(t => {
                    const s = (json.slots && typeof json.slots[t] !== 'undefined') ? json.slots[t] : { remaining: 0 };
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'time';
                    b.textContent = t.replace(':', '.');
                    b.dataset.time = t;

                    // If server returns remaining and it's zero, mark disabled. If the slot key is missing, treat as disabled.
                    if (typeof s.remaining !== 'undefined' && s.remaining <= 0) {
                        b.classList.add('disabled');
                        b.disabled = true;
                        if (s.mine) {
                            // Jika slot ini milik user sendiri namun penuh, tetap beri label "Your booking"
                            const tag = document.createElement('span');
                            tag.textContent = 'Your booking';
                            tag.style.marginLeft = '8px';
                            tag.style.fontSize = '11px';
                            tag.style.padding = '2px 6px';
                            tag.style.borderRadius = '10px';
                            tag.style.background = 'rgba(100, 200, 255, 0.15)';
                            tag.style.border = '1px solid rgba(100, 200, 255, 0.35)';
                            tag.style.color = '#cfe9ff';
                            b.appendChild(tag);
                            b.classList.add('mine');
                        }
                    } else {
                        if (s.mine) {
                            // Gaya khusus untuk slot milik user
                            b.classList.add('mine');
                            const tag = document.createElement('span');
                            tag.textContent = 'Your booking';
                            tag.style.marginLeft = '8px';
                            tag.style.fontSize = '11px';
                            tag.style.padding = '2px 6px';
                            tag.style.borderRadius = '10px';
                            tag.style.background = 'rgba(100, 200, 255, 0.15)';
                            tag.style.border = '1px solid rgba(100, 200, 255, 0.35)';
                            tag.style.color = '#cfe9ff';
                            b.appendChild(tag);
                        }
                        b.addEventListener('click', () => {
                            selectedTime = b.dataset.time;
                            document.querySelectorAll('#timeSuggestions .time.selected').forEach(x => x.classList.remove('selected'));
                            b.classList.add('selected');

                            // Show step 3
                            step3El.style.display = 'block';
                            step3El.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            updateSummaryAndButtonState();
                        });
                    }
                    timeSuggestions.appendChild(b);
                });
            } catch (e) {
                console.error("Failed to load times:", e);
                timeSuggestions.innerHTML = '<p style="opacity: 0.7; color: #ff8a8a;">Could not load times. Please try again.</p>';
            }
        }

        function updateSummaryAndButtonState() {
            const hasTicket = submitBtn.getAttribute('data-has-ticket') === '1';

            if (!selectedDate) {
                selectionSummaryTextEl.textContent = '—';
                submitBtn.disabled = true;
                submitBtn.textContent = 'Select a Date & Time';
                return;
            }

            const parts = selectedDate.split('-').map(x => parseInt(x, 10));
            const d = new Date(parts[0], parts[1] - 1, parts[2]);
            const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const dayName = d.toLocaleString('id-ID', { weekday: 'long' });
            let summary = `${dayName}, ${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;

            if (selectedTime) {
                summary += ` — ${selectedTime}`;
                document.getElementById('booking_time').value = `${selectedDate} ${selectedTime}:00`;
                submitBtn.disabled = false;
        
                if (hasTicket) {
                    submitBtn.textContent = 'Confirm & Use 1 Ticket';
                    ticketInfoTextEl.textContent = '🎟️ This session will use 1 of your available tickets.';
                } else {
                    submitBtn.textContent = 'Proceed to Payment';
                    ticketInfoTextEl.textContent = '';
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Select a Time';
                ticketInfoTextEl.textContent = '';
            }
            selectionSummaryTextEl.innerHTML = `🗓️ <strong>${summary}</strong>`;
        }

        // Keep calendar pinned to the current month: hide and disable navigation buttons
        try {
            prevBtn.style.display = 'none';
            prevBtn.setAttribute('aria-hidden', 'true');
            prevBtn.disabled = true;
            prevBtn.style.pointerEvents = 'none';
        } catch (e) { }
        try {
            nextBtn.style.display = 'none';
            nextBtn.setAttribute('aria-hidden', 'true');
            nextBtn.disabled = true;
            nextBtn.style.pointerEvents = 'none';
        } catch (e) { }

        // Update month at the next local midnight so calendar stays realtime for the current day/month
        function scheduleMidnightUpdate() {
            const now = new Date();
            const nextMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 2);
            const ms = nextMidnight.getTime() - now.getTime();
            setTimeout(() => {
                today = new Date();
                todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                current = new Date();
                renderMonth(current);
                updateSummaryAndButtonState();
                scheduleMidnightUpdate();
            }, ms);
        }
        scheduleMidnightUpdate();

        // --- EVENT LISTENERS (navigation disabled) ---
        prevBtn.addEventListener('click', () => {
            // navigation intentionally disabled to keep current month pinned
        });

        nextBtn.addEventListener('click', () => {
            // navigation intentionally disabled to keep current month pinned
        });

        form && form.addEventListener('submit', function (e) {
            const bookingInput = document.getElementById('booking_time');
            if (!bookingInput || !bookingInput.value) {
                e.preventDefault();
                // We can show a more elegant message than an alert
                alert('Please select a date and time before booking.');
                return;
            }
            const hasTicket = submitBtn.getAttribute('data-has-ticket') === '1';
            if (!hasTicket) {
                e.preventDefault();
                const schedule = encodeURIComponent(bookingInput.value);
                // Also pass along the notes
                const notes = encodeURIComponent(document.getElementById('session_notes').value);
                window.location.href = `{{ route('coaching.checkout') }}?schedule=${schedule}&notes=${notes}`;
            }
            // If user has a ticket, allow the form to submit normally to coaching.book route
        });

        // --- INITIALIZATION ---
        renderMonth(current);
        updateSummaryAndButtonState();
    });
    </script>
@endpush
