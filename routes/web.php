<?php

use App\Http\Controllers\admin\LessonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\Admin\TopicController;

Route::get('/', function () {
    return view('compro'); // atau ganti view sesuai yang ada
})->name('compro');

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('topics', TopicController::class);
// });


Route::get('/kelas', [KelasController::class, 'index'])->name('kelas');
Route::get('/kelas/{lesson}', [KelasController::class, 'show'])->name('kelas.show');
Route::get('/kelas/{lesson}/content', [KelasController::class, 'content'])->name('kelas.content');

// Route::get('/admin', [LessonController::class, 'index'])->name('admin.lessons.index');
// Route::get('/admin/create', [LessonController::class, 'create'])->name('admin.lessons.create');
// Route::post('admin/storedata', [LessonController::class, 'store'])->name('admin.lessons.store');
// Route::get('/admin/edit', [LessonController::class, 'edit'])->name('admin.lessons.edit');
// Route::delete('/admin/destroy/{lesson}', [LessonController::class, 'destroy'])->name('admin.lessons.destroy');


// // Admin Lesson
// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('lessons', LessonController::class);
//     Route::resource('topics', TopicController::class);
// });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function(){
        return redirect(route('admin.lessons.index'));
    })->name('dashboard');
    Route::resource('lessons', LessonController::class);
    // Route to create a signed upload URL for direct-to-Bunny uploads
    // Temporarily use a closure to avoid calling controller dispatch while debugging
    Route::post('bunny/upload-url', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'success' => false,
            'message' => 'Direct browser upload endpoint temporarily disabled. Use the server upload endpoint at admin/bunny/upload-server.',
            'upload_url' => null,
        ], 501);
    })->name('bunny.upload-url');
    Route::post('bunny/upload-server', [App\Http\Controllers\BunnyController::class, 'uploadToBunny'])->name('bunny.upload-server');
    Route::get('bunny/video-status/{guid}', [App\Http\Controllers\BunnyController::class, 'videoStatus'])->name('bunny.video-status');
    Route::get('lessons/{lesson}/topics/create', [TopicController::class, 'create'])->name('topics.create');
    Route::post('lessons/{lesson}/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get('lessons/{lesson}/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
    Route::put('lessons/{lesson}/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('lessons/{lesson}/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
});

// Topic progress endpoints (authenticated users)
// Topic progress endpoints removed — progress feature disabled and storage removed.

// Return stream URL for a topic (used by frontend to load private Bunny streams)
use App\Http\Controllers\BunnyController;

Route::get('/topics/{topic}/stream', function (App\Models\Topic $topic) {
    // Prefer bunny_guid if present
    if ($topic->bunny_guid) {
        $signed = BunnyController::signUrl($topic->bunny_guid, 300);
        if ($signed) return response()->json(['url' => $signed]);
        return response()->json(['url' => BunnyController::cdnUrl($topic->bunny_guid)]);
    }

    // Fallback to legacy video_url for compatibility
    $path = $topic->video_url ?? null;
    if (! $path) return response()->json(['url' => null]);
    if (preg_match('#^https?://#i', $path)) return response()->json(['url' => $path]);
    $signed = BunnyController::signUrl($path, 300);
    if ($signed) return response()->json(['url' => $signed]);
    return response()->json(['url' => BunnyController::cdnUrl($path)]);
})->name('topics.stream');


