@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="container" style="max-width:1100px;margin:0 auto;padding:16px;color:#fff;">
    <h2 style="margin:0 0 12px 0;">Audit Trail</h2>
    <p style="opacity:0.75;margin-bottom:16px">Lihat catatan aktivitas admin. Filter berdasarkan user, tanggal, atau action.</p>

    <form method="GET" action="{{ route('admin.audit.index') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:16px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.06);padding:12px;border-radius:8px;">
        <div>
            <label style="display:block;margin-bottom:6px">User</label>
            <select name="user_id" style="width:100%;padding:10px;border-radius:6px;background:#111;border:1px solid rgba(255,255,255,0.06);color:#fff">
                <option value="">-- All --</option>
                @foreach($users as $id => $name)
                    <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;margin-bottom:6px">Action</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="PACKAGE_CREATE, POST admin/packages, ..." style="width:100%;padding:10px;border-radius:6px;background:#111;border:1px solid rgba(255,255,255,0.06);color:#fff" />
        </div>
        <div>
            <label style="display:block;margin-bottom:6px">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width:100%;padding:10px;border-radius:6px;background:#111;border:1px solid rgba(255,255,255,0.06);color:#fff" />
        </div>
        <div>
            <label style="display:block;margin-bottom:6px">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width:100%;padding:10px;border-radius:6px;background:#111;border:1px solid rgba(255,255,255,0.06);color:#fff" />
        </div>
        <div style="grid-column: 1 / -1; text-align:right;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline" style="margin-left:8px;">Reset</a>
        </div>
    </form>

    <div style="overflow-x:auto;border:1px solid rgba(255,255,255,0.06);border-radius:8px;">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#0f0f0f">
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">Time</th>
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">User</th>
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">Action</th>
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">Entity</th>
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">IP</th>
                    <th style="text-align:left;padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($audits as $a)
                    <tr>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);white-space:nowrap">{{ $a->created_at->format('Y-m-d H:i:s') }}</td>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">{{ optional($a->user)->name }} <small style="opacity:0.6">(#{{ $a->user_id }})</small></td>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);"><code>{{ $a->action }}</code></td>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">
                            {{ class_basename($a->entity_type) }} @if($a->entity_id)#{{ $a->entity_id }}@endif
                        </td>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);">{{ $a->ip_address }}</td>
                        <td style="padding:10px;border-bottom:1px solid rgba(255,255,255,0.06);max-width:420px;">
                            @php $meta = $a->metadata ?? []; @endphp
                            @if($meta)
                                <div style="font-size:12px;opacity:0.9">
                                    @if(isset($meta['status']))<div>Status: {{ $meta['status'] }}</div>@endif
                                    @if(isset($meta['name']))<div>Name: {{ $meta['name'] }}</div>@endif
                                    @if(isset($meta['slug']))<div>Slug: {{ $meta['slug'] }}</div>@endif
                                    @if(isset($meta['price']))<div>Price: {{ $meta['price'] }}</div>@endif
                                    @if(isset($meta['payload_keys']))<div>Payload Keys: {{ implode(', ', $meta['payload_keys']) }}</div>@endif
                                    @if(isset($meta['query']) && $meta['query'])<div>Query: {{ http_build_query($meta['query']) }}</div>@endif
                                </div>
                            @else
                                <span style="opacity:0.7">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:14px;text-align:center;opacity:0.7">Tidak ada data audit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">{{ $audits->links() }}</div>
</div>
@endsection
