@extends('layouts.admin')

@section('title', 'Tambah Topik')

@section('content')
<h1>Tambah Topik untuk {{ $lesson->title }}</h1>
<form action="{{ route('admin.topics.store', $lesson->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label>Judul Topik</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Bunny Video ID (GUID)</label>
        <input type="text" name="bunny_guid" class="form-control" placeholder="Masukkan Bunny video GUID, contoh: 123e4567-e89b-12d3-a456-426614174000" value="{{ old('bunny_guid') }}">
        <small class="form-text text-muted">Upload video langsung ke Bunny.net melalui panel Bunny, kemudian salin GUID video dan tempel di sini.</small>
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

@section('scripts')
<!-- No client-side upload flow: admins should upload in Bunny and paste GUID here -->
@endsection
@endsection
