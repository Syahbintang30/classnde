<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value', 
        'type',
        'category',
        'description',
        'is_public',
        'requires_restart',
        'validation_rules',
        'updated_by'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'requires_restart' => 'boolean',
        'validation_rules' => 'array'
    ];

    /**
     * Cache key prefix for settings
     */
    const CACHE_PREFIX = 'app_setting:';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a setting value with caching
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, array $options = []): bool
    {
        try {
            $setting = self::updateOrCreate(
                ['key' => $key],
                array_merge([
                    'value' => $value,
                    'type' => $options['type'] ?? self::inferType($value),
                    'category' => $options['category'] ?? 'general',
                    'description' => $options['description'] ?? null,
                    'is_public' => $options['is_public'] ?? false,
                    'requires_restart' => $options['requires_restart'] ?? false,
                    'validation_rules' => $options['validation_rules'] ?? null,
                    'updated_by' => Auth::id() ?? 'system'
                ])
            );

            // Clear cache
            Cache::forget(self::CACHE_PREFIX . $key);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update app setting', [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cast value to appropriate type
     */
    private static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Infer type from value
     */
    private static function inferType($value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer', 
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}
