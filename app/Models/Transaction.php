<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','user_id','package_id','method','amount','status','midtrans_response','original_amount','referral_code','referrer_user_id'];

    /**
     * Cast json/amount fields to native types so arrays are stored correctly
     */
    protected $casts = [
        'midtrans_response' => 'array',
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
    ];


    // relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lesson::class);
    }

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class);
    }

}

