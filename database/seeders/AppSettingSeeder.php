<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Validation Settings
            [
                'key' => 'validation_name_max_length',
                'value' => '255',
                'type' => 'integer',
                'category' => 'validation',
                'description' => 'Maximum length for name fields',
                'is_public' => true,
            ],
            [
                'key' => 'validation_email_max_length',
                'value' => '255',
                'type' => 'integer',
                'category' => 'validation',
                'description' => 'Maximum length for email fields',
                'is_public' => true,
            ],
            [
                'key' => 'validation_filename_max_length',
                'value' => '100',
                'type' => 'integer',
                'category' => 'validation',
                'description' => 'Maximum length for filename fields',
                'is_public' => false,
            ],
            [
                'key' => 'validation_photo_max_size_kb',
                'value' => '5120',
                'type' => 'integer',
                'category' => 'validation',
                'description' => 'Maximum photo upload size in KB (5MB)',
                'is_public' => true,
            ],

            // Security Settings
            [
                'key' => 'security_session_timeout_minutes',
                'value' => '30',
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Session timeout in minutes',
                'is_public' => false,
                'requires_restart' => true,
            ],
            [
                'key' => 'security_max_concurrent_sessions',
                'value' => '3',
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Maximum concurrent sessions per user',
                'is_public' => false,
                'requires_restart' => true,
            ],
            [
                'key' => 'security_password_min_length',
                'value' => '8',
                'type' => 'integer',
                'category' => 'security',
                'description' => 'Minimum password length',
                'is_public' => true,
            ],

            // Rate Limiting Settings
            [
                'key' => 'rate_limit_api_requests',
                'value' => '100',
                'type' => 'integer',
                'category' => 'rate_limiting',
                'description' => 'API requests per hour',
                'is_public' => false,
                'requires_restart' => true,
            ],
            [
                'key' => 'rate_limit_auth_requests',
                'value' => '10',
                'type' => 'integer',
                'category' => 'rate_limiting',
                'description' => 'Auth requests per window',
                'is_public' => false,
                'requires_restart' => true,
            ],

            // Business Logic Settings
            [
                'key' => 'intermediate_package_slug',
                'value' => 'intermediate',
                'type' => 'string',
                'category' => 'business_logic',
                'description' => 'Slug for intermediate package',
                'is_public' => true,
            ],
            [
                'key' => 'free_coaching_ticket_count',
                'value' => '1',
                'type' => 'integer',
                'category' => 'business_logic',
                'description' => 'Number of free coaching tickets',
                'is_public' => true,
            ],
            [
                'key' => 'bunny_url_expiry_seconds',
                'value' => '3600',
                'type' => 'integer',
                'category' => 'business_logic',
                'description' => 'Bunny URL expiry time in seconds',
                'is_public' => false,
            ],

            // File Upload Settings
            [
                'key' => 'upload_image_max_size',
                'value' => '5242880',
                'type' => 'integer',
                'category' => 'file_upload',
                'description' => 'Maximum image upload size in bytes (5MB)',
                'is_public' => true,
            ],
            [
                'key' => 'upload_video_max_size',
                'value' => '524288000',
                'type' => 'integer',
                'category' => 'file_upload',
                'description' => 'Maximum video upload size in bytes (500MB)',
                'is_public' => true,
            ],

            // API Endpoints
            [
                'key' => 'bunny_video_base_url',
                'value' => 'https://video.bunnycdn.com',
                'type' => 'string',
                'category' => 'api_endpoints',
                'description' => 'Bunny CDN video base URL',
                'is_public' => false,
                'requires_restart' => true,
            ],
            [
                'key' => 'midtrans_base_url',
                'value' => 'https://app.midtrans.com',
                'type' => 'string',
                'category' => 'api_endpoints',
                'description' => 'Midtrans production base URL',
                'is_public' => false,
                'requires_restart' => true,
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
