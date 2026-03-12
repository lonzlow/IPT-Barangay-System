<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Household>
 */
class HouseholdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purok_id' => 1,
            'house_number' => $this->faker->numerify(),
            'street' => $this->faker->unique()->randomElement([
                'Saint Joseph', 'Redeemer', 'Saint John', 'Libayan', 'Salvi',
                'Tyrone', 'Teodoro M. Kalaw',
            ]),
            'family_size' => fake()->numberBetween(1, 9),
        ];
    }
}
