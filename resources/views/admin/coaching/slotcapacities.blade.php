@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Coaching Slot Capacities — {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}</h3>
                <small class="text-muted">Showing current month</small>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <p class="text-muted">Select dates in the calendar, then click "Edit hours" to pick hourly slots (1-hour increments). Each slot will be for 1 person (capacity = 1).</p>



            <style>
                /* black & gray modern calendar */
                #calendar table { width:100%; border-collapse: collapse; border-radius:12px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,0.35); background:#0b0b0b; }
                #calendar thead th { background:#0b0b0b; color:#e6e7e8; padding:12px 8px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.03); }
                #calendar tbody td { background:#0d0d0e; color:#d6d8da; vertical-align: top; padding:14px; border-right:1px solid rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.02); }
                #calendar tbody tr:last-child td { border-bottom: none; }
                #calendar tbody td .day-number { font-weight:600; display:block; margin-bottom:8px; color:#cfd3d6; }
                /* subtle dark hover for active dates */
                #calendar tbody td:not(.inactive):hover { background: #1a1a1b; cursor:pointer; }
                /* inactive/out-of-month cells */
                #calendar td.inactive { background:transparent; color:#6c757d; cursor:default; }
                /* past dates are visually muted */
                #calendar td.past { opacity:0.55; }
                /* selected highlight (dark gray) */
                #calendar td.selected { background:#343a40 !important; color:#fff !important; }
                /* badges */
                .slot-badge, #calendar .badge { background:#6c757d; color:#fff; border-radius:6px; padding:4px 6px; font-size:12px; }
                .badge.bg-success { background:#495057 !important; }
                @media (max-width: 768px){ #calendar tbody td { padding:10px; font-size:14px } }
            </style>

            <div class="row">
                <div class="col-lg-8">
                    <p class="text-muted">Click a date to pick hours, then click <strong>Add</strong> to queue the schedule in the sidebar.</p>
                    <div id="calendar" class="mb-3"></div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pending schedules</h5>
                            <p class="text-muted">Schedules you added (not yet saved)</p>
                            <div id="pendingList" style="min-height:120px"></div>
                            <hr>
                            <h6 class="mb-2">Existing schedules (this month)</h6>
                            <div id="existingList" style="min-height:80px">
                                {{-- will be rendered by JS for compact/more view --}}
                            </div>
                            <form id="saveForm" method="POST" action="{{ url('/admin/coaching/slot-capacities') }}">
                                @csrf
                                <input type="hidden" name="slots_json" id="slots_json">
                                <div class="d-flex gap-2 mt-3">
                                    <button id="saveAllBtn" class="btn btn-success btn-sm" type="submit" disabled>Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- modal for editing hours -->
            <div class="modal fade" id="hoursModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pick hours for <span id="modalDateLabel"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Choose hours (1-hour steps) for the selected date.</p>
                            <div class="mb-2"><small class="text-muted">Click hour to toggle. Click <strong>Add</strong> to queue this date + hours to the sidebar.</small></div>
                            <div id="hoursGrid" class="d-flex flex-wrap gap-2"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button id="addHoursBtn" type="button" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@section('scripts')
<script>
    // build calendar grid for the given month/year
    const year = {{ $year }};
    const month = {{ $month }}; // 1..12
    const existing = @json($slots);
    const booked = @json($booked ?? []);

    function buildCalendar(y,m){
        function formatDateLocal(d){
            const Y = d.getFullYear();
            const M = ('0'+(d.getMonth()+1)).slice(-2);
            const D = ('0'+d.getDate()).slice(-2);
            return `${Y}-${M}-${D}`;
        }
        const first = new Date(y, m-1, 1);
        const last = new Date(y, m, 0);
        const startWeekDay = first.getDay();
        const weeks = [];
        let day = 1 - startWeekDay;
        while(day <= last.getDate()){
            const week = [];
            for(let i=0;i<7;i++){
                const d = new Date(y, m-1, day);
                const inMonth = d.getMonth() === (m-1);
                week.push({ day: d.getDate(), date: new Date(d), inMonth });
                day++;
            }
            weeks.push(week);
        }

        const container = document.getElementById('calendar');
        container.innerHTML = '';

        const table = document.createElement('table');
        table.className = 'table table-dark';
        table.style.background = 'transparent';
        const thead = document.createElement('thead');
        thead.innerHTML = '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';
        table.appendChild(thead);
        const tb = document.createElement('tbody');

        weeks.forEach(row => {
            const tr = document.createElement('tr');
        row.forEach(cell => {
                const td = document.createElement('td');
                td.style.width = '120px';
                td.style.height = '80px';
                td.className = 'align-top';
                if(!cell.inMonth){
                    td.classList.add('inactive');
                    td.innerHTML = `<div class="small">${cell.day}</div>`;
                } else {
                    const iso = formatDateLocal(cell.date);
                    const has = existing[iso] && existing[iso].length>0;
                    const hasBookings = booked[iso] && booked[iso].length>0;
                    const now = new Date();
                    const isPast = cell.date < new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    // show green badge for defined slots, and small red dot if there are actual bookings
                    td.innerHTML = `<div class="d-flex justify-content-between align-items-center"><div><strong>${cell.day}</strong></div><div>${has?'<span class="badge bg-success">'+existing[iso].length+'</span>':''}${hasBookings?'<span title="Has bookings" style="display:inline-block;width:10px;height:10px;background:#dc3545;border-radius:50%;margin-left:8px"></span>':''}</div></div>`;
                    td.dataset.date = iso;
                    if (isPast) {
                        td.classList.add('inactive','past');
                    } else {
                        td.style.cursor = 'pointer';
                        td.addEventListener('click', ()=> openModalForDate(iso));
                    }
                }
                tr.appendChild(td);
            });
            tb.appendChild(tr);
        });
        table.appendChild(tb);
        container.appendChild(table);
    }

    // calendar uses per-date modal flow; clicking a date opens the hours modal

    // build hours grid
    const hoursGrid = document.getElementById('hoursGrid');
    for(let h=0;h<24;h++){
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-secondary btn-sm';
        btn.style.width = '72px';
        const label = ('0'+h).slice(-2)+':00';
        btn.textContent = label;
        btn.dataset.hour = label;
        btn.addEventListener('click', ()=>{ if(!btn.disabled) { btn.classList.toggle('btn-success'); btn.classList.toggle('btn-outline-secondary'); } });
        hoursGrid.appendChild(btn);
    }
    // when a date cell is clicked, open the modal for that single date
    function openModalForDate(iso){
        // reset hour buttons
        document.querySelectorAll('#hoursGrid button').forEach(b=>{ b.classList.remove('btn-success'); b.classList.add('btn-outline-secondary'); });
        document.getElementById('modalDateLabel').textContent = iso;
        // disable or mark hours that already have bookings
        document.querySelectorAll('#hoursGrid button').forEach(b=>{ b.disabled = false; b.title = ''; b.classList.remove('btn-danger'); });
        if(booked[iso]){
            booked[iso].forEach(h => {
                const b = Array.from(document.querySelectorAll('#hoursGrid button')).find(x=>x.dataset.hour===h);
                if(b){ b.disabled = true; b.classList.remove('btn-outline-secondary'); b.classList.add('btn-danger'); b.title = 'Already booked'; }
            });
        }
        // if existing capacity hours for this date, pre-select them (but don't select booked/disabled ones)
        if(existing[iso]){
            existing[iso].forEach(h => {
                const b = Array.from(document.querySelectorAll('#hoursGrid button')).find(x=>x.dataset.hour===h);
                if(b && !b.disabled){ b.classList.remove('btn-outline-secondary'); b.classList.add('btn-success'); }
            });
        }
        const modal = new bootstrap.Modal(document.getElementById('hoursModal'));
        modal.show();
        // store current modal date
        document.getElementById('hoursModal').dataset.currentDate = iso;
    }

    document.getElementById('addHoursBtn').addEventListener('click', function(){
        const iso = document.getElementById('hoursModal').dataset.currentDate;
        const hours = Array.from(document.querySelectorAll('#hoursGrid button.btn-success')).map(b=>b.dataset.hour);
        if(!iso || hours.length===0){ alert('Please pick at least one hour for the date'); return; }
        // add to pendingEntries
        pendingEntries[iso] = hours;
        renderPending();
        // enable Save All
        document.getElementById('saveAllBtn').disabled = false;
        // hide modal
        bootstrap.Modal.getInstance(document.getElementById('hoursModal')).hide();
    });

    // Clear button removed — clearing pending entries can be done by removing individual items.

    // Save All via AJAX with confirmation when overlaps exist
    document.getElementById('saveForm').addEventListener('submit', function(e){
        e.preventDefault();
        if(Object.keys(pendingEntries).length===0){ alert('No pending schedules to save'); return; }

        const pendingDates = Object.keys(pendingEntries);
        const existingDates = Object.keys(existing || {});
        const overlaps = pendingDates.filter(d => existingDates.includes(d));

    // Always replace existing slots for selected dates (no merge option)
    doAjaxSave(true);
    });

    function doAjaxSave(replace){
        const payload = JSON.stringify(pendingEntries);
        fetch('{{ url('/admin/coaching/slot-capacities') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ slots_json: pendingEntries, replace: replace })
        }).then(r => r.json()).then(data => {
            if(data && data.success){
                // update existing map and calendar badges
                Object.keys(data.updated || {}).forEach(d => { existing[d] = data.updated[d]; });
                // remove saved from pending
                Object.keys(pendingEntries).forEach(d => { delete pendingEntries[d]; });
                renderPending();
                // rebuild calendar to update badges
                buildCalendar(year, month);
                // re-render compact existing schedules
                renderExisting();
                // disable save button
                document.getElementById('saveAllBtn').disabled = true;
                alert('Schedules saved');
            } else {
                alert('Save failed');
            }
        }).catch(err => { console.error(err); alert('Network error saving schedules'); });
    }

    // pending entries map date->hours
    let pendingEntries = {};

    function renderPending(){
        const list = document.getElementById('pendingList');
        list.innerHTML = '';
        const keys = Object.keys(pendingEntries).sort();
        if(keys.length===0){ list.innerHTML = '<div class="text-muted">No pending schedules.</div>'; return; }
        keys.forEach(d=>{
            const div = document.createElement('div');
            div.className = 'mb-2';
            const hrs = pendingEntries[d].map(h=>`<span class="badge bg-info text-white me-1">${h}</span>`).join('');
            div.innerHTML = `<div class="d-flex justify-content-between align-items-start"><div><strong>${d}</strong><div class="mt-1">${hrs}</div></div><div><button class="btn btn-sm btn-outline-danger">Remove</button></div></div>`;
            div.querySelector('button').addEventListener('click', ()=>{ delete pendingEntries[d]; renderPending(); if(Object.keys(pendingEntries).length===0) document.getElementById('saveAllBtn').disabled = true; });
            list.appendChild(div);
        });
        // ensure hidden input is set for non-AJAX fallback form submit
        try {
            const hidden = document.getElementById('slots_json');
            if (hidden) hidden.value = JSON.stringify(pendingEntries);
        } catch(e){}
    }

    // initial build
    buildCalendar(year, month);
    // render existing schedules compactly (with delete per-date and +N more)
    function renderExisting(){
        const container = document.getElementById('existingList');
        container.innerHTML = '';
        const keys = Object.keys(existing).sort();
        if(keys.length===0){ container.innerHTML = '<div class="text-muted">No saved schedules for this month.</div>'; return; }
        keys.forEach(d=>{
            const div = document.createElement('div');
            div.className = 'mb-2';
            const hrs = (existing[d] || []).slice(0,4).map(h=>`<span class="badge bg-secondary me-1 small">${h}</span>`).join('');
            const moreCount = Math.max(0, (existing[d]||[]).length - 4);
            const more = moreCount>0 ? `<a href="#" class="ms-1 existing-more" data-date="${d}">+${moreCount} more</a>`: '';
            // delete button for this date
            const delBtn = `<button class="btn btn-sm btn-outline-danger ms-2 delete-date" data-date="${d}">Delete</button>`;
            div.innerHTML = `<div class="d-flex justify-content-between align-items-start"><div><strong class="me-2">${d}</strong><div class="mt-1 d-inline-block">${hrs}${more}</div></div><div>${delBtn}</div></div>`;
            container.appendChild(div);
        });

        // attach click handlers for +N more links to expand into full list inline
        Array.from(container.querySelectorAll('.existing-more')).forEach(a=>{
            a.addEventListener('click', function(e){
                e.preventDefault();
                const date = this.dataset.date;
                // render full hours with per-hour delete buttons
                const fullHtml = (existing[date]||[]).map(h=>{
                    return `<span class="badge bg-secondary me-1 small">${h} <button class=\"btn btn-xs btn-link text-danger p-0 ms-1 remove-hour\" data-date=\"${date}\" data-time=\"${h}\">×</button></span>`;
                }).join(' ');
                this.parentElement.innerHTML = fullHtml;

                // attach handlers for per-hour remove buttons
                Array.from(this.parentElement.querySelectorAll('.remove-hour')).forEach(btn=>{
                    btn.addEventListener('click', function(ev){
                        ev.preventDefault();
                        const dt = this.dataset.date;
                        const tm = this.dataset.time;
                        if(!confirm('Delete ' + tm + ' on ' + dt + '?')) return;
                        fetch('{{ url('/admin/coaching/slot-capacities/delete') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ date: dt, time: tm })
                        }).then(r=>r.json()).then(data=>{
                            if(data && data.success){
                                // update existing map with remaining times
                                existing[dt] = data.remaining || [];
                                if(existing[dt].length===0) delete existing[dt];
                                renderExisting();
                                buildCalendar(year, month);
                                alert('Deleted ' + tm + ' on ' + dt);
                            } else {
                                alert('Delete failed');
                            }
                        }).catch(err=>{ console.error(err); alert('Network error deleting slot'); });
                    });
                });
            });
        });

        // attach click handlers for delete buttons to remove all slots for that date
        Array.from(container.querySelectorAll('.delete-date')).forEach(b=>{
            b.addEventListener('click', function(e){
                const date = this.dataset.date;
                if(!confirm('Delete all schedules for ' + date + '?')) return;
                fetch('{{ url('/admin/coaching/slot-capacities/delete') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ date: date })
                }).then(r=>r.json()).then(data=>{
                    if(data && data.success){
                        // remove that date from existing map and refresh UI
                        delete existing[date];
                        renderExisting();
                        buildCalendar(year, month);
                        alert('Deleted ' + date);
                    } else {
                        alert('Delete failed');
                    }
                }).catch(err=>{ console.error(err); alert('Network error deleting slots'); });
            });
        });
    }
    renderExisting();

</script>
@endsection

@endsection
