@extends('layouts.admin')

@section('title', 'Detail Lesson')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 header">
        <h2>{{ $lesson->title }} Detail</h2>
        <a href="{{ route('admin.topics.create', $lesson->id) }}" class="btn-add">+ Tambah Topik</a>
    </div>

    {{-- Lesson-level headline/subheadline/youtube/description removed; topics contain per-topic data now --}}
    <div class="card-table">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Judul Topik</th>
                    <th>Video URL</th>
                    <th>Description</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topics as $topic)
                <tr>
                    <td>{{ $topic->position }}</td>
                    <td>{{ $topic->title }}</td>
                    <td>
                        @if($topic->bunny_guid)
                            <a href="{{ route('topics.stream', $topic->id) }}" target="_blank">Lihat Video (Bunny)</a>
                        @elseif(!empty($topic->video_url))
                            <a href="{{ $topic->video_url }}" target="_blank">Lihat Video</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ Str::limit($topic->description, 80) }}</td>
                    <td class="actions">
                        <form action="{{ route('admin.topics.edit', [$lesson->id, $topic->id]) }}" method="GET" class="d-inline">
                            <button class="icon-btn ph-duotone ph-pencil-simple-line"
                            onmouseover="this.style.color='#ffbc6b'"onmouseout="this.style.color=''"
                            onclick="event.stopPropagation()"></button>
                        </form>
                        <form action="{{ route('admin.topics.destroy', [$lesson->id, $topic->id]) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="icon-btn ph-duotone ph-trash btn-delete" onclick="event.stopPropagation()"></button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr style="pointer-events: none; background: transparent;">
                        <td colspan="5" class="text-center pt-5">Belum ada topik</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $topics->links('pagination::bootstrap-5') }}
        <!-- Modal Konfirmasi -->
        <div class="modal-confirm" id="modalConfirm">
            <div class="modal_content">
                <p class="mb-4 mt-2">Yakin Hapus?</p>
                <div class="actions mt-4">
                    <button id="confirmYes" class="btn-submit">Iya</button>
                    <button id="confirmNo" class="btn-back">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
{{ $topics->links() }}
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const deleteForms = document.querySelectorAll(".delete-form");
    const modal = document.getElementById("modalConfirm");
    const confirmYes = document.getElementById("confirmYes");
    const confirmNo = document.getElementById("confirmNo");

    let currentForm = null;

    document.querySelectorAll(".btn-delete").forEach(btn => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            currentForm = btn.closest("form");
            modal.style.display = "flex";
        });
    });

    confirmYes.addEventListener("click", () => {
        if (currentForm) currentForm.submit();
    });

    confirmNo.addEventListener("click", () => {
        modal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });
});
</script>
@endsection
