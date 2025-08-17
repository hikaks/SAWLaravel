<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@saw.com'],
            [
                'name' => 'Administrator SAW',
                'email' => 'admin@saw.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create default manager user
        User::updateOrCreate(
            ['email' => 'manager@saw.com'],
            [
                'name' => 'Manager SAW',
                'email' => 'manager@saw.com',
                'password' => Hash::make('manager123'),
                'role' => 'manager',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create default regular user
        User::updateOrCreate(
            ['email' => 'user@saw.com'],
            [
                'name' => 'User SAW',
                'email' => 'user@saw.com',
                'password' => Hash::make('user123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully:');
        $this->command->info('Admin: admin@saw.com / admin123');
        $this->command->info('Manager: manager@saw.com / manager123');
        $this->command->info('User: user@saw.com / user123');
    }
}
