<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function referralForm()
    {
        $value = Setting::get('referral.discount_percent', null);
        if ($value === null) $value = config('referral.discount_percent', 2);
        // compute metrics
        $totalReferrals = \App\Models\User::whereNotNull('referred_by')->count();
        $totalReferralTickets = \App\Models\CoachingTicket::where('source','referral')->count();
        // total discount given across transactions where referral_code is set
        $totalDiscount = \App\Models\Transaction::whereNotNull('referral_code')->whereNotNull('original_amount')->whereColumn('original_amount','>','amount')->get()->reduce(function($carry,$t){
            return $carry + (($t->original_amount ?: 0) - ($t->amount ?: 0));
        },0);

        return view('admin.referral_settings', ['discount' => (int) $value, 'metrics' => [
            'total_referrals' => $totalReferrals,
            'total_referral_tickets' => $totalReferralTickets,
            'total_discount' => (int) $totalDiscount,
        ]]);
    }

    public function referralSave(Request $request)
    {
        $data = $request->validate([
            'discount_percent' => ['required','numeric','min:0','max:100'],
        ]);
        Setting::set('referral.discount_percent', (string) $data['discount_percent']);
    return redirect()->route('admin.referral.settings.form')->with('success','Referral settings updated.');
    }

    // Export referral transactions as CSV
    public function exportReferralCsv()
    {
        $this->middleware('can:admin');
        $rows = \App\Models\Transaction::whereNotNull('referral_code')->orderByDesc('id')->get();
        $filename = 'referral_transactions_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','order_id','user_id','referrer_user_id','referral_code','amount','original_amount','status','created_at']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->order_id,
                    $r->user_id,
                    $r->referrer_user_id,
                    $r->referral_code,
                    $r->amount,
                    $r->original_amount,
                    $r->status,
                    $r->created_at,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
