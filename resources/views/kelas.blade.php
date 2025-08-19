@extends('layouts.app')

@section('title', 'Kelas Guitar')

@section('content')
<div class="kelas-container" style="display: flex;">
    <!-- Sidebar -->
    <aside class="sidebar" style="width: 250px; border-right:1px solid #ccc; padding:1rem;">
        <div class="logo-container" style="margin-bottom:1rem;">
            <a href="{{ route('kelas') }}">
                <img src="{{ asset('compro/img/ndelogo.png') }}" class="nav-home-btn" alt="Nde Logo">
            </a>
        </div>

        <ul class="menu" style="list-style:none; padding:0;">
            @forelse($lessons as $lesson)
                <li class="lesson-title" style="font-weight:bold; margin-top:1rem;">{{ $lesson->title }}</li>
                @forelse($lesson->topics as $topic)
                    <li class="topic-item" 
                        data-video="{{ $topic->video_url }}" 
                        data-description="{{ $topic->description }}">
                        {{ $topic->title }}
                    </li>
                @empty
                    <li class="topic-item disabled" style="padding-left:1rem; color:#999;">No topics available</li>
                @endforelse
            @empty
                <li>Tidak ada lesson tersedia</li>
            @endforelse
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" style="flex:1; padding:1rem;">
        <!-- Navbar -->
        <!-- Navbar -->
    <header class="navbar">
        <div class="nav-left">
            <button class="burger" onclick="toggleSidebar()">☰</button>
            <a href="{{ route('kelas') }}" class="nav-home-btn">
                <i class="ph-bold ph-house-simple nav-icon"></i>
                <span>Home</span>
            </a>
        </div>
        <div class="nav-right">
            <a href="{{ route('kelas') }}">Lessons</a>
            <a href="#">Coaching</a>
            <a href="#">Song Tutorial</a>
        </div>
    </header>


        <!-- Topik Content -->
        <main class="content">
            @php
                $firstTopic = $lessons->first()?->topics->first();
            @endphp

            <h1 id="video-title">{{ $firstTopic->title ?? 'No Topic' }}</h1>
            <p id="video-description">{{ $firstTopic->description ?? '' }}</p>
            <iframe id="lesson-video" width="100%" height="400"
                    src="{{ $firstTopic->video_url ?? '' }}"
                    frameborder="0" allowfullscreen></iframe>
        </main>
    </div>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Update video, title, description saat klik topik
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.topic-item[data-video]').forEach(item => {
        item.addEventListener('click', () => {
            const videoUrl = item.getAttribute('data-video');
            const title = item.textContent.trim();
            const description = item.getAttribute('data-description');

            document.getElementById('lesson-video').src = videoUrl;
            document.getElementById('video-title').textContent = title;
            document.getElementById('video-description').textContent = description;
        });
    });
});
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/kelas.css') }}">
<style>
.kelas-container { display:flex; }
.sidebar { width:250px; border-right:1px solid #ccc; padding:1rem; }
.lesson-title { font-weight:bold; margin-top:1rem; }
.topic-item { cursor:pointer; padding-left:1rem; }
.topic-item.disabled { color:#999; cursor:default; }
.main-wrapper { flex:1; padding:1rem; }
</style>
@endpush
