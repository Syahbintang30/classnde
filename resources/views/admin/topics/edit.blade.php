@extends('layouts.admin')

@section('title', 'Edit Topik')

@section('content')
<h1>Edit Topik untuk {{ $lesson->title }}</h1>
<form action="{{ route('admin.topics.update', [$lesson->id, $topic->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="mb-3">
        <label>Judul Topik</label>
        <input type="text" name="title" value="{{ $topic->title }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Bunny Video ID (GUID)</label>
        <input type="text" name="bunny_guid" value="{{ $topic->bunny_guid }}" class="form-control" placeholder="Masukkan Bunny video GUID jika ada">
        <small class="form-text text-muted">Jika Anda sudah mengupload video manual di Bunny, masukkan GUID di sini.</small>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control">{{ $topic->description }}</textarea>
    </div>
    <div class="mb-3">
        <label>Thumbnail (opsional)</label>
    <!-- Thumbnail preview removed: thumbnails are no longer stored in DB -->
        <!-- Thumbnail field removed: thumbnails are no longer stored in DB; use bunny_guid for thumbnails if available -->
    </div>
    <div class="mb-3">
        <label>Position</label>
        <input type="number" name="position" value="{{ $topic->position ?? 0 }}" class="form-control">
    </div>
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection

@section('scripts')
<!-- Client-side upload removed. Admins should paste Bunny GUID manually. -->
@endsection
