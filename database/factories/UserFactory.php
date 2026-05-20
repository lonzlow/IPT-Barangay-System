<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'official_id' => fn () => $this->officialId(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'Inactive',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function officialId(): string
    {
        $role = Role::firstOrCreate(
            ['role_name' => 'Admin'],
            ['description' => 'System administrator']
        );

        $purok = Purok::firstOrCreate(
            ['purok_name' => 'Purok 1'],
            ['description' => 'Default test purok']
        );

        $household = Household::forceCreate([
            'purok_id' => $purok->id,
            'house_number' => fake()->numerify('###'),
            'street' => fake()->streetName(),
            'family_size' => fake()->numberBetween(1, 8),
        ]);

        $resident = Resident::factory()->create([
            'household_id' => $household->id,
            'residency_status' => 'Active',
        ]);

        return Official::create([
            'official_number' => 'OFF-' . fake()->unique()->numerify('######'),
            'resident_id' => $resident->id,
            'role_id' => $role->id,
            'term_start' => now()->subYear()->toDateString(),
            'term_end' => now()->addYear()->toDateString(),
            'is_active' => true,
        ])->id;
    }
}
