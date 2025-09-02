<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Super Admin account
        User::firstOrCreate(
            ['email' => 'super@admin'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('superadminpass'),
                'is_admin' => true,
                'is_superadmin' => true,
            ]
        );

        // Admin account
        User::firstOrCreate(
            ['email' => 'admin@admin'],
            [
                'name' => 'Admin',
                'password' => bcrypt('adminpass'),
                'is_admin' => true,
                'is_superadmin' => false,
            ]
        );
    }
}
