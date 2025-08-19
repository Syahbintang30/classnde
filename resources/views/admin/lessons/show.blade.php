@extends('layouts.admin')

@section('title', 'Detail Lesson')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>{{ $lesson->title }}</h1>
    <a href="{{ route('admin.topics.create', $lesson->id) }}" class="btn btn-primary">+ Tambah Topik</a>
</div>

<p>{{ $lesson->description }}</p>

<h4>Daftar Topik</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Judul Topik</th>
            <th>Video URL</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topics as $topic)
        <tr>
            <td>{{ $topic->title }}</td>
            <td><a href="{{ $topic->video_url }}" target="_blank">Lihat Video</a></td>
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
