@extends('layouts.admin')

@section('title','Manage Packages')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0 text-dark">Packages</h2>
                <div class="d-flex align-items-center gap-2">
                    @if(session('status'))
                        <div class="alert alert-success mb-0">{{ session('status') }}</div>
                    @endif
                    <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">+ Add Package</a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
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
                                @foreach($packages as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td class="text-dark">{{ $p->name }}</td>
                                    <td class="text-dark">{{ $p->slug }}</td>
                                    <td class="text-dark" style="max-width:320px">
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
                                    <td class="text-dark">Rp {{ number_format($p->price,0,',','.') }}</td>
                                    <td>
                                        <a href="{{ route('admin.packages.edit',$p->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
