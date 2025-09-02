<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpgradeIntermediatePackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
}
