<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SongTutorialController extends Controller
{
    public function index(Request $request)
    {
    // Show the song tutorial main viewer. If no song lessons exist, show the generic index.
    $lessons = \App\Models\Lesson::where('type', 'song')->with('topics')->orderBy('position')->get();
        if ($lessons->isEmpty()) {
            return view('song_tutorial.index', ['hasIntermediate' => false, 'userPackage' => null]);
        }
        // Redirect to first lesson viewer (same UX as /song-tutorial/{lesson})
        $first = $lessons->first();
        return redirect()->route('song.tutorial.show', ['lesson' => $first->id]);
    }

    /**
     * Public index landing page for Song Tutorial (used by navbar link).
     * This shows the generic index view which prompts login/purchase when no access.
     */
    public function indexLanding(Request $request)
    {
    $user = $request->user();
    $hasIntermediate = $this->userIsIntermediate($user);

    // Load song lessons and topics. For the index we will show topics directly.
    $lessons = \App\Models\Lesson::where('type', 'song')->with(['topics' => function($q){ $q->orderBy('position'); }])->orderBy('position')->get();
    $topics = \App\Models\Topic::whereHas('lesson', function($q){ $q->where('type','song'); })->with('lesson')->orderBy('position')->get();

    // enrich topics with thumbnail URL (prefer Bunny metadata when bunny_guid exists)
    $thumbCache = [];
    foreach ($topics as $topic) {
        $topic->thumb = null;
        if (! empty($topic->bunny_guid)) {
            $guid = $topic->bunny_guid;
            try {
                if (isset($thumbCache[$guid])) {
                    $topic->thumb = $thumbCache[$guid];
                } else {
                    $meta = \App\Http\Controllers\BunnyController::getVideoStatus($guid);
                    if ($meta && isset($meta['thumbnailFileName']) && $meta['thumbnailFileName']) {
                        $signed = \App\Http\Controllers\BunnyController::signThumbnailUrl($guid, 300, $meta['thumbnailFileName']);
                        $topic->thumb = $signed;
                        $thumbCache[$guid] = $signed;
                    } else {
                        // fallback to default signing path
                        $signed = \App\Http\Controllers\BunnyController::signThumbnailUrl($guid, 300);
                        $topic->thumb = $signed;
                        $thumbCache[$guid] = $signed;
                    }
                }
            } catch (\Exception $e) {
                // ignore and leave thumb null
            }
        }
    }

    // Render the generic index view but set hasIntermediate according to the user's package
    return view('song_tutorial.index', ['hasIntermediate' => $hasIntermediate, 'userPackage' => $user ? $user->package_id : null, 'lessons' => $lessons, 'topics' => $topics]);
    }

    /**
     * Show the song tutorial viewer (same as kelas.show but under /song-tutorial)
     */
    public function show(\App\Models\Lesson $lesson, Request $request)
    {
    // access control: only users with the intermediate package may view
    $user = $request->user();
    $hasIntermediate = $this->userIsIntermediate($user);

        if (! $hasIntermediate) {
            // show the CTA/index page prompting purchase or login
            return view('song_tutorial.index', compact('hasIntermediate'));
        }

        // load topics for this lesson and list of lessons for sidebar
    $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
    $lessons = \App\Models\Lesson::where('type', 'song')->orderBy('position')->get();
        return view('song_tutorial.show', compact('lessons', 'lesson'));
    }

    /**
     * Return the lesson main content as a partial (AJAX) - mirrors KelasController::content
     */
    public function content(\App\Models\Lesson $lesson)
    {
        $lesson->load(['topics' => function($q){ $q->orderBy('position'); }]);
        // reuse the same partial used by kelas
        return view('kelas._lesson_content', compact('lesson'));
    }

    /**
     * Return true if the given user should be considered 'intermediate'.
     * Accepts numeric package_id == 2 or falls back to Package slug lookup.
     */
    private function userIsIntermediate($user)
    {
        if (! $user) return false;
        // numeric id fallback historically used for intermediate
        if ($user->package_id && $user->package_id == 2) return true;
        // fallback: check package slug if present (intermediate or upgrade-intermediate)
        if ($user->package_id) {
            $pkg = \App\Models\Package::find($user->package_id);
            if ($pkg && isset($pkg->slug) && in_array($pkg->slug, ['intermediate','upgrade-intermediate'])) return true;
        }

        // Check historical purchases: user_packages might contain intermediate or upgrade-intermediate
        try {
            $exists = \App\Models\UserPackage::where('user_id', $user->id)
                ->whereHas('package', function($q){ $q->whereIn('slug', ['intermediate','upgrade-intermediate']); })
                ->exists();
            if ($exists) return true;
        } catch (\Throwable $e) {
            // ignore DB errors and default to false
        }

        return false;
    }
}
