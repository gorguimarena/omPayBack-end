<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = \App\Models\Client::all();

        if ($clients->isNotEmpty()) {
            \App\Models\Compte::create([
                'id' => Str::uuid(),
                'client_id' => $clients->first()->id,
                'telephone' => '+221776525959',
                'pin' => '1234', 
            ]);
        }

        foreach ($clients as $client) {
            \App\Models\Compte::create([
                'id' => Str::uuid(),
                'client_id' => $client->id,
                'telephone' => '+221' . rand(700000000, 799999999),
                'pin' => '1234', 
            ]);
        }
    }
}
