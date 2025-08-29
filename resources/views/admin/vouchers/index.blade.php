@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Vouchers</h1>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary mb-3">Add Voucher</a>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <table class="table">
            <thead><tr><th>Code</th><th>Discount %</th><th>Active</th><th>Usage</th><th>Expires At</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($vouchers as $v)
                    <tr>
                        <td>{{ $v->code }}</td>
                        <td>{{ $v->discount_percent }}</td>
                        <td>{{ $v->active ? 'Yes' : 'No' }}</td>
                        <td>{{ $v->used_count }}{{ $v->usage_limit ? '/'.$v->usage_limit : '' }}</td>
                        <td>{{ $v->expires_at }}</td>
                        <td>
                            <a href="{{ route('admin.vouchers.edit', $v->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form method="POST" action="{{ route('admin.vouchers.destroy', $v->id) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
