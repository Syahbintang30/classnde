@extends('layouts.admin')

@section('title', 'Tambah Topik')

@section('content')
<div class="header mb-4">
    <h2>Tambah Topik Materi {{ $lesson->title }}</h2>
</div>

<form action="{{ route('admin.topics.store', $lesson->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="label">Judul Topik</label>
        <input type="text" name="title" class="input form-control" required>
    </div>
    <div class="mb-3">
        <label class="label">Bunny Video ID (GUID)</label>
        <input type="text" name="bunny_guid" class="form-control input" placeholder="Masukkan Bunny video GUID, contoh: 123e4567-e89b-12d3-a456-426614174000" value="{{ old('bunny_guid') }}">
        <small class="form-text">Upload video langsung ke Bunny.net melalui panel Bunny, kemudian salin GUID video dan tempel di sini.</small>
    </div>
    <div class="mb-3">
        <label class="label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <!-- Thumbnail input removed: thumbnails are no longer stored in DB. Use Bunny GUID for thumbnails. -->
    <div class="mb-3">
        <label class="label">Position</label>
        <input type="number" name="position" class="input form-control" value="0">
    </div>
    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Simpan</button>
        <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn-back">Kembali</a>
    </div>
</form>
@endsection

@section('scripts')
<!-- No client-side upload flow: admins should upload in Bunny and paste GUID here -->
@endsection

