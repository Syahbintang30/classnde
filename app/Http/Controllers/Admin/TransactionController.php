<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
    $q = Transaction::with(['user','package'])->orderBy('created_at','desc');
        if ($request->filled('status')) $q->where('status', $request->input('status'));
        $txns = $q->paginate(30);
        return view('admin.transactions.index', compact('txns'));
    }
}
