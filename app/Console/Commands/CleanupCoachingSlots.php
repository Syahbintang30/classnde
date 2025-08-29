<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\CoachingSlotCapacity;
use App\Models\CoachingBooking;
use Illuminate\Support\Facades\DB;

class CleanupCoachingSlots extends Command
{
    protected $signature = 'coaching:cleanup-slots {--prune-unbooked : Also remove unbooked future slots older than N days} {--days=30 : Age in days for pruning unbooked slots}';

    protected $description = 'Remove coaching slot records that are past or optionally prune unbooked old future slots';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $this->info("Cleaning coaching_slot_capacities for dates before {$today}...");

        $deletedPast = CoachingSlotCapacity::whereDate('date', '<', $today)->delete();
        $this->info("Deleted {$deletedPast} past slot records.");

        if ($this->option('prune-unbooked')) {
            $days = (int) $this->option('days');
            $cutoff = Carbon::now()->subDays($days);
            $this->info("Pruning unbooked slots older than {$days} days (created_at <= {$cutoff})...");

            // find distinct dates for slots created before cutoff
            $dates = CoachingSlotCapacity::where('created_at', '<=', $cutoff)
                ->groupBy('date')
                ->pluck('date')
                ->toArray();

            $prunedTotal = 0;
            foreach ($dates as $d) {
                // skip if there is any booking on that date
                $hasBooking = CoachingBooking::whereDate('booking_time', $d)->exists();
                if (! $hasBooking) {
                    $count = CoachingSlotCapacity::whereDate('date', $d)->delete();
                    $prunedTotal += $count;
                }
            }

            $this->info("Pruned {$prunedTotal} unbooked old slot records.");
        }

        return 0;
    }
}
