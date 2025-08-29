<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'booking_time',
        'status',
        'twilio_room_sid',
    'session_number',
    'notes',
    'admin_note',
    ];

    protected $dates = ['booking_time'];
    
    protected $casts = [
        'booking_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(CoachingTicket::class, 'ticket_id');
    }

    public function coach()
    {
        return $this->belongsTo(\App\Models\User::class, 'coach_user_id');
    }


}
