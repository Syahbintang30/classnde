<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachingFeedback extends Model
{
    protected $table = 'coaching_feedbacks';
    protected $fillable = ['user_id', 'booking_id', 'keluh_kesah', 'want_to_learn'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(\App\Models\CoachingBooking::class, 'booking_id');
    }
}
