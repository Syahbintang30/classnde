@extends('layouts.app')

@section('content')
    <div style="max-width:1200px;margin:24px auto;padding:16px;">
        <h1>Coaching Feedbacks</h1>
        <table style="width:100%;border-collapse:collapse;margin-top:12px;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid rgba(0,0,0,0.06);">
                    <th style="padding:8px">ID</th>
                    <th style="padding:8px">User</th>
                    <th style="padding:8px">Want to Learn</th>
                    <th style="padding:8px">Keluh-kesah</th>
                    <th style="padding:8px">Admin Action</th>
                    <th style="padding:8px">When</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $it)
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.03);">
                        <td style="padding:8px;vertical-align:top">{{ $it->id }}</td>
                        <td style="padding:8px;vertical-align:top">{{ $it->user ? $it->user->email . ' (' . $it->user->name . ')' : 'Guest' }}</td>
                        <td style="padding:8px;vertical-align:top">{{ $it->want_to_learn }}</td>
                        <td style="padding:8px;vertical-align:top">{{ $it->keluh_kesah }}</td>
                        <td style="padding:8px;vertical-align:top">
                            <form method="POST" action="{{ route('admin.coaching.feedback.update', $it->id) }}">
                                @csrf
                                @method('PUT')
                                <textarea name="admin_action" style="width:320px;min-height:64px">{{ $it->admin_action }}</textarea>
                                <div><button class="btn" type="submit">Save</button></div>
                            </form>
                        </td>
                        <td style="padding:8px;vertical-align:top">{{ $it->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px">{{ $items->links() }}</div>
    </div>
@endsection
