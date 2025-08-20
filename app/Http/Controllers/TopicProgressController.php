<?php

namespace App\Http\Controllers;

use App\Models\TopicProgress;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicProgressController extends Controller
{
    public function store(Request $request, Topic $topic)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $data = $request->validate([
            'watched_seconds' => 'required|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        $progress = TopicProgress::updateOrCreate(
            ['user_id' => $user->id, 'topic_id' => $topic->id],
            ['watched_seconds' => $data['watched_seconds'], 'completed' => $data['completed'] ?? false]
        );

        return response()->json(['status' => 'ok', 'progress' => $progress]);
    }

    public function show(Topic $topic)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $progress = TopicProgress::where('user_id', $user->id)->where('topic_id', $topic->id)->first();
        return response()->json(['progress' => $progress]);
    }
}
