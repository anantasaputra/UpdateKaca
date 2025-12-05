<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin Bingkis Kaca',
            'email' => 'admin@bingkiskaca.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_blocked' => false,
        ]);

        // Create Test Users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_blocked' => false,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_blocked' => false,
        ]);

        User::create([
            'name' => 'Alice Wonder',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_blocked' => false,
        ]);
    }
}