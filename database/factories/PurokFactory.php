<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purok>
 */
class PurokFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purok_name' => 'Purok ' . $this->faker->unique()->numberBetween(1, 99),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
