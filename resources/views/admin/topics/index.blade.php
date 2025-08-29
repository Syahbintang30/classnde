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
            <!-- Thumbnail column removed -->
            <th>Video</th>
            <th>Description</th>
            <th>Position</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topics as $topic)
            <td>
            <td>{{ $topic->id }}</td>
            <td>{{ $topic->lesson->title ?? 'N/A' }}</td>
            <td>{{ $topic->title }}</td>
            <td>
                @if($topic->bunny_guid)
                    <a href="{{ route('topics.stream', $topic->id) }}" target="_blank">Bunny: {{ $topic->bunny_guid }}</a>
                @elseif($topic->video_url)
                    <a href="{{ $topic->video_url }}" target="_blank">{{ Str::limit($topic->video_url, 40) }}</a>
                @else
                    -
                @endif
            </td>
            <td>{{ Str::limit($topic->description ?? $topic->content ?? '', 80) }}</td>
            <td>{{ $topic->position ?? '-' }}</td>
            <td>{{ $topic->created_at?->format('Y-m-d') }}</td>
            <td>
                <a href="{{ route('admin.topics.edit', $topic->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('admin.topics.destroy', $topic->id) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8">No topics found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $topics->links() }}
@endsection
