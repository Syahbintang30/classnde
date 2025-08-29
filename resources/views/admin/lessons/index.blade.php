@extends('layouts.admin')

@section('title', 'Daftar Lesson')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Daftar Lesson</h1>
        <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">+ Tambah Lesson</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Type</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessons as $lesson)
                <tr>
                    <td>{{ $lesson->title }}</td>
                    <td>{{ $lesson->type ?? 'course' }}</td>
                    <td>
                        <a href="{{ route('admin.lessons.show', $lesson->id) }}" class="btn btn-info btn-sm">Detail</a>
                        <a href="{{ route('admin.lessons.edit', $lesson->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">Belum ada lesson</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $lessons->links() }}
@endsection