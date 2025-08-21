@php
    $firstTopic = $lesson->topics->first();
    $initialGuid = $firstTopic?->bunny_guid ?? '';
    $initialUrl = $firstTopic?->video_url ?? '';
@endphp

<h1 id="video-title">{{ $firstTopic->title ?? 'No Topic' }}</h1>
<p id="video-description">{{ $firstTopic->description ?? '' }}</p>
<div class="player-wrapper">
    <div id="player" style="position:relative;overflow:hidden;min-height:360px;">
        {{-- HTML5 player will be injected here when playing Bunny HLS/MP4 --}}
       <div id="video-placeholder" class="video-placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#000;"
           data-bunny-guid="{{ $initialGuid }}"
           data-video-id="{{ preg_match('/(youtu\.be\/|v=)([A-Za-z0-9_-]{11})/', $initialUrl, $m) ? ($m[2] ?? '') : '' }}"
       >
            <button id="custom-play" class="custom-play-btn" aria-label="Play video"></button>
        </div>
    </div>
</div>

<div class="video-controls" style="display:flex;gap:12px;justify-content:center;margin-top:12px;">
    <button id="btn-prev" class="video-nav-btn" aria-label="Previous topic">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="label">Back</span>
    </button>
    <button id="btn-next" class="video-nav-btn" aria-label="Next topic">
        <span class="label">Next</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
    <!-- ripple element container (dynamically filled) -->
</div>

