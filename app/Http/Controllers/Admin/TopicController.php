<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BunnyController;
use App\Models\Lesson;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
            'bunny_guid' => 'nullable|string',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'bunny_guid', 'description', 'position']);
    // If admin provided a Bunny GUID, keep it; playback URL will be derived at read-time.
    // Do NOT write a `video_url` column because it may have been removed.

        $lesson->topics()->create($data);

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
            'bunny_guid' => 'nullable|string',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'bunny_guid', 'description', 'position']);
    // Do not write video_url; frontend will resolve playback URL from bunny_guid.
    $topic->update($data);

        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroy(Lesson $lesson, Topic $topic)
    {
        $topic->delete();
        return redirect()->route('admin.lessons.show', $lesson->id)->with('success', 'Topik berhasil dihapus.');
    }

    // ...existing code...
}
