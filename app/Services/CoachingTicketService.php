<?php

namespace App\Services;

use App\Models\CoachingTicket;
use App\Models\Package;
use App\Models\User;

class CoachingTicketService
{
    /**
     * Grant free coaching tickets on register based on user's package.
     * - beginner => 1 ticket
     * - intermediate or highest eligible course package => 2 tickets
     * Idempotent: tops up to the desired count for source 'free_on_register'.
     * Returns number of tickets created.
     */
    public static function grantFreeOnRegister(User $user): int
    {
        $desired = 0; // default: no tickets if user has no package

        // Determine desired count from package slug if available
        $pkgSlug = null;
        if (! empty($user->package_id)) {
            $pkg = Package::find($user->package_id);
            $pkgSlug = $pkg?->slug;

            // Compute highest eligible slug by price (fallback to last configured if price ties/missing)
            $eligible = config('coaching.eligible_packages', ['beginner','intermediate']);
            $eligiblePkgs = Package::whereIn('slug', $eligible)->get()->keyBy('slug');
            $highestSlug = null;
            if ($eligiblePkgs->isNotEmpty()) {
                $sorted = $eligiblePkgs->sortByDesc(function ($p) { return (int) ($p->price ?? 0); });
                $highestSlug = optional($sorted->first())->slug;
            } else {
                // fallback to last element in eligible list
                $highestSlug = end($eligible) ?: 'intermediate';
            }

            // Beginner explicit mapping -> 1 ticket
            if ($pkgSlug === 'beginner') {
                $desired = 1;
            }

            // Intermediate or highest package -> 2 tickets
            $intermediateSlug = DynamicConfigService::get('intermediate_package_slug', 'intermediate');
            if ($pkgSlug && ($pkgSlug === $intermediateSlug || $pkgSlug === $highestSlug)) {
                $desired = 2;
            }
        }

        // Idempotent top-up for source 'free_on_register'
        $existing = CoachingTicket::where('user_id', $user->id)
            ->where('source', 'free_on_register')
            ->count();

        $toCreate = max(0, $desired - $existing);
        for ($i = 0; $i < $toCreate; $i++) {
            CoachingTicket::create([
                'user_id' => $user->id,
                'is_used' => false,
                'source' => 'free_on_register',
            ]);
        }

        return $toCreate;
    }
}
