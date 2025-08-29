@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">Payment Methods</h1>
            </div>

            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="mb-3">
                <a href="#" id="show-add-method" class="btn btn-sm btn-outline-primary">+ Add Payment Method</a>
            </div>

            <div class="mb-2 text-muted small">Mapping note: choose a Midtrans Type to let the system use Midtrans payment flows (e.g. QRIS, GoPay, Bank Transfer). If you select Bank Transfer, you must also choose a Bank for proper Midtrans configuration.</div>

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

            <div id="add-method-form" class="card mb-3 d-none">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.payment-methods.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input name="name" class="form-control" placeholder="internal_name (e.g. BCA, gopay, QRIS)" required />
                            </div>
                            <div class="col-md-3">
                                <input name="display_name" class="form-control" placeholder="Display name (BCA / GoPay)" required />
                            </div>
                            <div class="col-md-3">
                                <input name="account_details" class="form-control" placeholder="Account number / phone (optional)" />
                            </div>
                            <div class="col-md-2">
                                <input type="file" name="logo" accept="image/*" class="form-control" />
                            </div>
                            <div class="col-md-2">
                                <select name="midtrans_code" class="form-control">
                                    <option value="">-- Midtrans Type --</option>
                                    <option value="bank_transfer" {{ old('midtrans_code') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="qris" {{ old('midtrans_code') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                    <option value="gopay" {{ old('midtrans_code') == 'gopay' ? 'selected' : '' }}>GoPay</option>
                                    <option value="shopeepay" {{ old('midtrans_code') == 'shopeepay' ? 'selected' : '' }}>ShopeePay</option>
                                    <option value="credit_card" {{ old('midtrans_code') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="midtrans_bank" class="form-control">
                                    <option value="">-- Bank (optional) --</option>
                                    <option value="bca" {{ old('midtrans_bank') == 'bca' ? 'selected' : '' }}>BCA</option>
                                    <option value="bni" {{ old('midtrans_bank') == 'bni' ? 'selected' : '' }}>BNI</option>
                                    <option value="bri" {{ old('midtrans_bank') == 'bri' ? 'selected' : '' }}>BRI</option>
                                    <option value="mandiri" {{ old('midtrans_bank') == 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                                    <option value="permata" {{ old('midtrans_bank') == 'permata' ? 'selected' : '' }}>Permata</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked />
                                    <label class="form-check-label small">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-primary btn-sm">Create</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ url('/admin/payment-methods/update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="list-group">
                            @foreach($methods as $method)
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center" style="gap:12px">
                                        <div style="width:64px;height:48px;">
                                            @if($method->logo_url)
                                                <img src="{{ asset($method->logo_url) }}" style="max-width:100%;height:100%;object-fit:contain" />
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $method->display_name }} <small class="text-muted">({{ $method->name }})</small></div>
                                            @if($method->name !== 'QRIS')
                                                <div class="mt-2">
                                                    <label class="form-label">Account Details (No. Rekening / No. HP)</label>
                                                    <input type="text" name="methods[{{ $method->id }}][account_details]" value="{{ old('methods.'.$method->id.'.account_details', $method->account_details) }}" class="form-control form-control-sm" />
                                                    <div class="mt-2">
                                                        <label class="form-label small">Change Logo (optional)</label>
                                                        <input type="file" name="methods[{{ $method->id }}][logo]" accept="image/*" class="form-control form-control-sm" />
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="form-label small">Midtrans Type</label>
                                                        <select name="methods[{{ $method->id }}][midtrans_code]" class="form-control form-control-sm">
                                                            <option value="">(none)</option>
                                                            <option value="bank_transfer" {{ $method->midtrans_code === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                            <option value="qris" {{ $method->midtrans_code === 'qris' ? 'selected' : '' }}>QRIS</option>
                                                            <option value="gopay" {{ $method->midtrans_code === 'gopay' ? 'selected' : '' }}>GoPay</option>
                                                            <option value="shopeepay" {{ $method->midtrans_code === 'shopeepay' ? 'selected' : '' }}>ShopeePay</option>
                                                            <option value="credit_card" {{ $method->midtrans_code === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                                        </select>
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="form-label small">Midtrans Bank (optional)</label>
                                                        <select name="methods[{{ $method->id }}][midtrans_bank]" class="form-control form-control-sm">
                                                            <option value="">(none)</option>
                                                            <option value="bca" {{ $method->midtrans_bank === 'bca' ? 'selected' : '' }}>BCA</option>
                                                            <option value="bni" {{ $method->midtrans_bank === 'bni' ? 'selected' : '' }}>BNI</option>
                                                            <option value="bri" {{ $method->midtrans_bank === 'bri' ? 'selected' : '' }}>BRI</option>
                                                            <option value="mandiri" {{ $method->midtrans_bank === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                                                            <option value="permata" {{ $method->midtrans_bank === 'permata' ? 'selected' : '' }}>Permata</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-2 text-muted">QRIS configuration uses MIDTRANS API key from .env. No account details needed here.</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end" style="display:flex;gap:8px;align-items:center">
                                        <div>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="methods[{{ $method->id }}][is_active]" value="0" />
                                                <input class="form-check-input" type="checkbox" name="methods[{{ $method->id }}][is_active]" value="1" {{ $method->is_active ? 'checked' : '' }} />
                                                <label class="form-check-label small">Active</label>
                                            </div>
                                        </div>
                                        <div>
                                            <form method="POST" action="{{ route('admin.payment-methods.destroy', $method->id) }}" onsubmit="return confirm('Delete payment method &quot;'+ '{{ $method->display_name }}' + '&quot;? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
</script>
@endsection
