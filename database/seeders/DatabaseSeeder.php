<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'is_admin' => 1,
        ]);

    // seed lessons & topics
    $this->call(\Database\Seeders\LessonTopicSeeder::class);
    // seed song tutorial example
    $this->call(\Database\Seeders\SongTutorialSeeder::class);
    // seed coaching tickets
    $this->call(\Database\Seeders\CoachingTicketSeeder::class);
    // seed packages (beginner/intermediate)
    $this->call(\Database\Seeders\PackageSeeder::class);
    }
}
