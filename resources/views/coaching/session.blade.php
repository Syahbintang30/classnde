@extends('layouts.app')

@section('content')
<div class="coaching-session-root">
    <header class="cs-topbar">
        <div class="cs-brand">{{ $booking->user ? $booking->user->name : 'Coaching' }} · Session {{ $booking->session_number ?? $booking->id }}</div>
    <div class="cs-recording cs-recording-top">● Live</div>
    </header>

    <!-- centered meta placed above the video area -->
    <div class="cs-meta cs-meta-center">
        <div class="cs-time">{{ \Carbon\Carbon::parse($booking->booking_time)->format('d M Y — H:i') }}</div>
        <div class="cs-status">Status: <span class="status-pill">{{ $booking->status }}</span></div>
        @if(isset($isAdmin) && $isAdmin && !empty($booking->notes))
            <div class="cs-notes">Notes: <strong>{{ Str::limit($booking->notes, 160) }}</strong></div>
        @endif
    </div>

    <main class="cs-stage" id="video-root">
        <section class="cs-video-area two-col" aria-label="video area">
            <div class="cs-video-pair">
                <div id="local-media" class="cs-local cs-video-tile ar-19x6" aria-label="local video"></div>
                <div id="remote-media" class="cs-remote-grid ar-19x6">
                    <!-- remote participant tiles appended here -->
                </div>
            </div>
        </section>

        <!-- controls moved below the video area (static, not overlay) -->
        <div class="cs-overlay-bar" role="group" aria-label="session controls">
            <div class="cs-overlay-left"><span id="cs-statusline">--:-- | 0 people in the call</span></div>
            <div class="cs-overlay-center" id="controls-center">
                <button id="ctl-mic" class="ctl-btn" title="Toggle mic">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 0 0-6 0v5a3 3 0 0 0 3 3z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 11v1a7 7 0 0 1-14 0v-1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <button id="ctl-camera" class="ctl-btn" title="Toggle camera">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="6" width="18" height="12" rx="2" stroke="currentColor" stroke-width="1.6"></rect><circle cx="12" cy="12" r="2.2" stroke="currentColor" stroke-width="1.6"></circle></svg>
                </button>
                <!-- layout toggle removed -->
                <button id="hangup" class="ctl-btn ctl-hangup" title="End call" aria-label="End call">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M21 15v2a2 2 0 0 1-2 2c-6.627 0-12-5.373-12-12a2 2 0 0 1 2-2h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    <span class="hangup-label">End</span>
                </button>
            </div>
            <!-- right-side quick actions removed as requested -->
        </div>
        </section>

    <!-- sidebar removed as requested -->
    </main>

    <nav class="cs-bottombar" role="toolbar" aria-label="session controls">
        <div class="cs-ambient"></div>
        <div class="cs-ctx">
            <!-- controls live badge moved to header; help and admin removed -->
        </div>
    </nav>

</div>

<!-- admin end-room script removed as requested -->
<!-- Confirmation modal for ending the call -->
<div id="cs-modal-backdrop" class="cs-modal-backdrop" aria-hidden="true">
    <div class="cs-modal" role="dialog" aria-modal="true" aria-labelledby="cs-modal-title">
        <h3 id="cs-modal-title">End Session</h3>
        <p class="cs-modal-body">Are you sure you want to end this session? This will disconnect all participants.</p>
        <div class="cs-modal-actions">
            <button id="cs-modal-cancel" class="btn">Cancel</button>
            <button id="cs-modal-confirm" class="btn btn-danger">End Session</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/coaching.css') }}">
@endpush

@push('scripts')
@if (file_exists(public_path('js/twilio-video.min.js')))
    <script src="{{ asset('js/twilio-video.min.js') }}"></script>
@endif
<script>
// Load Twilio SDK from CDN with fallback and wait for window.Twilio
(function(){
    var primary = 'https://media.twiliocdn.com/sdk/js/video/latest/twilio-video.min.js';
    var fallback = 'https://unpkg.com/twilio-video/dist/twilio-video.min.js';
    function insertScript(src){ var s = document.createElement('script'); s.src = src; s.async = false; document.head.appendChild(s); return s; }
    if (!window.Twilio) insertScript(primary);
    setTimeout(function(){ if (!window.Twilio) insertScript(fallback); }, 1200);
})();

console.log('[coaching.session] scripts loaded');

function waitForTwilio(timeoutMs = 6000){
    return new Promise(function(resolve, reject){
        if (window.Twilio) return resolve(window.Twilio);
        var waited = 0;
        var iv = setInterval(function(){
            if (window.Twilio) { clearInterval(iv); return resolve(window.Twilio); }
            waited += 200;
            if (waited >= timeoutMs) { clearInterval(iv); return reject(new Error('Twilio SDK not available after ' + timeoutMs + 'ms')); }
        }, 200);
    });
}

waitForTwilio().then(function(){
    document.addEventListener('DOMContentLoaded', async function(){
        const bookingId = {{ $booking->id }};
        let token = null;
        let roomName = null;

        // Prefer server-provided token
        @if(isset($accessToken) && isset($roomName))
            token = {!! json_encode($accessToken) !!};
            roomName = {!! json_encode($roomName) !!};
            console && console.log && console.log('Using server-supplied token/roomName');
        @else
            // Fallback: fetch token endpoint
            const tokenResp = await fetch("{{ url('/coaching/token') }}/" + bookingId, { credentials: 'same-origin' });
            if (!tokenResp.ok) {
                document.getElementById('video-root').innerText = 'Failed to get token: ' + tokenResp.statusText;
                return;
            }
            const data = await tokenResp.json();
            token = data.token;
            roomName = data.room;
        @endif

        const { connect, createLocalVideoTrack, createLocalAudioTrack } = Twilio.Video;
        // Keep debug output in the browser console only (do not write to DOM)
        function debug(msg){
            try { console.log(msg); } catch(e) { /* ignore */ }
        }

        let localVideoTrack = null;
        let localAudioTrack = null;
    // map to keep active volume monitors keyed by mediaStreamTrack.id
    const volumeMonitors = new Map();
    // single audio context for monitors (create lazily on first user gesture)
    let audioCtx = null;
        try {
            debug('Requesting local media...');
            localVideoTrack = await createLocalVideoTrack();
            localAudioTrack = await createLocalAudioTrack();
            debug('Local media acquired');
        } catch (e) {
            debug('Failed to acquire local media: ' + (e && e.message ? e.message : e));
        }

        if (localVideoTrack) {
            const localMedia = document.getElementById('local-media');
            localMedia.appendChild(localVideoTrack.attach());
        }

        // initialize control UI according to available local tracks
        try {
            if (localAudioTrack) {
                btnMic && btnMic.classList.remove('muted');
                updateLocalMicIndicator(false);
            } else {
                btnMic && btnMic.classList.add('muted');
                updateLocalMicIndicator(true);
            }
            if (localVideoTrack) {
                btnCamera && btnCamera.classList.remove('muted');
                ctlCam && ctlCam.classList.remove('muted');
            } else {
                btnCamera && btnCamera.classList.add('muted');
                ctlCam && ctlCam.classList.add('muted');
            }
        } catch (e) { /* ignore */ }

    // attach simple mic/camera toggles
    // prefer explicit control elements if present; fall back to legacy ids
    const ctlMic = document.getElementById('ctl-mic');
    const ctlCam = document.getElementById('ctl-camera');
    const btnMic = document.getElementById('btn-mic') || ctlMic;
    const btnCamera = document.getElementById('btn-camera') || ctlCam;
    // layout toggle removed

        // Publications (if room connected)
        let localVideoPublication = null;
        let localAudioPublication = null;

        async function enableCamera() {
            try {
                if (!localVideoTrack) {
                    localVideoTrack = await createLocalVideoTrack();
                    const localMedia = document.getElementById('local-media');
                    localMedia.innerHTML = '';
                    localMedia.appendChild(localVideoTrack.attach());
                }
                // if room is active, publish track
                if (room && room.localParticipant) {
                    if (!localVideoPublication) {
                        try {
                            localVideoPublication = await room.localParticipant.publishTrack(localVideoTrack);
                            debug('Published local video');
                        } catch (e) {
                            debug('Publish video failed: ' + e.message);
                        }
                    }
                }
                btnCamera.classList.remove('muted');
                ctlCam && ctlCam.classList.remove('muted');
            } catch (err) {
                debug('Failed to enable camera: ' + (err && err.message));
            }
        }

        function disableCamera() {
            try {
                // if published, unpublish
                if (room && room.localParticipant && localVideoPublication) {
                    try { room.localParticipant.unpublishTrack(localVideoPublication.track || localVideoTrack); } catch (e) { /* ignore */ }
                    localVideoPublication = null;
                }
                if (localVideoTrack) {
                    try { localVideoTrack.stop(); } catch (e) { }
                    localVideoTrack = null;
                }
                const localMedia = document.getElementById('local-media');
                if (localMedia) localMedia.innerHTML = '<div class="placeholder">Camera off</div>';
                btnCamera.classList.add('muted');
                ctlCam && ctlCam.classList.add('muted');
            } catch (err) { debug('Failed to disable camera: ' + (err && err.message)); }
        }

        async function enableMic() {
            try {
                if (!localAudioTrack) {
                    localAudioTrack = await createLocalAudioTrack();
                }
                if (room && room.localParticipant) {
                    if (!localAudioPublication) {
                        try {
                            localAudioPublication = await room.localParticipant.publishTrack(localAudioTrack);
                            debug('Published local audio');
                            // start monitoring local audio activity
                            startVolumeMonitorForTrack(localAudioTrack, document.getElementById('local-media'));
                        } catch (e) { debug('Publish audio failed: ' + e.message); }
                    }
                }
                btnMic && btnMic.classList.remove('muted');
                ctlMic && ctlMic.classList.remove('muted');
                updateLocalMicIndicator(false);
            } catch (err) { debug('Failed to enable mic: ' + (err && err.message)); }
        }

        function disableMic() {
            try {
                if (room && room.localParticipant && localAudioPublication) {
                    try { room.localParticipant.unpublishTrack(localAudioPublication.track || localAudioTrack); } catch (e) { }
                    localAudioPublication = null;
                    // stop monitoring local audio
                    stopVolumeMonitorForTrack(localAudioTrack);
                }
                if (localAudioTrack) {
                    try { localAudioTrack.stop(); } catch (e) { }
                    localAudioTrack = null;
                }
                btnMic && btnMic.classList.add('muted');
                ctlMic && ctlMic.classList.add('muted');
                updateLocalMicIndicator(true);
            } catch (err) { debug('Failed to disable mic: ' + (err && err.message)); }
        }

        btnCamera && btnCamera.addEventListener('click', function(){
            if (btnCamera.classList.contains('muted')) {
                enableCamera();
            } else {
                disableCamera();
            }
        });

        btnMic && btnMic.addEventListener('click', function(){
            if (btnMic.classList.contains('muted')) {
                enableMic();
            } else {
                disableMic();
            }
        });

    // mirror to overlay center controls (if present)
    // only mirror clicks if the central and fallback controls are different elements
    if (ctlCam && btnCamera && ctlCam !== btnCamera) ctlCam.addEventListener('click', () => btnCamera.click());
    if (ctlMic && btnMic && ctlMic !== btnMic) ctlMic.addEventListener('click', () => btnMic.click());
    
    // local mic indicator: small dot in the local-media corner
    function updateLocalMicIndicator(isMuted) {
        try {
            const local = document.getElementById('local-media');
            if (!local) return;
            let ind = local.querySelector('.local-mic-indicator');
            if (!ind) {
                ind = document.createElement('div');
                ind.className = 'local-mic-indicator';
                ind.setAttribute('aria-hidden','true');
                local.appendChild(ind);
            }
            if (isMuted) {
                ind.classList.add('muted');
            } else {
                ind.classList.remove('muted');
            }
        } catch (e) { /**/ }
    }
    // layout toggle handler removed because control is no longer present


            let room = null;
        try {
            if (!token) throw new Error('Missing token');
            debug('Connecting to room ' + roomName + ' with token ' + (token ? token.slice(0,8) + '...' : 'null'));
            const connectOpts = { name: roomName };
            const tracks = [];
            if (localAudioTrack) tracks.push(localAudioTrack);
            if (localVideoTrack) tracks.push(localVideoTrack);
            if (tracks.length) connectOpts.tracks = tracks;
            room = await connect(token, connectOpts);
            debug('Connected to room ' + roomName + ' (sid=' + (room && room.sid ? room.sid : 'unknown') + ')');

            const remoteContainer = document.getElementById('remote-media');

            function addParticipantToList(participant, muted=false) {
                const ul = document.getElementById('participant-list');
                if (!ul) return; // participant list was removed from DOM
                const li = document.createElement('li');
                li.id = 'p-' + participant.sid;
                const dot = document.createElement('span'); dot.className = 'dot';
                const pname = document.createElement('span'); pname.className = 'pname'; pname.textContent = participant.identity || participant.sid;
                const aind = document.createElement('span'); aind.className = 'pmuted audio-indicator'; aind.title = 'Audio muted'; aind.style.display = 'none'; aind.textContent = '🔇';
                const vind = document.createElement('span'); vind.className = 'pmuted video-indicator'; vind.title = 'Video off'; vind.style.display = 'none'; vind.textContent = '📷✖';
                li.appendChild(dot); li.appendChild(pname); li.appendChild(aind); li.appendChild(vind);
                ul.appendChild(li);
            }

            function removeParticipantFromList(participant) {
                const el = document.getElementById('p-' + participant.sid);
                if (el) el.remove();
            }

            function attachParticipant(participant) {
                // hide empty placeholder if present
                const empty = document.querySelector('.cs-empty'); if (empty) empty.style.display = 'none';

                addParticipantToList(participant);
                const tile = document.createElement('div');
                tile.className = 'cs-video-tile';
                tile.id = participant.sid;
                const nameTag = document.createElement('div');
                nameTag.className = 'tile-name';
                nameTag.textContent = participant.identity || participant.sid;
                tile.appendChild(nameTag);

                participant.tracks.forEach(publication => {
                    if (publication.track) tile.appendChild(publication.track.attach());
                });
                participant.on('trackSubscribed', track => {
                    tile.appendChild(track.attach());
                    setParticipantIndicators(participant, true, track.kind === 'video' ? true : false);
                    // if it's an audio track, start monitoring to show speaking indicator
                    if (track.kind === 'audio') {
                        startVolumeMonitorForTrack(track, tile);
                    }
                });
                participant.on('trackUnsubscribed', track => {
                    track.detach().forEach(el => el.remove());
                    setParticipantIndicators(participant, false, track.kind === 'video' ? false : false);
                    // stop audio monitor when unsubscribed
                    if (track.kind === 'audio') stopVolumeMonitorForTrack(track);
                });
                remoteContainer.appendChild(tile);
            }

            room.participants.forEach(attachParticipant);
            room.on('participantConnected', attachParticipant);
            room.on('participantDisconnected', participant => {
                const el = document.getElementById(participant.sid);
                if (el) el.remove();
                removeParticipantFromList(participant);
                // if no remaining remote tiles, show placeholder
                const remote = document.getElementById('remote-media');
                if (remote && remote.querySelectorAll('.cs-video-tile').length === 0) {
                    const empty = document.querySelector('.cs-empty'); if (empty) empty.style.display = '';
                }
                // update grid classes
                updateGridClass();
            });

            // update status line with time and participant count
            const statusLine = document.getElementById('cs-statusline');
            function refreshStatus() {
                try {
                    const now = new Date();
                    const hh = ('0'+now.getHours()).slice(-2);
                    const mm = ('0'+now.getMinutes()).slice(-2);
                    const count = 1 + (room ? room.participants.size : 0); // include local
                    if (statusLine) statusLine.textContent = `${hh}:${mm} | ${count} people in the call`;
                } catch (e) { }
            }
            refreshStatus();
            // refresh every 20s and on participant changes
            setInterval(refreshStatus, 20000);
            room.on('participantConnected', refreshStatus);
            room.on('participantDisconnected', refreshStatus);

            // show friendly placeholder when no remote participants
            if (remoteContainer && remoteContainer.querySelectorAll('.cs-video-tile').length === 0) {
                const ph = document.createElement('div');
                ph.className = 'cs-empty';
                ph.textContent = 'Waiting for others to join the session';
                remoteContainer.appendChild(ph);
            }

            // update grid layout class based on participant count
            function updateGridClass(){
                const grid = document.getElementById('remote-media');
                if (!grid) return;
                const tiles = grid.querySelectorAll('.cs-video-tile');
                grid.classList.remove('two');
                if (tiles.length === 2) {
                    grid.classList.add('two');
                }
            }
            // call once after initial attach
            updateGridClass();

            // helper to show/hide audio/video indicators for a participant
            function setParticipantIndicators(participant, audioPresent, videoPresent) {
                try {
                    const li = document.getElementById('p-' + participant.sid);
                    if (li) {
                        const a = li.querySelector('.audio-indicator');
                        const v = li.querySelector('.video-indicator');
                        if (a) a.style.display = audioPresent ? 'none' : '';
                        if (v) v.style.display = videoPresent ? 'none' : '';
                    }
                    const tile = document.getElementById(participant.sid);
                    if (tile) {
                        let badge = tile.querySelector('.tile-muted');
                        if (!badge) {
                            badge = document.createElement('div'); badge.className = 'tile-muted'; tile.appendChild(badge);
                        }
                        const parts = [];
                        if (!audioPresent) parts.push('🔇');
                        if (!videoPresent) parts.push('📷');
                        badge.textContent = parts.join(' ');
                        badge.style.display = parts.length ? '' : 'none';
                    }
                } catch (e) { /* ignore */ }
            }

            // volume monitor helpers using WebAudio Analyser
            function ensureAudioContext(){
                if (audioCtx) return audioCtx;
                try {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                } catch (e) { audioCtx = null; }
                return audioCtx;
            }

            function startVolumeMonitorForTrack(track, element){
                try {
                    if (!track || !track.mediaStreamTrack) return;
                    const id = track.mediaStreamTrack.id || (track.trackSid || track.sid || String(Math.random()));
                    if (volumeMonitors.has(id)) return; // already monitoring
                    const ctx = ensureAudioContext();
                    if (!ctx) return;
                    const ms = new MediaStream([track.mediaStreamTrack]);
                    const src = ctx.createMediaStreamSource(ms);
                    const analyser = ctx.createAnalyser(); analyser.fftSize = 1024; analyser.smoothingTimeConstant = 0.3;
                    src.connect(analyser);
                    const data = new Uint8Array(analyser.fftSize);
                    let raf = null;
                    function tick(){
                        try {
                            analyser.getByteTimeDomainData(data);
                            let sum = 0;
                            for (let i=0;i<data.length;i++){ const v = (data[i]-128)/128; sum += v*v; }
                            const rms = Math.sqrt(sum / data.length);
                            const isSpeaking = rms > 0.02; // threshold -- tweak if needed
                            if (element) {
                                // for local-media element, toggle .local-mic-indicator.speaking
                                if (element.id === 'local-media'){
                                    const micInd = element.querySelector('.local-mic-indicator');
                                    if (micInd) micInd.classList.toggle('speaking', !!isSpeaking);
                                } else {
                                    // element is a tile — toggle speaking class to show green outline
                                    element.classList.toggle('speaking', !!isSpeaking);
                                }
                            }
                        } catch (e) { /* ignore */ }
                        raf = requestAnimationFrame(tick);
                    }
                    raf = requestAnimationFrame(tick);
                    volumeMonitors.set(id, { analyser, src, raf, ctx });
                } catch (e) { debug('startVolumeMonitor error: ' + (e && e.message)); }
            }

            function stopVolumeMonitorForTrack(track){
                try {
                    if (!track || !track.mediaStreamTrack) return;
                    const id = track.mediaStreamTrack.id || (track.trackSid || track.sid);
                    const item = volumeMonitors.get(id);
                    if (!item) return;
                    if (item.raf) cancelAnimationFrame(item.raf);
                    try { if (item.analyser && item.src) { item.src.disconnect(); item.analyser.disconnect(); } } catch(e){}
                    volumeMonitors.delete(id);
                } catch (e) { /**/ }
            }

            // Listen to remote participant publications to initialize indicators
            room.participants.forEach(p => {
                // determine presence of audio/video tracks
                const hasAudio = Array.from(p.tracks.values()).some(pub => pub.track && pub.track.kind === 'audio');
                const hasVideo = Array.from(p.tracks.values()).some(pub => pub.track && pub.track.kind === 'video');
                setParticipantIndicators(p, hasAudio, hasVideo);
            });

            // update indicators for local participant when publications change
            if (room.localParticipant) {
                room.localParticipant.on('trackPublished', (pub) => {
                    // local audio/video published -> hide local indicators
                    const localLi = document.getElementById('p-' + room.localParticipant.sid);
                    // we don't render local in list by default, but update local tile
                    const localTile = document.getElementById(room.localParticipant.sid) || document.getElementById('local-media');
                    if (localTile) {
                        const badge = localTile.querySelector('.tile-muted');
                        if (badge) badge.style.display = 'none';
                    }
                });
                room.localParticipant.on('trackUnpublished', (pub) => {
                    const localTile = document.getElementById(room.localParticipant.sid) || document.getElementById('local-media');
                    if (localTile) {
                        let badge = localTile.querySelector('.tile-muted');
                        if (!badge) { badge = document.createElement('div'); badge.className = 'tile-muted'; localTile.appendChild(badge); }
                        badge.textContent = '🔇'; badge.style.display = '';
                    }
                });
            }

        } catch (err) {
            const msg = (err && err.message) ? err.message : String(err);
            debug('Failed to connect: ' + msg);
            try {
                // Log connect error to server for diagnostics
                const errMeta = { message: msg };
                try { if (err && typeof err === 'object' && 'code' in err) errMeta.code = err.code; } catch(_){}
                await fetch("{{ url('/coaching') }}/{{ $booking->id }}/event", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ event: 'connect_error', meta: errMeta })
                });
            } catch (e) { /* ignore logging failure */ }

            // One-time retry: fetch a fresh token then retry connect
            try {
                if (!window.__twilioConnectRetried) {
                    window.__twilioConnectRetried = true;
                    const tokenResp = await fetch("{{ url('/coaching/token') }}/" + bookingId, { credentials: 'same-origin' });
                    if (tokenResp.ok) {
                        const data = await tokenResp.json();
                        if (data && data.token && data.room) {
                            token = data.token; roomName = data.room;
                            debug('Retrying connect with fresh token for room ' + roomName);
                            // Retry without publishing local tracks to avoid media permission issues
                            const retryOpts = { name: roomName };
                            room = await connect(token, retryOpts);
                            debug('Connected on retry');
                        }
                    }
                }
            } catch (e2) {
                // retry failed, fall through to UI message
                debug('Retry failed: ' + (e2 && e2.message ? e2.message : e2));
            }

            if (!room) {
                // For non-admin users, keep UI clean (no raw error). Admins see console/log only.
                try {
                    @if(isset($isAdmin) && $isAdmin)
                        document.getElementById('video-root').innerText = 'Failed to connect: ' + msg;
                    @else
                        document.getElementById('video-root').innerHTML = '<div class="cs-connection-issue">Reconnecting… (Please check camera/mic permissions)</div>';
                    @endif
                } catch(e){}
                return;
            }
        }

        // Hang up flow: show confirmation modal instead of immediate confirm
        const hangupBtn = document.getElementById('hangup');
        const modalBackdrop = document.getElementById('cs-modal-backdrop');
        const modalCancel = document.getElementById('cs-modal-cancel');
        const modalConfirm = document.getElementById('cs-modal-confirm');

        function showModal(){ if (modalBackdrop) modalBackdrop.style.display = 'flex'; }
        function hideModal(){ if (modalBackdrop) modalBackdrop.style.display = 'none'; }

        hangupBtn && hangupBtn.addEventListener('click', function(){
            showModal();
        });

        modalCancel && modalCancel.addEventListener('click', function(){ hideModal(); });

        modalBackdrop && modalBackdrop.addEventListener('click', function(e){ if (e.target === modalBackdrop) hideModal(); });

        modalConfirm && modalConfirm.addEventListener('click', function(){
            // perform the hangup then redirect the user back to the coaching index
            try {
                if (room) {
                    room.localParticipant.tracks.forEach(publication => {
                        if (publication.track) publication.track.stop();
                    });
                    room.disconnect();
                }
            } catch (e) { debug('Error during hangup: ' + (e && e.message)); }
            const local = document.getElementById('local-media'); if (local) local.innerHTML = '';
            const remote = document.getElementById('remote-media'); if (remote) remote.innerHTML = '';
            hideModal();
            // redirect to coaching index
            try {
                window.location.href = {!! json_encode(route('coaching.index')) !!};
            } catch (e) {
                // fallback
                window.location.href = '/coaching';
            }
        });

    }); // DOMContentLoaded
}).catch(function(err){
    console.error('[coaching.session] Twilio SDK error:', err);
    var root = document.getElementById('video-root');
    if (root) {
        @if(isset($isAdmin) && $isAdmin)
            root.innerText = 'Video SDK failed to load: ' + (err && err.message ? err.message : err);
        @else
            root.innerHTML = '<div class="cs-connection-issue">Video is loading…</div>';
        @endif
    }
});
</script>
@endpush
