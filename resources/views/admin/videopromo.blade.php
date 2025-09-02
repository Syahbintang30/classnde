@extends('layouts.admin')

@section('title','Company Profile - Video Promo')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Company Profile - Video Promo</div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="post" action="{{ route('admin.videopromo.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Bunny Video GUID</label>
                        <input type="text" name="promo_bunny_guid" class="form-control" value="{{ old('promo_bunny_guid', $guid) }}" placeholder="GUID">
                        <div class="form-text">Paste Bunny Stream GUID (e.g., 123e4567-e89b-12d3-a456-426614174000)</div>
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
@endsection
