<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Allow filtering by status (pending|settlement) or order_id (q)
        $status = $request->query('status');
        $search = $request->query('q');
    $from = $request->query('from');
    $to = $request->query('to');

        $q = Transaction::with(['user','package'])->orderBy('created_at','desc');

        if ($status) {
            if ($status === 'pending') {
                $q = $q->whereNotIn('status', ['settlement','capture','success']);
            } elseif ($status === 'settlement') {
                $q = $q->whereIn('status', ['settlement','capture','success']);
            }
        }

        if ($search) {
            $q = $q->where('order_id', 'like', '%'.$search.'%');
        }

        // date range filter: support from only, to only, or both
        if ($from || $to) {
            try {
                if ($from && $to) {
                    $fromDt = Carbon::parse($from)->startOfDay();
                    $toDt = Carbon::parse($to)->endOfDay();
                    $q = $q->whereBetween('created_at', [$fromDt, $toDt]);
                } elseif ($from) {
                    $fromDt = Carbon::parse($from)->startOfDay();
                    $q = $q->where('created_at', '>=', $fromDt);
                } elseif ($to) {
                    $toDt = Carbon::parse($to)->endOfDay();
                    $q = $q->where('created_at', '<=', $toDt);
                }
            } catch (\Throwable $e) {
                // ignore invalid date formats and fall back to unfiltered
            }
        }

    $txns = $q->paginate(30)->appends($request->query());

    return view('admin.transactions.index', compact('txns', 'status', 'search'));
    }
}
