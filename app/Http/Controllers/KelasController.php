<?php

namespace App\Http\Controllers;

use App\Models\Lesson;

class KelasController extends Controller
{
    public function index()
    {
        $lessons = Lesson::get();
        return view('kelas', compact('lessons'));
    }
}
