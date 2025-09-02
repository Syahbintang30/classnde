<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create default admin and superadmin accounts for local development (idempotent)

        // Create default admin and superadmin accounts (separate seeder)
        $this->call(\Database\Seeders\AdminUserSeeder::class);

    // seed lessons & topics
    $this->call(\Database\Seeders\LessonTopicSeeder::class);
    // seed song tutorial example
    $this->call(\Database\Seeders\SongTutorialSeeder::class);
    // seed coaching tickets
    $this->call(\Database\Seeders\CoachingTicketSeeder::class);
    // seed packages (beginner/intermediate)
    $this->call(\Database\Seeders\PackageSeeder::class);
    // seed upgrade-intermediate package if applicable
    $this->call(\Database\Seeders\UpgradeIntermediatePackageSeeder::class);
    }
}
