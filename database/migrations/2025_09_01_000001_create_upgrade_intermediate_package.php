<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpgradeIntermediatePackage extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration will insert a seed row for upgrade-intermediate package if not exists.
        if (! Schema::hasTable('packages')) return;

        $beginner = \App\Models\Package::where('slug','beginner')->first();
        $intermediate = \App\Models\Package::where('slug','intermediate')->first();
        if ($beginner && $intermediate) {
            $diff = max(0, intval($intermediate->price) - intval($beginner->price));
            if ($diff > 0) {
                \App\Models\Package::updateOrCreate(
                    ['slug' => 'upgrade-intermediate'],
                    [
                        'name' => 'Upgrade Intermediate',
                        'price' => $diff,
                        'description' => 'Upgrade from Beginner to Intermediate — bayar selisih harga saja.',
                        'benefits' => 'Upgrade fee to move from Beginner to Intermediate.',
                        'image' => null,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('packages')) return;
        \App\Models\Package::where('slug','upgrade-intermediate')->delete();
    }
}
