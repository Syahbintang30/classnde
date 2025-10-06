<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with('user')->orderByDesc('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->get('action').'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $audits = $query->paginate(50)->appends($request->query());
        // Only include admin and superadmin users in the dropdown filter
        $users = User::where(function($q){
                $q->where('is_admin', true)->orWhere('is_superadmin', true);
            })
            ->orderBy('name')
            ->pluck('name','id');

        return view('admin.audit.index', compact('audits','users'));
    }
}
