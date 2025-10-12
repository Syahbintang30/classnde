<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'units', // each unit represents 25%
        'order_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
