@extends('layouts.admin')

@section('title', 'Edit Lesson')

@section('content')
<h1>Edit Lesson</h1>
<form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" value="{{ $lesson->title }}" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Position (urutan)</label>
        <input type="number" name="position" value="{{ $lesson->position ?? 0 }}" class="form-control">
    </div>
    <div class="mb-3">
        <label>Type</label>
        <select name="type" class="form-control">
            <option value="course" {{ ($lesson->type ?? 'course') === 'course' ? 'selected' : '' }}>Course</option>
            <option value="song" {{ ($lesson->type ?? '') === 'song' ? 'selected' : '' }}>Song</option>
        </select>
    </div>
    
    <button class="btn btn-primary">Update</button>
    <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary">Kembali</a>
</form>
@endsection
