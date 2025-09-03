@extends('layouts.admin')

@section('title', 'Edit Topik')

@section('content')
<div class="header mb-4">
    <h2>Edit Topik Materi {{ $lesson->title }}</h2>
</div>
<form action="{{ route('admin.topics.update', [$lesson->id, $topic->id]) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="label">Judul Topik</label>
        <input type="text" name="title" value="{{ $topic->title }}" class="form-control input" required>
    </div>
    <div class="mb-3">
        <label class="label">Bunny Video ID (GUID)</label>
        <input type="text" name="bunny_guid" value="{{ $topic->bunny_guid }}" class="form-control input" placeholder="Masukkan Bunny video GUID jika ada">
        <small class="form-text">Jika Anda sudah mengupload video manual di Bunny, masukkan GUID di sini.</small>
    </div>
    <div class="mb-3">
        <label class="label">Description</label>
        <textarea name="description" class="form-control">{{ $topic->description }}</textarea>
    </div>
    <div class="mb-3">
        <label class="label">Thumbnail (opsional)</label>
    <!-- Thumbnail preview removed: thumbnails are no longer stored in DB -->
        <!-- Thumbnail field removed: thumbnails are no longer stored in DB; use bunny_guid for thumbnails if available -->
    </div>
    <div class="mb-3">
        <label class="label">Position</label>
        <input type="number" name="position" value="{{ $topic->position ?? 0 }}" class="form-control input">
    </div>
    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Update</button>
        <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn-back">Kembali</a>
    </div>
</form>
@endsection

@section('scripts')
<!-- Client-side upload removed. Admins should paste Bunny GUID manually. -->
@endsection
