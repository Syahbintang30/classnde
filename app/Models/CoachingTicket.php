<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachingTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_used',
        'source',
    'referrer_user_id',
    'used_at',
    'used_by_admin_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(CoachingBooking::class, 'ticket_id');
    }

    public function referrer()
    {
        return $this->belongsTo(\App\Models\User::class, 'referrer_user_id');
    }

    public function usedByAdmin()
    {
        return $this->belongsTo(\App\Models\User::class, 'used_by_admin_id');
    }
}
