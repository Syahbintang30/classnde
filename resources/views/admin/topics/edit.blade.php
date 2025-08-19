@extends('layouts.admin')

@section('title', 'Edit Topik')

@section('content')
<h1>Edit Topik untuk {{ $lesson->title }}</h1>
<form action="{{ route('admin.topics.update', [$lesson->id, $topic->id]) }}" method="POST">
    @csrf @method('PUT')
    <div class="mb-3">
        <label>Judul Topik</label>
        <input type="text" name="title" value="{{ $topic->title }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Video URL</label>
        <input type="url" name="video_url" value="{{ $topic->video_url }}" class="form-control" required>
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection
