@extends('layouts.admin')

@section('title', 'Transactions')

@section('content') 
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4 header">
            <h2>Transactions</h2>
            <form method="GET" class="d-flex align-items-center">
                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="settlement" {{ request('status')=='settlement' ? 'selected' : '' }}>Settlement</option>
                    <option value="expire" {{ request('status')=='expire' ? 'selected' : '' }}>Expired</option>
                    <option value="cancel" {{ request('status')=='cancel' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </form>
        </div>
    </div>
    <div class="card-table">
        <table class="custom-table">
            <thead>
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
                <tr style="pointer-events: none; background: transparent;">
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
        {{ $txns->links('pagination::bootstrap-5') }}
    </div>
    {{-- 
    <div class="mt-3">
        {{ $txns->links() }}
    </div> --}}
@endsection
