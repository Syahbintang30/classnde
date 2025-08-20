<?php

namespace App\Http\Controllers;

use App\Models\Lesson;

class KelasController extends Controller
{
    public function index()
    {
    $lessons = Lesson::with('topics')->orderBy('position')->get();
        return view('kelas', compact('lessons'));
    }

    public function show(Lesson $lesson)
    {
        // load topics ordered by position
        $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
        // also provide list of all lessons for sidebar navigation
        $lessons = Lesson::orderBy('position')->get();
        return view('kelas', compact('lessons', 'lesson'));
    }

    /**
     * Return the lesson main content as a partial (AJAX)
     */
    public function content(Lesson $lesson)
    {
        $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
        return view('kelas._lesson_content', compact('lesson'));
    }
}
