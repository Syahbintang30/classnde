@extends('layouts.admin')

@section('title','Edit Package')

@section('content')
<div class="header mb-4">
    <h2>Edit Package</h2>
</div>

<form method="POST" action="{{ route('admin.packages.update', $package->id) }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="mb-3 col-md-6">
            <label class="label">Name</label>
            <input name="name" value="{{ old('name',$package->name) }}" class="form-control input" />
        </div>

        <div class="mb-3 col-md-6">
            <label class="label">Slug</label>
            <input name="slug" value="{{ old('slug',$package->slug) }}" class="form-control input" />
            <small>e.g. beginner/intermediate/coaching-ticket</small>
        </div>
    </div>

    <div class="mb-3">
        <label class="label">Price</label>
        <input name="price" value="{{ old('price',$package->price) }}" type="number" class="form-control input" />
        <small>Rupiah ex:125000</small>
    </div>

    <div class="mb-3">
        <label class="label">Description</label>
        <textarea name="description" class="form-control" rows="5">{{ old('description', $package->description) }}</textarea>
    </div>

    <div class="mb-3">
        <label class="label">Benefits (one per line)</label>
        <textarea name="benefits" class="form-control" rows="5" placeholder="Write each benefit on its own line">{{ old('benefits', $package->benefits) }}</textarea>
        <small>Benefits will be shown as a list on the class cards. Use one benefit per line.</smmall>
    </div>

    <div class="d-flex justify-content-end mt-3 gap-3">
        <button class="btn-submit">Simpan</button>
        <a href="{{ route('admin.packages.index') }}" class="btn-back">Kembali</a>
    </div>
</form>
@endsection
