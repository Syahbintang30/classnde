@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 header">
        <h2>Vouchers</h2>
        <a href="{{ route('admin.vouchers.create') }}" class="btn-add">+ Add Voucher</a>
    </div>
        
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card-table">
        <table class="custom-table">
            <thead><tr><th>Code</th><th>Discount %</th><th>Active</th><th>Usage</th><th>Expires At</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($vouchers as $v)
                    <tr>
                        <td>{{ $v->code }}</td>
                        <td>{{ $v->discount_percent }}</td>
                        <td>{{ $v->active ? 'Yes' : 'No' }}</td>
                        <td>{{ $v->used_count }}{{ $v->usage_limit ? '/'.$v->usage_limit : '' }}</td>
                        <td>{{ $v->expires_at }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.vouchers.edit', $v->id) }}" method="GET" class="d-inline">
                                <button class="icon-btn ph-duotone ph-pencil-simple-line" 
                                onmouseover="this.style.color='#ffbc6b'"onmouseout="this.style.color=''"
                                onclick="event.stopPropagation()"></button>
                            </form>
                            <form action="{{ route('admin.vouchers.destroy', $v->id) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="icon-btn ph-duotone ph-trash btn-delete" onclick="event.stopPropagation()"></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr style="pointer-events: none; background: transparent;">
                        <td colspan="6" class="text-center pt-5">Belum ada vouchers</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- {{ $vouchers->links('pagination::bootstrap-5') }} --}}
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