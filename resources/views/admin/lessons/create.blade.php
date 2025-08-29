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
        <label>Position (urutan)</label>
        <input type="number" name="position" class="form-control" value="0">
    </div>
    <div class="mb-3">
        <label>Type</label>
        <select name="type" class="form-control">
            <option value="course" selected>Course</option>
            <option value="song">Song</option>
        </select>
    </div>
    
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection
