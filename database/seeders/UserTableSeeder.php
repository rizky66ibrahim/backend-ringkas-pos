<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@ringkaspos.com',
                'phone_number' => '081234561890',
                'profile_picture' => 'admin.jpg',
                'status' => 'active',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'Kasir',
                'username' => 'kasir',
                'email' => 'kasir@ringkaspos.com',
                'phone_number' => '081234564891',
                'profile_picture' => 'kasir.jpg',
                'status' => 'active',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@ringkaspos.com',
                'phone_number' => '081234567821',
                'profile_picture' => 'user.jpg',
                'status' => 'active',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
