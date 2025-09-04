@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 header">
        <div class="col-9" >
            <h2 class="h4">Payment Methods</h2>
            <p style="color:#666; font-size:14px">Mapping note: choose a Midtrans Type to let the system use Midtrans payment flows (e.g. QRIS, GoPay, Bank Transfer). If you select Bank Transfer, you must also choose a Bank for proper Midtrans configuration.</p> 
        </div>
        <a href="#" id="show-add-method" class="btn-add">+ Add Payment Method</a>
    </div>   
    
    @if(session('status'))
        <div class="alert alert-success" id="success-alert">
            {{ session('status') }}
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
    
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="add-method-form" class="mb-3 d-none">
        <div class="card-table">
            <form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
                @csrf
                <p class="text-white mt-2 mb-3" style="font-weight: 700">Add Payment</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="label">Internal Name</label>
                        <input name="name" class="form-control input" placeholder="internal_name (e.g. BCA, gopay, QRIS)" required />
                    </div>
                    <div class="col-md-6">
                        <label class="label">Display Name</label>
                        <input name="display_name" class="form-control input" placeholder="Display name (BCA / GoPay)" required />
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <label class="label">Account Number</label>
                        <input name="account_details" class="form-control input" placeholder="Account number / phone (optional)" />
                    </div>
                </div>

                <div class="row mt-2 g-3">
                    <div class="col-md-6 m-0">
                        <label class="label">Midtrans Type</label>
                        <select name="midtrans_code" style="color=#fff" class="form-control input">
                            <option value="">-- Midtrans Type --</option>
                            <option value="bank_transfer" {{ old('midtrans_code') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="qris" {{ old('midtrans_code') == 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="gopay" {{ old('midtrans_code') == 'gopay' ? 'selected' : '' }}>GoPay</option>
                            <option value="shopeepay" {{ old('midtrans_code') == 'shopeepay' ? 'selected' : '' }}>ShopeePay</option>
                            <option value="credit_card" {{ old('midtrans_code') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        </select>
                    </div>
                    <div class="col-md-6 m-0">
                        <label class="label">Bank</label>
                        <select name="midtrans_bank" class="form-control input">
                            <option value="">-- Bank (optional) --</option>
                            <option value="bca" {{ old('midtrans_bank') == 'bca' ? 'selected' : '' }}>BCA</option>
                            <option value="bni" {{ old('midtrans_bank') == 'bni' ? 'selected' : '' }}>BNI</option>
                            <option value="bri" {{ old('midtrans_bank') == 'bri' ? 'selected' : '' }}>BRI</option>
                            <option value="mandiri" {{ old('midtrans_bank') == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                            <option value="permata" {{ old('midtrans_bank') == 'permata' ? 'selected' : '' }}>Permata</option>
                        </select>
                    </div>
                </div>
                    <div class="col-md-4 mt-2">
                        <label class="label">Logo</label>
                        <input type="file" name="logo" accept="image/*" class="form-control input" />
                    </div>
                    <div class="col-md-1 mt-2">
                        <div class="form-check p-0">
                            <label class="label"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                        </div>
                    </div>
                </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn-submit">Create</button>
            </div>
            </form>
        </div>
    </div>

    <div class="card-table">
        <form method="POST" action="{{ url('/admin/payment-methods/update') }}" enctype="multipart/form-data">
            @csrf
            @foreach($methods as $method)
                <p class="text-white mt-2 m-0" style="font-weight: 700">{{ $method->display_name }} <small>({{ $method->name }})</small></p>
                <div style="width:100px;height:auto;">
                    @if($method->logo_url)
                        <img src="{{ asset($method->logo_url) }}" style="max-width:100%;height:100%;object-fit:contain" />
                    @endif
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6 m-0">
                        <label class="label">Account Details (No. Rekening / No. HP)</label>
                        <input type="text" name="methods[{{ $method->id }}][account_details]" value="{{ old('methods.'.$method->id.'.account_details', $method->account_details) }}" class="form-control input" />
                    </div>
                    <div class="col-md-6 m-0">
                        <label class="label">Change Logo (optional)</label>
                        <input type="file" name="methods[{{ $method->id }}][logo]" accept="image/*" class="form-control input" />
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6 m-0">
                        <label class="label small">Midtrans Type</label>
                        <select name="methods[{{ $method->id }}][midtrans_code]" class="form-control input">
                            <option value="">(none)</option>
                            <option value="bank_transfer" {{ $method->midtrans_code === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="qris" {{ $method->midtrans_code === 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="gopay" {{ $method->midtrans_code === 'gopay' ? 'selected' : '' }}>GoPay</option>
                            <option value="shopeepay" {{ $method->midtrans_code === 'shopeepay' ? 'selected' : '' }}>ShopeePay</option>
                            <option value="credit_card" {{ $method->midtrans_code === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        </select>
                    </div>
                    <div class="col-md-6 m-0">
                        <label class="label small">Midtrans Bank (optional)</label>
                        <select name="methods[{{ $method->id }}][midtrans_bank]" class="form-control input">
                            <option value="">(none)</option>
                            <option value="bca" {{ $method->midtrans_bank === 'bca' ? 'selected' : '' }}>BCA</option>
                            <option value="bni" {{ $method->midtrans_bank === 'bni' ? 'selected' : '' }}>BNI</option>
                            <option value="bri" {{ $method->midtrans_bank === 'bri' ? 'selected' : '' }}>BRI</option>
                            <option value="mandiri" {{ $method->midtrans_bank === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                            <option value="permata" {{ $method->midtrans_bank === 'permata' ? 'selected' : '' }}>Permata</option>
                        </select>
                    </div>                                   
                </div>
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="methods[{{ $method->id }}][is_active]" value="0" />
                    <input class="form-check-input" type="checkbox" name="methods[{{ $method->id }}][is_active]" value="1" {{ $method->is_active ? 'checked' : '' }} />
                    <label class="form-check-label text-white small">Active</label>
                </div>
                <div class="d-flex flex-col justify-content-end mt-3 gap-3">
                    <button type="button" class="btn-delete"
                        onclick="deleteMethod({{ $method->id }}, '{{ $method->display_name }}')">
                        Delete
                    </button>
                </div>

            @endforeach
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save</button>
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
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // small enhancement: toggle visibility of add form
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('show-add-method');
        if (!btn) return;
        btn.addEventListener('click', function(e){
            e.preventDefault();
            var form = document.getElementById('add-method-form');
            form.classList.toggle('d-none');
        });
    });

    // enforce client-side validation: when midtrans_code == bank_transfer require midtrans_bank
    document.addEventListener('submit', function(e){
        const form = e.target;
        if (form && form.matches('form')) {
            // check add-method-form or the update form
            const selects = form.querySelectorAll('select[name="midtrans_code"], select[name^="methods"][name$="[midtrans_code]"]');
            for (const sel of selects) {
                const val = sel.value;
                if (val === 'bank_transfer') {
                    // find corresponding bank select
                    let bankSelect = null;
                    if (sel.name === 'midtrans_code') {
                        bankSelect = form.querySelector('select[name="midtrans_bank"]');
                    } else {
                        const prefix = sel.name.replace('[midtrans_code]', '');
                        bankSelect = form.querySelector('select[name="' + prefix + '[midtrans_bank]"]');
                    }
                    if (bankSelect && !bankSelect.value) {
                        e.preventDefault();
                        alert('Please select a bank when Midtrans Type is Bank Transfer.');
                        bankSelect.focus();
                        return false;
                    }
                }
            }
        }
    }, true);

    function deleteMethod(id, name) {
        if(confirm(`Delete payment method "${name}"? This action cannot be undone.`)) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/payment-methods/${id}`;
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            let method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);

            document.body.appendChild(form);
            form.submit();
        }
    }
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
