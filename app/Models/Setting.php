<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key','value'];

    public static function get($key, $default = null)
    {
        $s = static::where('key', $key)->first();
        if (! $s) return $default;
        return $s->value;
    }

    public static function set($key, $value)
    {
        $s = static::updateOrCreate(['key' => $key], ['value' => $value]);
        return $s;
    }
}
