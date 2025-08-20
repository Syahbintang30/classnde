@extends('layouts.admin')

@section('title', 'Tambah Topik')

@section('content')
<h1>Tambah Topik untuk {{ $lesson->title }}</h1>
<form action="{{ route('admin.topics.store', $lesson->id) }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Judul Topik</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Video URL</label>
        <input type="url" name="video_url" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label>Position</label>
        <input type="number" name="position" class="form-control" value="0">
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection
