@extends('layouts.admin')

@section('title','Manage Packages')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 header">
        <h2>Packages</h2>
        @if(session('status'))
            <div class="alert alert-success mb-0">{{ session('status') }}</div>
        @endif
        <a href="{{ route('admin.packages.create') }}" class="btn-add">+ Add Package</a>
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
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Benefits</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->slug }}</td>
                    <td style="max-width:320px">
                        @if(!empty($p->benefits))
                            @php
                                $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $p->benefits))));
                            @endphp
                            @if(count($lines))
                                @foreach(array_slice($lines,0,3) as $i => $line)
                                    <div style="font-size:13px">• {{ 
                                        Illuminate\Support\Str::limit($line, 60) }}</div>
                                @endforeach
                                @if(count($lines) > 3)
                                    <div style="font-size:12px;color:#666">...and {{ count($lines) - 3 }} more</div>
                                @endif
                            @endif
                        @elseif(!empty($p->description))
                            <div style="font-size:13px">{{ Illuminate\Support\Str::limit($p->description, 80) }}</div>
                        @else
                            <div style="font-size:13px;color:#888">—</div>
                        @endif
                    </td>
                    <td>Rp {{ number_format($p->price,0,',','.') }}</td>
                    <td class="actions">
                        <form action="{{ route('admin.packages.edit',$p->id) }}" method="GET" class="d-inline">
                            <button class="icon-btn ph-duotone ph-pencil-simple-line" 
                            onmouseover="this.style.color='#ffbc6b'"onmouseout="this.style.color=''"
                            onclick="event.stopPropagation()"></button>
                        </form>
                        <form action="{{ route('admin.packages.destroy', $p->id) }}" method="POST" class="d-inline delete-form">
                            @csrf @method('DELETE')
                            <button type="button" class="icon-btn ph-duotone ph-trash btn-delete" onclick="event.stopPropagation()"></button>
                        </form>
                    </td>
                </tr>
                @empty
                    <tr style="pointer-events: none; background: transparent;">
                        <td colspan="6" class="text-center pt-5">Belum ada package</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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