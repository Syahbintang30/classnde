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
    Route::get('lessons/{lesson}/topics/create', [TopicController::class, 'create'])->name('topics.create');
    Route::post('lessons/{lesson}/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get('lessons/{lesson}/topics/{topic}/edit', [TopicController::class, 'edit'])->name('topics.edit');
    Route::put('lessons/{lesson}/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('lessons/{lesson}/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
});


