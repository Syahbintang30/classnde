<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run()
    {
        Package::updateOrCreate(['slug' => 'beginner'], [
            'name' => 'Beginner',
            'price' => 75000,
        ]);

        Package::updateOrCreate(['slug' => 'intermediate'], [
            'name' => 'Intermediate',
            'price' => 125000,
        ]);
    }
}
