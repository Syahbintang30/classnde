@extends('layouts.admin')

@section('title', 'Topics List')

@section('content')
<h1>Topics</h1>

<a href="{{ route('admin.topics.create') }}" class="btn btn-primary mb-3">Add Topic</a>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Lesson</th>
            <th>Title</th>
            <th>Content</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topics as $topic)
        <tr>
            <td>{{ $topic->id }}</td>
            <td>{{ $topic->lesson->title ?? 'N/A' }}</td>
            <td>{{ $topic->title }}</td>
            <td>{{ Str::limit($topic->content,50) }}</td>
            <td>
                <a href="{{ route('admin.topics.edit', $topic->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.topics.destroy', $topic->id) }}" method="POST" style="display:inline-block;">
                <div class="mb-3">
            <label>Video URL (BunnyNet)</label>
            <input type="url" name="video_url" class="form-control" required>
        </div>
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5">No topics found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $topics->links() }}
@endsection
