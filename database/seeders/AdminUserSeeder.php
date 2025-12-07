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
        // Cek apakah admin sudah ada
        $adminExists = User::where('email', 'admin@bingkiskaca.com')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists!');
            return;
        }

        // Buat admin user
        User::create([
            'name' => 'Admin Bingkis Kaca',
            'email' => 'admin@bingkiskaca.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'is_blocked' => false,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Admin user created successfully!');
        $this->command->line('');
        $this->command->line('Login Credentials:');
        $this->command->line('Email: admin@bingkiskaca.com');
        $this->command->line('Password: admin123');
    }
}
