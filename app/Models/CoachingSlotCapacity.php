<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingSlotCapacity extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
    'time',
    'capacity',
    ];

    protected $casts = [
        'date' => 'date',
    'capacity' => 'integer',
    ];
}
