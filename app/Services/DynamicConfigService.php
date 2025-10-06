<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class DynamicConfigService
{
    /**
     * Get configuration value with database override
     */
    public static function get(string $key, $default = null)
    {
        // First try to get from database settings
        $dbValue = AppSetting::get($key);
        
        if ($dbValue !== null) {
            return $dbValue;
        }

        // Fallback to config file
        $configKey = self::convertToConfigKey($key);
        $configValue = config($configKey, $default);
        
        if ($configValue !== null) {
            return $configValue;
        }

        return $default;
    }

    /**
     * Get validation configuration
     */
    public static function getValidation(): array
    {
        return [
            'name_max_length' => self::get('validation_name_max_length', 255),
            'email_max_length' => self::get('validation_email_max_length', 255),
            'filename_max_length' => self::get('validation_filename_max_length', 100),
            'photo_max_size_kb' => self::get('validation_photo_max_size_kb', 5120),
        ];
    }

    /**
     * Get security configuration
     */
    public static function getSecurity(): array
    {
        return [
            'session_timeout_minutes' => self::get('security_session_timeout_minutes', 30),
            'max_concurrent_sessions' => self::get('security_max_concurrent_sessions', 3),
            'password_min_length' => self::get('security_password_min_length', 8),
        ];
    }

    /**
     * Get rate limiting configuration
     */
    public static function getRateLimiting(): array
    {
        return [
            'api_requests' => self::get('rate_limit_api_requests', 100),
            'auth_requests' => self::get('rate_limit_auth_requests', 10),
            'auth_window_minutes' => self::get('rate_limit_auth_window', 15),
            'window_minutes' => self::get('rate_limit_window_minutes', 60),
        ];
    }

    /**
     * Get business logic configuration
     */
    public static function getBusinessLogic(): array
    {
        return [
            'intermediate_package_slug' => self::get('intermediate_package_slug', 'intermediate'),
            'free_coaching_ticket_count' => self::get('free_coaching_ticket_count', 1),
            'bunny_url_expiry_seconds' => self::get('bunny_url_expiry_seconds', 3600),
        ];
    }

    /**
     * Get file upload configuration
     */
    public static function getFileUpload(): array
    {
        return [
            'image_max_size_bytes' => self::get('upload_image_max_size', 5242880),
            'video_max_size_bytes' => self::get('upload_video_max_size', 524288000),
            'document_max_size_bytes' => self::get('upload_document_max_size', 10485760),
        ];
    }

    /**
     * Get API endpoints configuration
     */
    public static function getApiEndpoints(): array
    {
        return [
            'bunny_video_base' => self::get('bunny_video_base_url', 'https://video.bunnycdn.com'),
            'midtrans_base' => self::get('midtrans_base_url', 'https://app.midtrans.com'),
            'midtrans_sandbox' => self::get('midtrans_sandbox_url', 'https://app.sandbox.midtrans.com'),
        ];
    }

    /**
     * Convert database setting key to config key
     */
    private static function convertToConfigKey(string $key): string
    {
        // Convert validation_name_max_length to constants.validation.name_max_length
        $parts = explode('_', $key);
        if (count($parts) >= 2) {
            $category = $parts[0];
            $setting = implode('_', array_slice($parts, 1));
            return "constants.{$category}.{$setting}";
        }
        
        return "constants.{$key}";
    }

    /**
     * Set a configuration value in database
     */
    public static function set(string $key, $value, array $options = []): bool
    {
        return AppSetting::set($key, $value, $options);
    }

    /**
     * Clear configuration cache
     */
    public static function clearCache(): void
    {
        AppSetting::clearCache();
        
        // Also clear Laravel config cache if exists
        if (file_exists(storage_path('framework/cache/config.php'))) {
            @unlink(storage_path('framework/cache/config.php'));
        }
    }

    /**
     * Get all public settings for frontend
     */
    public static function getPublicSettings(): array
    {
        return AppSetting::getPublicSettings();
    }
}