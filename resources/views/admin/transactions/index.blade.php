@extends('layouts.admin')

@section('title', 'Transactions')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4">Transactions</h1>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div style="padding:12px;border-bottom:1px solid #f1f1f1;display:flex;gap:12px;align-items:center;">
                        <form id="txn-filter-form" method="GET" action="{{ route('admin.transactions.index') }}" style="display:flex;gap:8px;align-items:center;">
                            <select name="status" class="form-select form-select-sm" style="width:160px">
                                <option value="">All statuses</option>
                                <option value="pending" {{ (isset($status) && $status==='pending') ? 'selected' : '' }}>Pending</option>
                                <option value="settlement" {{ (isset($status) && $status==='settlement') ? 'selected' : '' }}>Settlement</option>
                            </select>

                            <div style="display:flex;gap:6px;align-items:center">
                                <div class="input-group input-group-sm" style="width:150px">
                                    <span class="input-group-text" title="From"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="date" name="from" id="filter-from" value="{{ request()->get('from') }}" class="form-control form-control-sm" title="From date" />
                                </div>
                                <div class="input-group input-group-sm" style="width:150px">
                                    <span class="input-group-text" title="To"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
                                    <input type="date" name="to" id="filter-to" value="{{ request()->get('to') }}" class="form-control form-control-sm" title="To date" />
                                </div>
                            </div>

                            <div style="display:flex;gap:6px;align-items:center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-preset="today">Hari ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-preset="week">Minggu ini</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-preset="month">Bulan ini</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="preset-clear">Clear</button>
                            </div>

                            <input name="q" value="{{ $search ?? '' }}" placeholder="Order ID" class="form-control form-control-sm" style="width:220px" />
                            <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="light-custom-table table-sm mb-0">
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
                                <tr>
                                    <td>{{ $loop->iteration + ($txns->currentPage()-1)*$txns->perPage() }}</td>
                                    <td>{{ $txn->order_id }}</td>
                                    <td>{{ optional($txn->user)->name ?? 'Guest' }}</td>
                                    <td>{{ optional($txn->package)->name ?? '-' }}</td>
                                    <td>{{ $txn->method }}</td>
                                    <td>{{ number_format($txn->amount,0,',','.') }}</td>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    function formatDate(d){
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const dd = String(d.getDate()).padStart(2,'0');
        return d.getFullYear() + '-' + mm + '-' + dd;
    }

    document.querySelectorAll('#txn-filter-form [data-preset]').forEach(btn=>{
        btn.addEventListener('click', function(){
            const preset = this.getAttribute('data-preset');
            const today = new Date();
            let from=null, to=null;
            if (preset === 'today'){
                from = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                to = new Date(from);
            } else if (preset === 'week'){
                const day = today.getDay();
                const diff = (day === 0) ? -6 : (1 - day);
                from = new Date(today);
                from.setDate(today.getDate() + diff);
                to = new Date(from);
                to.setDate(from.getDate() + 6);
            } else if (preset === 'month'){
                from = new Date(today.getFullYear(), today.getMonth(), 1);
                to = new Date(today.getFullYear(), today.getMonth()+1, 0);
            }

            if (from && to){
                document.getElementById('filter-from').value = formatDate(from);
                document.getElementById('filter-to').value = formatDate(to);
                document.getElementById('txn-filter-form').submit();
            }
        });
    });

    const presetClear = document.getElementById('preset-clear');
    if (presetClear){
        presetClear.addEventListener('click', function(){
            document.getElementById('filter-from').value = '';
            document.getElementById('filter-to').value = '';
            document.getElementById('txn-filter-form').submit();
        });
    }
});
</script>
@endsection
