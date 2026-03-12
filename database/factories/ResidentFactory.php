<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake('fil_PH')->firstName(),
            'last_name' => fake('fil_PH')->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'contact_number' => '09' . $this->faker->numerify('#########'),
            'birthdate' => fake()->date(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'civil_status' => $this->faker->randomElement(['Single', 'Married', 'Widowed', 'Separated', 'Divorced']),
            'voter_status'=> $this->faker->randomElement(['Registered', 'Unregistered', 'Suspended']),
            'residency_status' => $this->faker->randomElement(['Active', 'Deceased', 'Transferred']),
            'household_id' => 1,
        ];
    }
}
