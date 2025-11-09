<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Admin User 1',
                'email' => 'admin1@ompay.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Admin User 2',
                'email' => 'admin2@ompay.com',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($admins as $adminData) {
            $user = \App\Models\User::create(array_merge($adminData, ['id' => Str::uuid()]));
            \App\Models\Admin::create(['id' => Str::uuid(), 'user_id' => $user->id]);
        }
    }
}
