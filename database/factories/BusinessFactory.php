<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_name' => fake()->unique()->randomElement([
                'Super67', 'Ohio', 'Umbrella', 'McDollibee', 'Unlad', 'TungTungSahur', '6/7'
            ]),
            'business_type' => fake()->unique()->randomElement([
                'Supermarket', 'Laundry Service', 'Pharmacy', 'Restaurant', 'Retail Service', 'Sari-Sari'
            ]),
            'business_address' => fake()->unique()->address(),
            'date_established' => fake()->date(),
        ];
    }
}
