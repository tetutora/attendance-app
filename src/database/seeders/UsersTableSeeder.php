<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => '一般ユーザー１',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => '一般ユーザー２',
            'email' => 'user2@example.com',
            'password' => Hash::make('password321'),
        ]);

    }
}
