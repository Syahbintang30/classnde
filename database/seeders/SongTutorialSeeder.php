<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Topic;

class SongTutorialSeeder extends Seeder
{
    public function run()
    {
        // Only create default lesson in development environment
        if (!app()->environment('production')) {
            // create a sample song lesson if not exists
            $lesson = Lesson::firstOrCreate(
                ['title' => 'Barasuara - Terbuang Dalam Waktu', 'type' => 'song'],
                ['position' => 999]
            );

            // create a sample topic (video) if none exist
            if ($lesson->topics()->count() === 0) {
                Topic::create([
                    'lesson_id' => $lesson->id,
                    'title' => 'Part 1 - Intro & Verse',
                    // bunny_guid is optional; using a youtube fallback URL in description or bunny_guid
                    'bunny_guid' => null,
                    'description' => 'Demo section for development. Replace with actual content in production.',
                    'position' => 1,
                ]);
            }
        }
    }
}
