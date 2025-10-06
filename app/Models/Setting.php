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

    /**
     * Get intermediate package ID from settings with fallback
     */
    public static function getIntermediatePackageId()
    {
        return (int) static::get('intermediate_package_id', 2);
    }

    /**
     * Get allowed intermediate package slugs from settings
     */
    public static function getIntermediatePackageSlugs()
    {
        $defaultSlug = config('constants.business_logic.intermediate_package_slug');
        $slugs = static::get('intermediate_package_slugs', "{$defaultSlug},upgrade-{$defaultSlug}");
        return array_map('trim', explode(',', $slugs));
    }
}
