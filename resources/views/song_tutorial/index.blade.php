@extends('layouts.app')

@section('title', 'Song Tutorial')

@section('content')
    <div style="max-width:1200px;margin:40px auto;padding:0 18px;color:#fff">
        <h1 style="font-size:22px;margin-bottom:20px">Song Tutorial</h1>

        <style>
            /* Topic grid and card styling */
            .topic-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:18px; }
            .topic-card { background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.25)); border-radius:12px; overflow:hidden; padding:12px; box-shadow:0 8px 30px rgba(0,0,0,0.6); border:1px solid rgba(255,255,255,0.03); transition:transform .28s cubic-bezier(.2,.9,.2,1), box-shadow .28s, border-color .2s; display:block; color:inherit; }
            .topic-card:focus, .topic-card:hover { transform:translateY(-8px) scale(1.01); box-shadow:0 18px 50px rgba(0,0,0,0.7); border-color: rgba(100,220,255,0.12); }
            .topic-thumb { background:#111; height:140px; border-radius:10px; overflow:hidden; display:block; position:relative; }
            .topic-thumb img{ width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s, filter .3s; }
            .topic-card:hover .topic-thumb img{ transform:scale(1.03); filter:brightness(.95) contrast(1.02); }
            .topic-title{ padding-top:12px; color:rgba(255,255,255,0.94); font-weight:700; font-size:15px }
        </style>

        @if($hasIntermediate)
            <p style="color:rgba(255,255,255,0.75);margin-bottom:18px">Welcome to Song Tutorial. Select a song to start learning.</p>

            <div class="topic-grid">
                @foreach($topics as $topic)
                    @php
                        $title = $topic->title ?: ($topic->lesson->title ?? 'Untitled Topic');
                        $thumb = asset('compro/img/ndelogo.png');
                            // Use thumbnail URL prepared by controller when available
                            if (! empty($topic->thumb)) {
                                $thumb = $topic->thumb;
                            } else {
                                $thumb = asset('compro/img/ndelogo.png');
                            }
                    @endphp

                    <a href="{{ route('song.tutorial.show', ['lesson' => $topic->lesson->id]) }}?topic={{ $topic->id }}" class="topic-card">
                        <div class="topic-thumb">
                            <img src="{{ $thumb }}" alt="{{ $title }}">
                        </div>
                        <div class="topic-title">{{ $title }}</div>
                    </a>
                @endforeach
            </div>

        @else
            <div style="background:linear-gradient(180deg,#0f1724,#050607);padding:28px;border-radius:12px;display:flex;align-items:center;gap:18px">
                <div style="flex:1">
                    <h2 style="margin:0 0 8px 0;color:#fff">Song Tutorial is for Intermediate users</h2>
                    <p style="margin:0;color:rgba(255,255,255,0.78)">This feature is available only to users who have the <strong>intermediate</strong> package. You can purchase an upgrade on the class registration page.</p>
                </div>
                <div style="flex:0">
                    @auth
                        <a href="{{ route('registerclass') }}" class="btn-ghost" style="display:inline-block">Go to Register Class</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost" style="display:inline-block">Login to Purchase</a>
                    @endauth
                </div>
            </div>
        @endif
    </div>
@endsection
