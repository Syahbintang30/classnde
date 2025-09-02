@extends('layouts.admin')

@section('title','Create Package')

@section('content')
<div class="header mb-4">
    <h2>Create Package</h2>
</div>

<form method="POST" action="{{ route('admin.packages.store') }}">
    @csrf
    <div class="row">
        <div class="mb-3 col-6">
            <label class="label">Name</label>
            <input name="name" value="{{ old('name') }}" class="form-control input w-full" />
        </div>

        <div class="mb-3 col-6">
            <label class="label">Slug</label>
            <input name="slug" value="{{ old('slug') }}" class="form-control input" />
            <small>e.g. beginner/intermediate/coaching-ticket</small>
        </div>
    </div>

    <div class="mb-3">
        <label class="label">Price</label>
        <input name="price" value="{{ old('price') }}" type="number" class="form-control input" />
        <small>Rupiah ex:125000</small>
    </div>

    <div class="mb-3">
        <label class="label">Description</label>
        <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="label">Benefits (one per line)</label>
        <textarea name="benefits" class="form-control" rows="5" placeholder="Write each benefit on its own line">{{ old('benefits') }}</textarea>
        <small>Benefits will be shown as a list on the class cards. Use one benefit per line.</small>
    </div>

    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Simpan</button>
        <a href="{{ route('admin.packages.index') }}" class="btn-back">Kembali</a>
    </div>
</form>
@endsection
