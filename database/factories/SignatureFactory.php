<?php

namespace Database\Factories;

use App\Models\Signature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Signature>
 */
class SignatureFactory extends Factory
{
    protected $model = Signature::class;

    public function definition(): array
    {
        return [
            'official_id' => \App\Models\Official::query()->value('id'),
            'label' => fake()->optional()->word(),
            'path' => 'signatures/'.fake()->uuid().'.png',
            'uploaded_by' => null,
        ];
    }
}
