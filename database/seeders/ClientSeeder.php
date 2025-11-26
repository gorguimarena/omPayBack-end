<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Client User 1',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Client User 2',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Client User 3',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($clients as $clientData) {
            $user = \App\Models\User::create(array_merge([
                'name' => $clientData['name'],
                'password' => $clientData['password'],
            ], ['id' => Str::uuid()]));
            \App\Models\Client::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
            ]);
        }
    }
}
