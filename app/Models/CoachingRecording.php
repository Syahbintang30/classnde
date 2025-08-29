<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_sid',
        'recording_sid',
        'status',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
