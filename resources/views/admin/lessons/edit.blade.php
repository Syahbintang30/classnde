@extends('layouts.admin')

@section('title', 'Edit Lesson')

@section('content')
<div class="header mb-4">
    <h2>Edit Lesson</h2>
</div>
<form action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="label">Judul</label>
        <input type="text" name="title" value="{{ $lesson->title }}" class="input form-control" required>
    </div>
    <div class="mb-3">
        <label class="label">Position (urutan)</label>
        <input type="number" name="position" value="{{ $lesson->position ?? 0 }}" class="input form-control">
    </div>
    <div class="select-menu">
        <label class="label">Type</label>
        <input type="hidden" name="type" id="typeInput" value="{{ $lesson->type ?? 'course' }}">

        <!-- Custom dropdown -->
        <div class="select-btn">
            <span class="btn-text">
                {{ ucfirst($lesson->type ?? 'course') }}
            </span>
            <i class="ph ph-caret-down"></i>
        </div>

        <ul class="options">
            <li class="option" data-value="course">
                <span class="option-text">Course</span>
            </li>
            <li class="option" data-value="song">
                <span class="option-text">Song</span>
            </li>
        </ul>
    </div>
    
    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Update</button>
        <a href="{{ route('admin.lessons.index') }}" class="btn-back">Kembali</a>
    </div>
</form>
@endsection
