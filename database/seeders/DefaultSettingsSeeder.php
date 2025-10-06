<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Set default intermediate package configuration
        Setting::set('intermediate_package_id', '2');
        Setting::set('intermediate_package_slugs', 'intermediate,upgrade-intermediate');
        
        // Add other default configurations
        Setting::set('coaching.max_booking_days_ahead', '30');
        Setting::set('coaching.session_duration_minutes', '60');
        Setting::set('coaching.buffer_minutes_before', '10');
        Setting::set('coaching.buffer_minutes_after', '60');
        
        // Default notification settings
        Setting::set('notifications.admin_booking_enabled', 'true');
        Setting::set('notifications.user_booking_status_enabled', 'true');
        
        $this->command->info('Default settings seeded successfully.');
    }
}