<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@wisatapadang.test'],
            [
                'name' => 'Admin Wisata Padang',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'user@wisatapadang.test'],
            [
                'name' => 'Pengguna Contoh',
                'password' => Hash::make('password'),
                'role' => Role::User,
                'email_verified_at' => now(),
            ],
        );
    }
}
