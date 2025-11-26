<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Passport personal access client for users
        if (\Laravel\Passport\Client::where('name', 'Personal Access Client')->doesntExist()) {
            \Laravel\Passport\Client::create([
                'id' => '019a6d75-e746-7029-b19a-d4073664c9ab',
                'name' => 'Personal Access Client',
                'secret' => 'i6mUy73Cl5CreiDLWpKEiIOhk64A5kWMv3UhjkTd',
                'redirect_uris' => 'http://localhost',
                'grant_types' => 'personal_access',
                'revoked' => false,
            ]);
        }

        $this->call([
            AdminSeeder::class,
            ServicePartenaireSeeder::class,
            ClientSeeder::class,
            CompteSeeder::class,
            AgentSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
