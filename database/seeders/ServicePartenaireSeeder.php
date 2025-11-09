<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServicePartenaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'nom' => 'Senelec',
                'api_url' => 'https://api.senelec.sn',
                'api_key' => 'senelec_api_key_' . rand(1000, 9999),
            ],
            [
                'nom' => 'Sen\'Eau',
                'api_url' => 'https://api.seneau.sn',
                'api_key' => 'seneau_api_key_' . rand(1000, 9999),
            ],
            [
                'nom' => 'Sonatel',
                'api_url' => 'https://api.sonatel.sn',
                'api_key' => 'sonatel_api_key_' . rand(1000, 9999),
            ],
        ];

        foreach ($services as $serviceData) {
            \App\Models\ServicePartenaire::create(array_merge($serviceData, ['id' => Str::uuid()]));
        }
    }
}
