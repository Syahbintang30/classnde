<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
    $this->call(\Database\Seeders\AdminUserSeeder::class);
    $this->call(\Database\Seeders\LessonTopicSeeder::class);
    $this->call(\Database\Seeders\SongTutorialSeeder::class);
    $this->call(\Database\Seeders\CoachingTicketSeeder::class);
    $this->call(\Database\Seeders\PackageSeeder::class);
    $this->call(\Database\Seeders\UpgradeIntermediatePackageSeeder::class);
    }
}
