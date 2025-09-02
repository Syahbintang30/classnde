@extends('layouts.app')

@section('title','Promo Settings')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Promo Video Settings</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="post" action="{{ route('admin.settings.promo.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Bunny Video GUID</label>
                            <input type="text" name="promo_bunny_guid" class="form-control" value="{{ old('promo_bunny_guid', $guid) }}" placeholder="e.g. 123e4567-e89b-12d3-a456-426614174000">
                            <small class="text-muted">If you uploaded a video to Bunny Stream, paste its GUID here.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Promo Title</label>
                            <input type="text" name="promo_title" class="form-control" value="{{ old('promo_title', $title) }}">
                        </div>
                        <button class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
