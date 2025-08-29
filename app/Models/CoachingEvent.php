<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'event',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(CoachingBooking::class, 'booking_id');
    }
}
