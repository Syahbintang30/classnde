<?php

namespace App\Services;

use App\Models\ReferralRedemption;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Package;

class ReferralService
{
    /**
     * Returns discount percent for a buyer using someone else's referral code for course packages.
     * Requirement: new user using a friend's referral gets 5% discount on beginner or intermediate.
     */
    public static function guestCourseDiscountPercent(?string $referralCode, ?Package $package): int
    {
        if (! $referralCode || ! $package) return 0;
        $eligibleSlugs = config('coaching.eligible_packages', ['beginner','intermediate']);
        if (! in_array($package->slug, $eligibleSlugs, true)) return 0;
        $refUser = User::where('referral_code', $referralCode)->first();
        if (! $refUser) return 0;
        // fixed 5%
        return 5;
    }

    /**
     * Calculate how many 25% units the referrer has available, based on number of users registered with their code
     * minus already redeemed units.
     */
    public static function availableCoachingUnits(User $referrer): int
    {
        // count referred users (users whose referred_by = referrer.id)
        $referredCount = User::where('referred_by', $referrer->id)->count();
        $redeemed = ReferralRedemption::where('user_id', $referrer->id)->sum('units');
        $available = max(0, $referredCount - (int) $redeemed);
        return $available; // each unit = 25%
    }

    /**
     * Compute discount percent for referrer when buying a coaching ticket. 25% per unit, up to 100%.
     */
    public static function referrerCoachingDiscountPercent(User $referrer): int
    {
        $units = self::availableCoachingUnits($referrer);
        return max(0, min(100, $units * 25));
    }

    /**
     * Redeem units on successful purchase (coaching ticket only). This will record a redemption entry so future
     * purchases see reduced available units.
     */
    public static function redeemUnits(User $referrer, int $percentApplied, string $orderId = null): void
    {
        $units = (int) ceil($percentApplied / 25);
        if ($units <= 0) return;
        ReferralRedemption::create([
            'user_id' => $referrer->id,
            'units' => $units,
            'order_id' => $orderId,
        ]);
    }
}
