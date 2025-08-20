<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'position'];

    public function topics()
    {
        return $this->hasMany(Topic::class)->orderBy('position');
    }
}

