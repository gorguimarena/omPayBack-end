<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => \Illuminate\Support\Str::uuid(),
            'telephone' => '+221' . $this->faker->numberBetween(700000000, 799999999),
            'adresse' => $this->faker->streetAddress(),
            'ville' => $this->faker->city(),
            'code_postal' => $this->faker->postcode(),
            'pays' => 'Sénégal',
            'statut' => $this->faker->randomElement(['actif', 'inactif', 'suspendu']),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
