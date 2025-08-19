@extends('layouts.admin')

@section('title', 'Tambah Lesson')

@section('content')
<h1>Tambah Lesson</h1>
<form action="{{ route('admin.lessons.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection
