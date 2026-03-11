<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_name' => $this->faker->randomElement([
                'Admin', 'Punong Barangay', 'Secretary', 'Treasurer', 'Kagawad',
                'SK Chair', 'Tanod', 'BHW', 'BDRRM Coordinator', 'Encoder',
                'Auditor', 'Guest',
            ]),
        ];
    }
}
