<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'username' => 'testuser',
            'password' => Hash::make('password'),
            'lastname' => 'Testov',
            'firstname' => 'Test',
            'patronymic' => null,
            'email' => 'test@example.com',
            'phone' => null,
            'group_id' => 1,
            'role' => 'user',
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
