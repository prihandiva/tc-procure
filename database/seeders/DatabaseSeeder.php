<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Users with simple usernames requested
        User::updateOrCreate(
            ['email' => 'admin@tcprocure.test'],
            [
                'username' => 'superadmin',
                'name' => 'Super Administrator',
                'role' => \App\Enums\UserRole::SUPERADMIN,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'spv@tcprocure.test'],
            [
                'username' => 'tc',
                'name' => 'Supervisor Training Center',
                'role' => \App\Enums\UserRole::SPV,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'vendor@tcprocure.test'],
            [
                'username' => 'vendor',
                'name' => 'Admin Yulia Catering',
                'role' => \App\Enums\UserRole::VENDOR,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->call(MasterDataSeeder::class);
    }
}
