<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;

class LessonTopicSeeder extends Seeder
{
    public function run(): void
    {
        // A small guitar course curriculum
        $courses = [
            [
                'title' => 'Fundamentals',
                'headline' => 'Getting to Know Your Guitar',
                'sub_headline' => 'Basics to get started',
                'youtube_link' => 'https://youtu.be/dQw4w9WgXcQ',
                'description' => 'Introduction and foundation',
            ],
            [
                'title' => 'Confidence',
                'headline' => 'Playing With Confidence',
                'sub_headline' => 'Build stage presence',
                'youtube_link' => 'https://youtu.be/dQw4w9WgXcQ',
                'description' => 'Techniques to play confidently',
            ],
            [
                'title' => 'Express',
                'headline' => 'Express Yourself',
                'sub_headline' => 'Dynamics and phrasing',
                'youtube_link' => 'https://youtu.be/dQw4w9WgXcQ',
                'description' => 'Make music sound alive',
            ],
        ];

        $pos = 1;
        foreach ($courses as $course) {
            // lessons now only contain title and position; full content lives in topics
            $lesson = Lesson::create([
                'title' => $course['title'],
                'position' => $pos++,
            ]);

            // create topics for each lesson
            $topicSamples = [
                ['Know Your Guitar', 'Overview of the instrument', 'https://youtu.be/dQw4w9WgXcQ'],
                ['Tuning', 'How to tune your guitar', 'https://youtu.be/dQw4w9WgXcQ'],
                ['Finger Exercises', 'Warm up exercises', 'https://youtu.be/dQw4w9WgXcQ'],
                ['Basic Chord', 'Open chord shapes', 'https://youtu.be/dQw4w9WgXcQ'],
                ['Strumming Patterns', 'Common strumming patterns', 'https://youtu.be/dQw4w9WgXcQ'],
            ];

            $tpos = 1;
            foreach ($topicSamples as $t) {
                $lesson->topics()->create([
                    'title' => $t[0],
                    'description' => $t[1],
                    'video_url' => $t[2],
                    'position' => $tpos++,
                ]);
            }
        }
    }
}
