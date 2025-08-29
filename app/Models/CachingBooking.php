<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CachingBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'user_id', 'date', 'time', 'status', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(CoachingBooking::class, 'booking_id');
    }
}
