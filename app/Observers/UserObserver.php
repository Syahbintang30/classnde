<?php

namespace App\Observers;

use App\Models\User;
use App\Models\CoachingTicket;

class UserObserver
{
    public function created(User $user)
    {
        // Ensure user has a referral code
        if (empty($user->referral_code)) {
            $user->referral_code = $this->generateReferralCode($user->id, $user->email);
            // avoid touching timestamps
            $user->saveQuietly();
        }

        // Ensure the new user has at least one free ticket (existing behaviour)
        if (! CoachingTicket::where('user_id', $user->id)->exists()) {
            CoachingTicket::create([
                'user_id' => $user->id,
                'is_used' => false,
                'source' => 'free_on_register',
            ]);
        }

        // If this user was referred by another user, give the referrer one free ticket
        if (! empty($user->referred_by)) {
            $referrerId = $user->referred_by;
            // Avoid giving duplicate referral ticket for the same referred user
            if (! CoachingTicket::where('user_id', $referrerId)
                ->where('source', 'referral')
                ->where('referrer_user_id', $user->id)
                ->exists()) {
                CoachingTicket::create([
                    'user_id' => $referrerId,
                    'is_used' => false,
                    'source' => 'referral',
                    // store the referred user's id so we can avoid duplicates
                    'referrer_user_id' => $user->id,
                ]);
            }
        }
    }

    protected function generateReferralCode($id, $email)
    {
        // simple deterministic but hard-to-guess code: base36 of id + 4 chars of email hash
        $prefix = base_convert((string) max(1, (int) $id), 10, 36);
        $hash = substr(md5($email . time()), 0, 4);
        return strtoupper($prefix . $hash);
    }
}
