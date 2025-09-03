@extends('layouts.admin')

@section('title', 'Daftar Lesson')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 header">
        <h2>Daftar Lesson</h2>
        <a href="{{ route('admin.lessons.create') }}" class="btn-add">+ Add Lessons</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success" id="success-alert">
            {{ session('success') }}
        </div>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.transition = "opacity 0.5s ease";
                    alert.style.opacity = "0";
                    setTimeout(() => alert.remove(), 500);
                }
            }, 3000);
        </script>
    @endif
    <div class="card-table">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Type</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lessons as $lesson)
                    <tr onclick="window.location='{{ route('admin.lessons.show', $lesson->id) }}'" style="cursor:pointer;">
                        <td>{{ $lesson->title }}</td>
                        <td>{{ $lesson->type ?? 'Course' }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.lessons.edit', $lesson->id) }}" method="GET" class="d-inline">
                                <button class="icon-btn ph-duotone ph-pencil-simple-line" 
                                onmouseover="this.style.color='#ffbc6b'"onmouseout="this.style.color=''"
                                onclick="event.stopPropagation()"></button>
                            </form>
                            <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="icon-btn ph-duotone ph-trash btn-delete" onclick="event.stopPropagation()"></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr style="pointer-events: none; background: transparent;">
                        <td colspan="3" class="text-center pt-5">Belum ada lesson</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{ $lessons->links('pagination::bootstrap-5') }}
    </div>

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