<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'title', 'video_url'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
