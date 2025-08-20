@extends('layouts.admin')

@section('title', 'Detail Lesson')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ $lesson->title }}</h1>
    <a href="{{ route('admin.topics.create', $lesson->id) }}" class="btn btn-primary">+ Tambah Topik</a>
</div>

{{-- Lesson-level headline/subheadline/youtube/description removed; topics contain per-topic data now --}}

<h4>Daftar Topik</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Position</th>
            <th>Judul Topik</th>
            <th>Video URL</th>
            <th>Description</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topics as $topic)
        <tr>
            <td>{{ $topic->position }}</td>
            <td>{{ $topic->title }}</td>
            <td><a href="{{ $topic->video_url }}" target="_blank">Lihat Video</a></td>
            <td>{{ Str::limit($topic->description, 80) }}</td>
            <td>
                <a href="{{ route('admin.topics.edit', [$lesson->id, $topic->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('admin.topics.destroy', [$lesson->id, $topic->id]) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center">Belum ada topik</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $topics->links() }}
@endsection
