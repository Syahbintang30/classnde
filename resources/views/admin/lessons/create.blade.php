@extends('layouts.admin')

@section('title', 'Tambah Lesson')

@section('content')
<div class="header mb-4">
    <h2>Tambah Lesson</h2>
</div>

<form action="{{ route('admin.lessons.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="label">Judul</label>
        <input type="text" name="title" class="input form-control" required>
    </div>

    <div class="mb-3">
        <label class="label">Position (urutan)</label>
        <input type="number" name="position" class="input form-control" value="0" required>
    </div>

    <div class="select-menu">
        <label class="label">Type</label>
        <input type="hidden" name="type" id="typeInput" value="{{ $lesson->type ?? 'course' }}" required>

        <div class="select-btn">
            <span class="btn-text">Select your option</span>
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
        <button class="btn-submit">Simpan</button>
        <a href="{{ route('admin.lessons.index') }}" class="btn-back">Kembali</a>
    </div>
</form>

@endsection
