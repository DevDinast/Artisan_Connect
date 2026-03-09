<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ ADMIN
        User::create([
            'name' => 'Admin',
            'email' => 'admin@artisanconnect.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2️⃣ ARTISANS
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'Artisan'.$i,
                'email' => 'artisan'.$i.'@mail.com',
                'password' => Hash::make('password'),
                'role' => 'artisan',
            ]);
        }

        // 3️⃣ ACHETEURS
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'Acheteur'.$i,
                'email' => 'acheteur'.$i.'@mail.com',
                'password' => Hash::make('password'),
                'role' => 'acheteur',
            ]);
        }
    }
}
