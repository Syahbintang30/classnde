<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\CoachingTicket;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    // Show form to edit referral-related settings
    public function settingsForm()
    {
        $discount = Setting::get('referral.discount_percent', null);
        if ($discount === null) $discount = config('referral.discount_percent', 2);
        $autoGrantTicket = Setting::get('referral.auto_grant_ticket', '1');
        return view('admin.referral.settings', compact('discount','autoGrantTicket'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'discount_percent' => 'required|integer|min:0|max:100',
            'auto_grant_ticket' => 'nullable|in:0,1',
        ]);

        Setting::set('referral.discount_percent', (int)$data['discount_percent']);
        Setting::set('referral.auto_grant_ticket', $data['auto_grant_ticket'] ?? '0');

    return redirect()->route('admin.referral.settings.form')->with('success', 'Referral settings updated');
    }

    // Show leaderboard of users who referred the most new users
    public function leaderboard(Request $request)
    {
        // count referred users grouped by referrer
        $rows = User::selectRaw('referred_by, count(*) as referrals')
            ->whereNotNull('referred_by')
            ->groupBy('referred_by')
            ->orderByDesc('referrals')
            ->get();

        $users = User::whereIn('id', $rows->pluck('referred_by')->filter()->unique()->toArray())->get()->keyBy('id');

        return view('admin.referral.leaderboard', compact('rows','users'));
    }

    // Show users who were referred by a specific user (admin view)
    public function referredUsers(Request $request, User $referrer)
    {
        $users = User::where('referred_by', $referrer->id)
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.referral.users', compact('users','referrer'));
    }

    // Admin view showing each user package and their remaining coaching tickets
    public function userPackages(Request $request)
    {
        $users = User::orderBy('name')
            ->withCount(['coachingTickets as available_tickets_count' => function($q){ $q->where('is_used', false); }])
            ->withCount(['coachingTickets as total_tickets_count'])
            ->paginate(50);

                // only role packages (Beginner / Intermediate) are considered the user's class role
                // include the upgrade-intermediate slug so upgrade users count as Intermediate
                $rolePackages = Package::where(function($q){
                        $q->whereIn('name', ['Beginner', 'Intermediate'])
                            ->orWhere('slug', 'upgrade-intermediate');
                })->get()->keyBy('id');
        return view('admin.users.packages', compact('users','rolePackages'));
    }

    // Show edit form for a single user (package, ticket count)
    public function editUser(User $user)
    {
                // only allow role packages (include upgrade-intermediate as Intermediate)
                $packages = Package::where(function($q){
                        $q->whereIn('name', ['Beginner', 'Intermediate'])
                            ->orWhere('slug', 'upgrade-intermediate');
                })->get()->keyBy('id');
        // count current unused tickets and total tickets
        $available = CoachingTicket::where('user_id', $user->id)->where('is_used', false)->count();
        $total = CoachingTicket::where('user_id', $user->id)->count();
        return view('admin.users.edit', compact('user','packages','available','total'));
    }

    // Update user's package and ticket count (admin)
    public function updateUser(Request $request, User $user)
    {

                // restrict allowed package ids to Beginner/Intermediate (treat upgrade-intermediate as Intermediate)
                $allowed = Package::where(function($q){
                        $q->whereIn('name', ['Beginner', 'Intermediate'])
                            ->orWhere('slug', 'upgrade-intermediate');
                })->pluck('id')->toArray();
        $data = $request->validate([
            'package_id' => ['nullable', 'integer', function($attr, $value, $fail) use ($allowed) {
                if ($value !== null && ! in_array((int)$value, $allowed)) $fail('Selected package is not allowed.');
            }],
            'available_tickets' => 'required|integer|min:0|max:1000',
        ]);

        // Update package
        $user->package_id = $data['package_id'] ?? null;
        $user->save();

        // Adjust coaching tickets to match requested available_tickets
        $desired = (int)$data['available_tickets'];
        $current = CoachingTicket::where('user_id', $user->id)->where('is_used', false)->count();

        if ($desired > $current) {
            // create new tickets
            $toCreate = $desired - $current;
            for ($i = 0; $i < $toCreate; $i++) {
                CoachingTicket::create(['user_id' => $user->id, 'is_used' => false]);
            }
        } elseif ($desired < $current) {
            // mark the newest unused tickets as used for audit-safety instead of deleting
            $tos = CoachingTicket::where('user_id', $user->id)->where('is_used', false)->orderByDesc('id')->take($current - $desired)->get();
            foreach ($tos as $t) {
                $t->is_used = true;
                $t->used_at = now();
                // record which admin performed the action if available
                $t->used_by_admin_id = Auth::id();
                $t->save();
            }
        }

        return redirect()->route('admin.users.packages', ['user_id' => $user->id])->with('success', 'User updated');
    }
}
