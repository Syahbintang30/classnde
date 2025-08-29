<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name', 'account_details', 'logo_url', 'is_active', 'midtrans_code', 'midtrans_bank'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
