<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index()
    {
    $lessons = Lesson::orderBy('position')->paginate(10);
        return view('admin.lessons.index', compact('lessons'));
    }

    public function create()
    {
        return view('admin.lessons.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'position' => 'nullable|integer',
        ]);

        Lesson::create($request->only(['title', 'position']));

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson berhasil ditambahkan.');
    }

    public function show(Lesson $lesson)
    {
        $topics = $lesson->topics()->paginate(10);
        return view('admin.lessons.show', compact('lesson', 'topics'));
    }

    public function edit(Lesson $lesson)
    {
        return view('admin.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'position' => 'nullable|integer',
        ]);

        $lesson->update($request->only(['title', 'position']));

        return redirect()->route('admin.lessons.index')->with('success', 'Lesson berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return redirect()->route('admin.lessons.index')->with('success', 'Lesson berhasil dihapus.');
    }
}
