@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">Transactions</h1>
                <form method="GET" class="d-flex align-items-center">
                    <label class="me-2">Status</label>
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="settlement" {{ request('status')=='settlement' ? 'selected' : '' }}>Settlement</option>
                        <option value="expire" {{ request('status')=='expire' ? 'selected' : '' }}>Expired</option>
                        <option value="cancel" {{ request('status')=='cancel' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($txns as $txn)
                                <tr>
                                    <td>{{ $loop->iteration + ($txns->currentPage()-1)*$txns->perPage() }}</td>
                                    <td>{{ $txn->order_id }}</td>
                                    <td>{{ optional($txn->user)->name ?? 'Guest' }}</td>
                                    <td>{{ optional($txn->package)->name ?? '-' }}</td>
                                    <td>{{ $txn->method }}</td>
                                    <td>Rp {{ number_format($txn->amount,0,',','.') }}</td>
                                    <td>{{ $txn->status }}</td>
                                    <td>{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $txns->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
