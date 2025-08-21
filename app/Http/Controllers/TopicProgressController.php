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
    // Progress tracking disabled: accept request but do not store anything.
    // Return 204 No Content to indicate request processed.
    return response()->noContent();
    }

    public function show(Topic $topic)
    {
    // Progress tracking disabled: always return no progress
    return response()->json(['progress' => null]);
    }
}
