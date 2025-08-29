@extends('layouts.app')

@section('title', 'Song Tutorial')

@section('content')
<div class="kelas-container" style="display: flex;">
    <!-- Sidebar -->
    <aside class="sidebar" style="width: 250px; border-right:1px solid #ccc; padding:1rem;">
    <div class="logo-container" style="margin-bottom:1rem; display:flex; align-items:center; justify-content:center;">
            <a href="{{ route('song.tutorial') }}">
                <img src="{{ asset('compro/img/ndelogo.png') }}" class="nav-home-btn" alt="Nde Logo">
            </a>
        </div>

        <ul class="menu" style="list-style:none; padding:0;">
            @forelse($lessons as $ls)
                <li class="lesson-block" style="margin-bottom:0.5rem;">
                        <a href="{{ route('song.tutorial.show', $ls->id) }}" class="lesson-header" style="display:flex;align-items:center;cursor:pointer;padding:0.25rem 0;justify-content:space-between;text-decoration:none;">
                            <div class="lesson-left" style="display:flex;align-items:center;">
                                <span class="lesson-logo" style="width:28px;height:28px;margin-right:12px;display:inline-flex;align-items:center;justify-content:center;background:#222;border-radius:6px;font-size:14px;line-height:24px;text-align:center;color:#fff">♫</span>
                                <span class="lesson-title">{{ $ls->title }}</span>
                            </div>
                            <div class="lesson-right">
                                <span class="lesson-arrow" style="display:inline-block;width:18px;text-align:center;">▾</span>
                            </div>
                        </a>
                    @php $topics = $ls->topics ?? collect(); @endphp
                    <ul class="topic-list" style="list-style:none;padding-left:26px;display:none;">
                        @forelse($topics as $topic)
                            <li class="topic-item" 
                                data-bunny-guid="{{ $topic->bunny_guid }}"
                                data-description="{{ $topic->description }}"
                                data-topic-id="{{ $topic->id }}"
                                style="padding:6px 0; display:flex; align-items:center;">
                                <div class="topic-box">{{ $topic->title }}</div>
                            </li>
                        @empty
                            <li class="topic-item disabled" style="padding-left:1rem; color:#999;">No topics available</li>
                        @endforelse
                    </ul>
                </li>
            @empty
                <li>Tidak ada lesson tersedia</li>
            @endforelse
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" style="flex:1; padding:1rem;">
    <!-- local navbar removed to use global navbar from layouts.app -->

        <!-- Top-right Back button -->
        <div class="page-actions" style="display:flex;justify-content:flex-end;align-items:center;margin-bottom:12px;">
            <a href="{{ route('song.tutorial.index') }}" class="btn-ghost" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="width:18px;height:18px">
                    <path d="M15 6L9 12L15 18" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Back</span>
            </a>
        </div>


        <!-- Topik Content -->
        <main class="content">
            @php $firstLesson = $lessons->first(); @endphp
            @include('kelas._lesson_content', ['lesson' => $firstLesson])
        </main>
    </div>
</div>

<script>
// initial topic requested from index (via ?topic=ID)
var initialTopicId = {{ request()->has('topic') ? intval(request()->query('topic')) : 'null' }};

function toggleSidebar() {
    const sb = document.querySelector('.sidebar');
    if(!sb) return;
    const isActive = sb.classList.toggle('active');
    // body class for layout state
    document.body.classList.toggle('sidebar-open', isActive);
    // backdrop (created in DOMContentLoaded)
    const bd = document.getElementById('sidebar-backdrop');
    if(bd) bd.classList.toggle('visible', isActive);
    // prevent background scroll when sidebar open on small screens
    if(isActive) document.body.style.overflow = 'hidden'; else document.body.style.overflow = '';
}

// Update video, title, description saat klik topik
// --- HLS / HTML5 player + progress tracking (Bunny CDN) ---
// load hls.js dynamically
(function loadHlsScript(){
    if(window.Hls) return;
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
    s.async = true;
    document.head.appendChild(s);
})();

let player = null; // for YT player or placeholder
let hlsInstance = null; // for hls.js instance
let currentTopicId = null;
let progressTimer = null;

function isYouTubeUrl(url){ return /youtu\.be\/|youtube\.com\/.+v=/.test(url || ''); }

function loadTopicVideo(url, topicId, title, description){
    currentTopicId = topicId;
    document.getElementById('video-title').textContent = title;
    document.getElementById('video-description').textContent = description;
    const placeholder = document.getElementById('video-placeholder');
    // clear prior attributes
    placeholder.removeAttribute('data-video-id');
    placeholder.removeAttribute('data-stream-url');
    placeholder.setAttribute('data-topic-id', topicId || '');

    if(!url) return;

    if(isYouTubeUrl(url)){
        // extract YouTube id for backward compatibility
        const m = url.match(/(youtu\.be\/|v=)([A-Za-z0-9_-]{11})/);
        const videoId = m ? m[2] : null;
        if(videoId){
            const thumb = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
            placeholder.style.backgroundImage = `url(${thumb})`;
            placeholder.setAttribute('data-video-id', videoId);
        }
    } else {
        // assume Bunny / HLS or MP4 full URL or path
        placeholder.style.backgroundImage = '';
        placeholder.setAttribute('data-stream-url', url);
    }

    // clear existing progress timer
    if(progressTimer){ clearInterval(progressTimer); progressTimer = null; }
}

// progress tracking disabled by configuration. reportProgress is a no-op to avoid calling backend.
function reportProgress(markComplete = false){ /* no-op */ }

function createHtml5PlayerAndPlay(streamUrl, topicId){
    // ensure container
    const container = document.getElementById('player');
    if(!container) return;
    // remove any previous html5 player
    let v = document.getElementById('html5-player');
    if(v){ try{ v.pause(); }catch(e){} v.remove(); }
    // destroy hls instance
    if(window._hlsInstance){ try{ window._hlsInstance.destroy(); }catch(e){} window._hlsInstance = null; }

    v = document.createElement('video');
    v.id = 'html5-player'; v.controls = true; v.setAttribute('playsinline','');
    // position the video absolutely so it sits above the thumbnail placeholder
    v.style.position = 'absolute'; v.style.top = '0'; v.style.left = '0'; v.style.width = '100%'; v.style.height = '100%'; v.style.zIndex = '2';
    container.appendChild(v);
    // hide the placeholder so the video element is visible
    try{ const ph = document.getElementById('video-placeholder'); if(ph) ph.style.display = 'none'; }catch(e){}

    // attach HLS if available; if hls.js not yet loaded, load it dynamically then retry
    const attachAndPlay = () => {
        if(window.Hls && Hls.isSupported()){
            const hls = new Hls(); window._hlsInstance = hls; hls.loadSource(streamUrl); hls.attachMedia(v);
            hls.on(Hls.Events.MANIFEST_PARSED, function(){
                // progress tracking disabled: just play
                v.play().catch(()=>{});
            });
        } else {
            // native HLS (iOS) or MP4
            v.src = streamUrl;
            v.addEventListener('loadedmetadata', function once(){ v.removeEventListener('loadedmetadata', once); v.play().catch(()=>{}); });
        }
    };

    if(!window.Hls){
        // try to load hls.js from CDN, then attach
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
        s.async = true;
        s.onload = () => { try{ attachAndPlay(); }catch(e){ console.error('hls attach failed', e); } };
        s.onerror = () => { console.warn('Failed to load hls.js, falling back to native playback'); attachAndPlay(); };
        document.head.appendChild(s);
    } else {
        attachAndPlay();
    }

    // wire events for progress reporting
        v.addEventListener('play', function(){ const sp = document.getElementById('ajax-spinner'); if(sp) sp.classList.remove('show'); });
    v.addEventListener('pause', function(){ /* progress disabled */ });
    v.addEventListener('ended', function(){ /* progress disabled */ });
}

function destroyHtml5Player(){
    const v = document.getElementById('html5-player'); if(v){ try{ v.pause(); }catch(e){} v.remove(); }
    if(window._hlsInstance){ try{ window._hlsInstance.destroy(); }catch(e){} window._hlsInstance = null; }
    if(progressTimer){ clearInterval(progressTimer); progressTimer = null; }
    // restore placeholder visibility when player is destroyed
    try{ const ph = document.getElementById('video-placeholder'); if(ph) ph.style.display = 'flex'; }catch(e){}
}

// SPA-like navigation and page initialization
document.addEventListener('DOMContentLoaded', () => {
    // create a global ajax spinner overlay (hidden by default)
    (function createAjaxSpinner(){
        if(document.getElementById('ajax-spinner')) return;
        const s = document.createElement('div');
        s.id = 'ajax-spinner';
        s.style.display = 'none';
        s.innerHTML = '<div class="spinner-inner"><div class="spinner"></div></div>';
        document.body.appendChild(s);
        // allow CSS transitions via class
    })();

        // create a backdrop for mobile sidebar overlay
        (function createSidebarBackdrop(){
            if(document.getElementById('sidebar-backdrop')) return;
            const b = document.createElement('div');
            b.id = 'sidebar-backdrop';
            b.addEventListener('click', function(){
                // close sidebar when tapping backdrop
                const sb = document.querySelector('.sidebar');
                if(sb && sb.classList.contains('active')) toggleSidebar();
            });
            document.body.appendChild(b);
        })();

    const openLessonsKey = 'kelas_open_lessons';
    // prefetch cache and timers for hover intent
    const prefetchCache = {}; // keyed by contentUrl
    const prefetchTimers = new Map();
    const prefetchControllers = new Map();

    async function prefetchLesson(url){
        try{
            const contentUrl = url.replace(/\/?$/, '') + '/content';
            if(prefetchCache[contentUrl]) return; // already cached
            // avoid duplicate controllers
            if(prefetchControllers.has(contentUrl)) return;
            const ctrl = new AbortController();
            prefetchControllers.set(contentUrl, ctrl);
            const res = await fetch(contentUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: ctrl.signal });
            if(!res.ok){ prefetchControllers.delete(contentUrl); return; }
            const html = await res.text();
            prefetchCache[contentUrl] = html;
            prefetchControllers.delete(contentUrl);
        }catch(e){
            // ignore abort or network errors
            prefetchControllers.delete(url);
        }
    }

    function getOpenLessons(){
        try{ return JSON.parse(localStorage.getItem(openLessonsKey) || '[]'); }catch(e){ return []; }
    }
    function setOpenLessons(arr){ localStorage.setItem(openLessonsKey, JSON.stringify(arr || [])); }

    // force stop player/timers
    function forceStopAll(){
        try{
            if(progressTimer){ clearInterval(progressTimer); progressTimer = null; }
            if(player && typeof player.stopVideo === 'function'){ try{ player.stopVideo(); }catch(e){} }
            if(player && typeof player.destroy === 'function'){ try{ player.destroy(); }catch(e){} }
            player = null;
            currentTopicId = null;
        }catch(e){ console.warn('forceStopAll error', e); }
    }

    // initialize sidebar interactions (toggle/restore open state)
    function initSidebar(){
        const open = getOpenLessons();
        document.querySelectorAll('.lesson-block').forEach(block => {
            const a = block.querySelector('.lesson-header');
            const arrow = block.querySelector('.lesson-arrow');
            const topics = block.querySelector('.topic-list');
            const href = a.getAttribute('href');
            const lessonId = href ? href.split('/').filter(Boolean).pop() : null;

            // restore open state
            if(lessonId && open.includes(lessonId)){
                if(topics) topics.style.display = 'block';
                block.classList.add('active');
                if(arrow) arrow.textContent = '▾';
            }

            // arrow click toggles expand without navigation
            if(arrow){
                arrow.addEventListener('click', (ev) => {
                    ev.preventDefault(); ev.stopPropagation();
                    const isHidden = window.getComputedStyle(topics).display === 'none';
                    topics.style.display = isHidden ? 'block' : 'none';
                    block.classList.toggle('active', isHidden);
                    arrow.textContent = isHidden ? '▾' : '▸';
                    // persist
                    const openNow = getOpenLessons();
                    if(isHidden){ if(!openNow.includes(lessonId)) openNow.push(lessonId); }
                    else { const idx = openNow.indexOf(lessonId); if(idx>-1) openNow.splice(idx,1); }
                    setOpenLessons(openNow);
                });
            }

            // header (anchor) click -> toggle topic list only (do not navigate)
            a.addEventListener('click', function(ev){
                // if click originated from arrow, ignore (arrow handled above)
                if(ev.target.closest('.lesson-arrow')){ return; }
                ev.preventDefault();
                // toggle topics visible state
                const isHidden = window.getComputedStyle(topics).display === 'none';
                topics.style.display = isHidden ? 'block' : 'none';
                block.classList.toggle('active', isHidden);
                if(arrow) arrow.textContent = isHidden ? '▾' : '▸';
                // persist open state
                const openNow = getOpenLessons();
                if(isHidden){ if(lessonId && !openNow.includes(lessonId)) openNow.push(lessonId); }
                else { if(lessonId){ const idx = openNow.indexOf(lessonId); if(idx>-1) openNow.splice(idx,1); } }
                setOpenLessons(openNow);
            });

            // hover intent: start a short timer then prefetch partial
            a.addEventListener('mouseenter', function(){
                const url = a.getAttribute('href');
                if(!url) return;
                const t = setTimeout(()=>{
                    prefetchLesson(url);
                    prefetchTimers.delete(a);
                }, 180);
                prefetchTimers.set(a, t);
            });
            a.addEventListener('mouseleave', function(){
                const t = prefetchTimers.get(a);
                if(t){ clearTimeout(t); prefetchTimers.delete(a); }
                // if a prefetch is in-flight, abort it to avoid wasted bandwidth
                const contentUrl = (a.getAttribute('href') || '').replace(/\/?$/, '') + '/content';
                const ctrl = prefetchControllers.get(contentUrl);
                if(ctrl){ try{ ctrl.abort(); }catch(e){} prefetchControllers.delete(contentUrl); }
            });
        });
    }

    // initialize topic handlers and player bindings inside main content
    function initPage(lessonId){
    // topic clicks (sidebar only)
    document.querySelectorAll('.topic-item').forEach(item => {
            item.addEventListener('click', () => {
                const bunnyGuid = item.getAttribute('data-bunny-guid');
                const title = item.textContent.trim();
                const description = item.getAttribute('data-description');
                const topicId = item.getAttribute('data-topic-id');
                // persist last topic for this lesson
                if(lessonId && topicId) localStorage.setItem('kelas_last_topic_' + lessonId, topicId);
                // SPA-style topic navigation (pushState + play)
                // We do not pass a videoUrl; the player will request /topics/{id}/stream to get the signed URL based on bunny_guid
                navigateTopic(lessonId, topicId, null, true);
                // selection highlight
                document.querySelectorAll('.topic-item.selected').forEach(s => s.classList.remove('selected'));
                item.classList.add('selected');
            });
        });

        // progress markers disabled: do not fetch per-topic progress from server
    document.querySelectorAll('.topic-item[data-topic-id]').forEach(item => {
            // no-op
        });

        // play button behavior
        const customPlay = document.getElementById('custom-play');
        if(customPlay){
            customPlay.addEventListener('click', function(){
                const placeholder = document.getElementById('video-placeholder');
                const ytId = placeholder ? placeholder.getAttribute('data-video-id') : null;
                const streamUrlAttr = placeholder ? placeholder.getAttribute('data-stream-url') : null;
                const topicId = placeholder ? placeholder.getAttribute('data-topic-id') : null;

                // If we have a stream URL attribute already, use it
                if(streamUrlAttr){
                    destroyHtml5Player();
                    createHtml5PlayerAndPlay(streamUrlAttr, topicId);
                    return;
                }

                // If placeholder doesn't have stream URL but topic id exists, ask server for it
                if(topicId){
                    fetch(`/topics/${topicId}/stream`).then(r=>r.json()).then(data=>{
                        if(data && data.url){
                            placeholder.setAttribute('data-stream-url', data.url);
                            destroyHtml5Player();
                            createHtml5PlayerAndPlay(data.url, topicId);
                            return;
                        }
                        // fallback to YouTube if present
                            if(ytId){
                            // create or load YT player as before (keep legacy support)
                                // hide placeholder so the iframe is visible
                                try{ const ph = document.getElementById('video-placeholder'); if(ph) ph.style.display = 'none'; }catch(e){}
                                if(!player || typeof player.loadVideoById !== 'function'){
                                player = new YT.Player('player', {
                                    height: '100%', width: '100%', videoId: ytId,
                                    playerVars: { rel:0, modestbranding:1 },
                                    events: { 'onStateChange': onPlayerStateChange, 'onReady': function(e){ player.playVideo(); }}
                                });
                            } else { player.loadVideoById(ytId); player.playVideo(); }
                        }
                    }).catch(err => {
                        console.warn('stream lookup failed', err);
                        if(ytId){
                            try{ const ph = document.getElementById('video-placeholder'); if(ph) ph.style.display = 'none'; }catch(e){}
                            if(!player){ player = new YT.Player('player', { height:'100%', width:'100%', videoId: ytId, playerVars:{rel:0,modestbranding:1}, events:{'onStateChange':onPlayerStateChange} }); } else { player.loadVideoById(ytId); }
                        }
                    });
                } else if(ytId){
                    // no topicId but have yt fallback
                    if(!player || typeof player.loadVideoById !== 'function'){
                        player = new YT.Player('player', { height:'100%', width:'100%', videoId: ytId, playerVars:{rel:0,modestbranding:1}, events:{'onStateChange':onPlayerStateChange} });
                    } else { player.loadVideoById(ytId); }
                }
            });
        }

        // restore last topic selection for this lesson if exists
        try{
            if(lessonId){
                    const last = localStorage.getItem('kelas_last_topic_' + lessonId);
                    if(last){
                        const el = document.querySelector('[data-topic-id="' + last + '"]');
                        if(el) { el.click(); /* continue to wire buttons and handlers */ }
                    }
                }
        }catch(e){}

    // otherwise auto-click requested topic (from index) or first topic if present
    if(typeof initialTopicId === 'number' && initialTopicId) {
        const requested = document.querySelector('.topic-item[data-topic-id="' + initialTopicId + '"]');
        if(requested) requested.click();
        else {
            const first = document.querySelector('.topic-item[data-topic-id]');
            if(first) first.click();
        }
    } else {
        const first = document.querySelector('.topic-item[data-topic-id]');
        if(first) first.click();
    }

        // wire next/back buttons (if present in the partial)
        const btnNext = document.getElementById('btn-next');
        const btnPrev = document.getElementById('btn-prev');
        function updateNavButtons(){
            const visibleTopics = Array.from(document.querySelectorAll('.topic-item[data-topic-id]'));
            const sel = document.querySelector('.topic-item.selected');
            const idx = sel ? visibleTopics.indexOf(sel) : -1;
            if(btnPrev) btnPrev.disabled = (idx <= 0);
            if(btnNext) btnNext.disabled = (idx < 0 || idx >= visibleTopics.length - 1);
        }

        if(btnNext){
            btnNext.addEventListener('click', function(){
                console.debug('btnNext clicked');
                const topics = Array.from(document.querySelectorAll('.topic-item[data-topic-id]'));
                let sel = document.querySelector('.topic-item.selected');
                let idx = sel ? topics.indexOf(sel) : -1;
                // fallback: if nothing selected, select first topic first
                if(idx === -1 && topics.length){
                    const firstTopic = topics[0];
                    if(firstTopic){ firstTopic.click(); sel = firstTopic; idx = 0; }
                }
                if(idx >= 0 && idx < topics.length - 1){
                    const nxt = topics[idx+1];
                    if(nxt){ nxt.click(); }
                    // ensure navigation behavior triggers
                    const lessonIdLocal = lessonId || (location.pathname.split('/').filter(Boolean)[1] || null);
                    navigateTopic(lessonIdLocal, nxt.getAttribute('data-topic-id'), null, true);
                    setTimeout(updateNavButtons, 50);
                }
            });
        }
        if(btnPrev){
            btnPrev.addEventListener('click', function(){
                console.debug('btnPrev clicked');
                const topics = Array.from(document.querySelectorAll('.topic-item[data-topic-id]'));
                let sel = document.querySelector('.topic-item.selected');
                let idx = sel ? topics.indexOf(sel) : -1;
                // if nothing selected, select first and do nothing else
                if(idx === -1 && topics.length){
                    const firstTopic = topics[0];
                    if(firstTopic){ firstTopic.click(); sel = firstTopic; idx = 0; }
                }
                if(idx > 0){
                    const prev = topics[idx-1];
                    if(prev){ prev.click(); }
                    const lessonIdLocal = lessonId || (location.pathname.split('/').filter(Boolean)[1] || null);
                    navigateTopic(lessonIdLocal, prev.getAttribute('data-topic-id'), null, true);
                    setTimeout(updateNavButtons, 50);
                }
            });
        }

        // update buttons whenever topics change or selection changes
        document.addEventListener('click', function(ev){ if(ev.target.closest('.topic-item')) setTimeout(updateNavButtons,30); });
        updateNavButtons();

        // ripple effect for nav buttons
        document.querySelectorAll('.video-nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e){
                // create ripple
                const r = document.createElement('span');
                r.className = 'ripple';
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                r.style.width = r.style.height = size + 'px';
                r.style.left = (e.clientX - rect.left - size/2) + 'px';
                r.style.top = (e.clientY - rect.top - size/2) + 'px';
                btn.appendChild(r);
                setTimeout(()=>{ try{ r.remove(); }catch(e){} }, 700);
            });
        });
    }

    // fetch content for a lesson and swap into main content area
    async function navigateTo(url, pushState=true){
        try{
            const spinner = document.getElementById('ajax-spinner');
            if(spinner){ spinner.style.display = 'flex'; setTimeout(()=>spinner.classList.add('show'), 10); }
            const main = document.querySelector('main.content');
            if(main){ main.classList.remove('fade-in'); main.classList.add('fade-out'); }
            const contentUrl = url.replace(/\/?$/, '') + '/content';
            let html;
            if(prefetchCache[contentUrl]){
                html = prefetchCache[contentUrl];
            } else {
                const res = await fetch(contentUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if(!res.ok) { if(spinner){ spinner.classList.remove('show'); setTimeout(()=>spinner.style.display='none',200); } window.location.href = url; return; }
                html = await res.text();
            }
            // wait for fade out transition (250ms)
            await new Promise(r => setTimeout(r, 260));
            document.querySelector('main.content').innerHTML = html;
            // extract lesson id from url
            const lessonId = url.split('/').filter(Boolean).pop();
            if(pushState) history.pushState({ ajax: true, url: url }, '', url);
            // re-init page scripts for newly loaded content
            initPage(lessonId);
            // fade in and hide spinner
            const m = document.querySelector('main.content'); if(m){ m.classList.remove('fade-out'); m.classList.add('fade-in'); }
            if(spinner){ spinner.classList.remove('show'); setTimeout(()=>spinner.style.display='none',220); }
        }catch(e){ console.error('navigate error', e); window.location.href = url; }
    }

    // handle back/forward
    window.addEventListener('popstate', function(ev){
        const path = location.pathname + location.search + location.hash;
        // handle lesson content swap
        if(path.startsWith('/song-tutorial') || path.startsWith('/kelas')){
            forceStopAll();
            // if URL contains topic query or hash, attempt to parse and play topic
            const params = new URLSearchParams(location.search);
            const topicParam = params.get('topic') || (location.hash ? location.hash.replace('#','') : null);
            navigateTo(location.pathname, false).then(()=>{
                if(topicParam){
                    const el = document.querySelector('[data-topic-id="'+topicParam+'"]');
                    if(el) el.click();
                }
            });
        }
    });

    // navigate between topics without full reload
    function navigateTopic(lessonId, topicId, videoUrl, pushState=true){
        try{
            forceStopAll();
            const spinner = document.getElementById('ajax-spinner'); if(spinner){ spinner.style.display='flex'; setTimeout(()=>spinner.classList.add('show'),10); }
            // update placeholder and start playback after creating player
            const placeholder = document.getElementById('video-placeholder');
            if(placeholder){
                // Always set current topic id for the placeholder so the player can request stream metadata
                if(topicId) placeholder.setAttribute('data-topic-id', topicId);

                // If this is a YouTube link, set video-id and thumbnail
                const vid = (videoUrl && videoUrl.match(/(youtu\.be\/|v=)([A-Za-z0-9_-]{11})/)) ? videoUrl.match(/(youtu\.be\/|v=)([A-Za-z0-9_-]{11})/)[2] : null;
                if(vid){
                    placeholder.style.backgroundImage = 'url(https://img.youtube.com/vi/'+vid+'/hqdefault.jpg)';
                    placeholder.setAttribute('data-video-id', vid);
                    // clear any existing stream-url/bunny-guid since this is YouTube
                    placeholder.removeAttribute('data-stream-url');
                    placeholder.removeAttribute('data-bunny-guid');
                } else {
                    // For non-YouTube topics, set bunny GUID from sidebar item if available
                    try{
                        const topicEl = document.querySelector('[data-topic-id="' + topicId + '"]');
                        if(topicEl){
                            const bg = topicEl.getAttribute('data-bunny-guid');
                            if(bg) {
                                placeholder.setAttribute('data-bunny-guid', bg);
                            } else {
                                placeholder.removeAttribute('data-bunny-guid');
                            }
                        }
                        // clear video-id (youtube) if present
                        placeholder.removeAttribute('data-video-id');
                        // also clear any previously cached stream-url so it will be fetched fresh
                        placeholder.removeAttribute('data-stream-url');
                    }catch(e){}
                }
            }
            // update displayed title/description using sidebar item text if available
            try{
                const topicEl = document.querySelector('[data-topic-id="' + topicId + '"]');
                const titleEl = document.getElementById('video-title');
                const descEl = document.getElementById('video-description');
                if(topicEl){
                    if(titleEl) titleEl.textContent = topicEl.textContent.trim();
                    if(descEl) descEl.textContent = topicEl.getAttribute('data-description') || '';
                }
            }catch(e){ /* ignore */ }
            // push topic into URL as query param (keeps lesson path)
            const newUrl = '/song-tutorial/' + lessonId + (topicId ? '?topic=' + topicId : '');
            if(pushState) history.pushState({ ajax: true, url: newUrl }, '', newUrl);
            // create player if needed and play
            if(player){
                player.loadVideoById(placeholder.getAttribute('data-video-id'));
                currentTopicId = topicId;
                player.playVideo();
                // spinner will be hidden on PlayerStateChange when PLAYING
            } else {
                // trigger the custom-play button's click to create player
                const customPlay = document.getElementById('custom-play');
                if(customPlay) customPlay.click();
                // spinner will be hidden once player fires onReady/onStateChange
            }
        }catch(e){ console.error('navigateTopic error', e); }
    }

    // initial setup
    initSidebar();
    // initialize page for current lesson (try to extract lesson id from url)
    const pathParts = location.pathname.split('/').filter(Boolean);
    const currentLessonId = (pathParts.length && (pathParts[0] === 'kelas' || pathParts[0] === 'song-tutorial') && pathParts[1]) ? pathParts[1] : (document.querySelector('.lesson-block') ? document.querySelector('.lesson-block').querySelector('.lesson-header').getAttribute('href').split('/').filter(Boolean).pop() : null);
    initPage(currentLessonId);

    // close overlay sidebar automatically when resizing to desktop
    window.addEventListener('resize', function(){
        try{
            if(window.innerWidth > 900){
                const sb = document.querySelector('.sidebar'); if(sb) sb.classList.remove('active');
                const bd = document.getElementById('sidebar-backdrop'); if(bd) bd.classList.remove('visible');
                document.body.classList.remove('sidebar-open');
                document.body.style.overflow = '';
            }
        }catch(e){}
    });
});
</script>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kelas.css') }}">
<!-- Poppins font for buttons (kept) -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* copied styles from kelas.blade.php to keep exact parity */
.kelas-container { display:flex; }
.sidebar { width:250px; border-right:1px solid #222; padding:1rem; background:#0f0f0f; }
.lesson-title { font-weight:400; margin-top:0; font-size:15px; color:#cfcfcf; }
.lesson-block .lesson-title { transition: color .12s ease, font-weight .12s ease; }
.lesson-block.active .lesson-title { color:#fff; font-weight:700; }
.topic-item { cursor:pointer; padding-left:1rem; font-size:13px; color:#bfbfbf; }
.topic-item.disabled { color:#999; cursor:default; }
.main-wrapper { flex:1; padding:1rem; }
.topic-item.completed { color: #9fd19f; }

/* Use Roboto font for page */
body, .main-wrapper, .sidebar, .kelas-container { font-family: 'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }

/* Video placeholder & play button */
.video-placeholder { background-size: cover; background-position: center; position:relative; display:flex; align-items:center; justify-content:center; }
.custom-play-btn {
    width:96px; height:96px; border-radius:50%; background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,.6);
    position:relative; display:flex; align-items:center; justify-content:center;
}
.custom-play-btn:before {
    content:''; display:block; width:0; height:0; border-left:28px solid #fff; border-top:18px solid transparent; border-bottom:18px solid transparent; position:relative; margin-left:6px;
}

/* Player wrapper to match example size and style (use aspect-ratio so iframe fills frame) */
.player-wrapper { max-width:1100px; margin:1rem auto; }
.player-wrapper #player { width:100%; aspect-ratio:16/9; position:relative; border-radius:14px; overflow:hidden; box-shadow:0 8px 40px rgba(0,0,0,.6); border:1px solid rgba(255,255,255,0.04); }
.player-wrapper iframe, .player-wrapper .video-placeholder { position:absolute; top:0; left:0; width:100%; height:100%; }
.player-wrapper .video-placeholder { z-index:1; }
.player-wrapper iframe { z-index:3; }

.main-wrapper .navbar { margin-bottom:1rem; }
.main-wrapper .content { padding-top:1rem; }
.video-meta { margin-bottom:1.5rem; }

/* Prevent long subtitle/title text from creating horizontal scroll */
#video-description, .video-meta, .video-meta * {
    overflow-wrap: break-word;
    word-wrap: break-word;
    overflow: visible;
    word-break: break-word;
    white-space: normal; /* allow wrapping */
    max-width: 100%;
}

/* Collapsible sidebar styles - improved alignment */
.lesson-header { padding: 8px 6px; margin-bottom:2px; border-radius:6px; }
.lesson-arrow { width:18px; display:inline-block; color:#bbb; margin-left:6px; }
.lesson-logo { background:#222; color:#fff; display:inline-flex; align-items:center; justify-content:center; }
.topic-list { margin-top:2px; }

/* visual highlight left strip when active - simple clean style */
.lesson-block.active > .lesson-header { position:relative; }
.lesson-block.active > .lesson-header:before { 
    content:''; position:absolute; left:-12px; top:2px; bottom:2px; 
    width:3px; background:#fff; border-radius:2px; 
}

/* hover/active styles */
.lesson-header:hover { background: rgba(255,255,255,0.02); }
.lesson-block.active .lesson-title { color:#fff; }

/* Stylish Next/Back buttons */
.video-controls { display:flex; gap:12px; justify-content:center; margin-top:12px; }
.video-nav-btn {
    font-family: 'Poppins', 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    display:inline-flex; align-items:center; gap:10px;
    font-weight:700; font-size:16px; padding:12px 20px; border-radius:12px; border:0; cursor:pointer;
    transition: transform .12s ease, box-shadow .12s ease, background-color .12s ease, color .12s ease;
    position:relative; overflow:hidden;
}
.video-nav-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; box-shadow:none; }
.video-nav-btn#btn-prev { background: linear-gradient(180deg,#ffffff,#f2f2f2); color:#111; box-shadow: 0 8px 30px rgba(0,0,0,0.25); }
.video-nav-btn#btn-next { background: linear-gradient(180deg,#111,#0a0a0a); color:#fff; box-shadow: 0 8px 30px rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.04); }
.video-nav-btn#btn-prev:hover:not(:disabled){ transform: translateY(-4px); box-shadow: 0 26px 60px rgba(0,0,0,0.35); }
.video-nav-btn#btn-next:hover:not(:disabled){ transform: translateY(-4px); box-shadow: 0 26px 80px rgba(0,0,0,0.5); }
.video-nav-btn#btn-prev:active, .video-nav-btn#btn-next:active{ transform: translateY(-2px); }

/* icon sizing inside buttons */
.video-nav-btn svg{ width:18px;height:18px;display:inline-block;}
.video-nav-btn .label{display:inline-block}

/* shine effect */
.video-nav-btn::after{ content:''; position:absolute; left:-60%; top:0; width:40%; height:100%; background: linear-gradient(120deg, rgba(255,255,255,0.06), rgba(255,255,255,0.18), rgba(255,255,255,0.06)); transform: skewX(-18deg) translateX(0); transition: transform .9s ease; pointer-events:none; }
.video-nav-btn:hover::after{ transform: translateX(200%); }

/* ripple effect */
.ripple { position:absolute; border-radius:999px; transform:scale(0); background: rgba(255,255,255,0.14); animation:ripple .6s linear; pointer-events:none; }
@keyframes ripple { to { transform: scale(6); opacity:0; } }


/* connector lines for nested topics - clean text-only style */
.topic-list { position:relative; padding-left:28px; margin-top:8px; }
.topic-item { 
    position:relative; padding:8px 12px 8px 28px; border-radius:4px;
    transition: background-color 0.15s ease; display:flex; align-items:center; line-height:1.35; text-indent:0; word-break:break-word; white-space:normal;
}
.topic-item:hover { background:rgba(255,255,255,0.04); }
.topic-item.selected { 
    color:#fff; font-weight:600; background:rgba(255,255,255,0.08);
    border-left:3px solid #fff; padding-left:19px;
}
/* topic box padding */
.topic-box { padding:8px 12px; }
/* Responsive: mobile-friendly layout */
@media (max-width: 900px) {
    .kelas-container { flex-direction:row; }
    .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 300px; max-width: 80%; transform: translateX(-110%); transition: transform .28s ease; z-index: 1200; box-shadow: 0 10px 40px rgba(0,0,0,0.6); }
    .sidebar.active { transform: translateX(0); }
    /* backdrop when sidebar is open */
    #sidebar-backdrop { position:fixed; inset:0; background: rgba(0,0,0,0.5); opacity:0; transition: opacity .22s ease; z-index:1100; display:none; }
    #sidebar-backdrop.visible { display:block; opacity:1; }
    /* collapse lesson arrow area and increase tap targets */
    .lesson-header { padding: 12px 10px; }
    .topic-item { padding: 10px 14px 10px 34px; }
    .main-wrapper { padding: 0.75rem; }
    .player-wrapper { max-width: 100%; margin: 0.5rem 0; }
    .player-wrapper #player { border-radius:10px; }
    /* make navbar smaller and burger obvious */
    .navbar { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:8px 0; }
    .nav-left .burger { font-size:20px; padding:8px 10px; border-radius:8px; }
    /* ensure content area uses full width when sidebar hidden */
    body:not(.sidebar-open) .main-wrapper { margin-left:0; }
}

@media (max-width: 480px) {
    .lesson-title { font-size:14px; }
    .topic-item { font-size:14px; }
    .video-nav-btn { padding:10px 14px; font-size:15px; }
    .custom-play-btn { width:72px; height:72px; }
}
</style>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kelas.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
@endpush
