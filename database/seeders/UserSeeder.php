<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'name'     => 'Administrator',
            'email'    => 'admin@danymart.com',
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'username' => 'kasir1',
            'name'     => 'Siti Rahayu',
            'email'    => 'kasir1@danymart.com',
            'role'     => 'user',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'username' => 'kasir2',
            'name'     => 'Budi Santoso',
            'email'    => 'kasir2@danymart.com',
            'role'     => 'user',
            'password' => Hash::make('password'),
        ]);
    }
}