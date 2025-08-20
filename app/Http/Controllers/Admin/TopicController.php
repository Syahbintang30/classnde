<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function create(Lesson $lesson)
    {
        return view('admin.topics.create', compact('lesson'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $lesson->topics()->create($request->only(['title', 'video_url', 'description', 'position']));

        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Topik berhasil ditambahkan.');
    }

    public function edit(Lesson $lesson, Topic $topic)
    {
        return view('admin.topics.edit', compact('lesson', 'topic'));
    }

    public function update(Request $request, Lesson $lesson, Topic $topic)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $topic->update($request->only(['title', 'video_url', 'description', 'position']));

        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson, Topic $topic)
    {
        $topic->delete();
        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Topik berhasil dihapus.');
    }
}
