@extends('layouts.admin')

@section('title','Create Package')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-3 text-dark">Create Package</h3>
                    <form method="POST" action="{{ route('admin.packages.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label text-dark">Name</label>
                            <input name="name" value="{{ old('name') }}" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Slug (e.g. beginner/intermediate/coaching-ticket)</label>
                            <input name="slug" value="{{ old('slug') }}" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Price (Rupiah ex:125000)</label>
                            <input name="price" value="{{ old('price') }}" type="number" class="form-control" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Description</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-dark">Benefits (one per line)</label>
                            <textarea name="benefits" class="form-control" rows="5" placeholder="Write each benefit on its own line">{{ old('benefits') }}</textarea>
                            <div class="form-text">Benefits will be shown as a list on the class cards. Use one benefit per line.</div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary me-2">Back</a>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
