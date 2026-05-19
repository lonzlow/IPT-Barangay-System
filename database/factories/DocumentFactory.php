<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resident = Resident::factory();
        $template = DocumentTemplate::factory();

        return [
            'resident_id' => $resident,
            'document_template_id' => $template,
            'reference_number' => 'BRG-' . now()->format('Ym') . '-' . str_pad($this->faker->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'purpose' => $this->faker->randomElement([
                'Employment',
                'Scholarship',
                'Travel',
                'Business Registration',
                'Loan Application',
                'Government Benefits',
                'Education',
                'Medical Assistance',
            ]),
            'rendered_html' => '<p>Document rendered HTML here</p>',
            'issued_by' => $this->faker->randomElement([
                'Hon. Sample Captain',
                'Barangay Secretary',
                'Barangay Treasurer',
            ]),
            'issued_date' => $this->faker->dateTimeBetween('-6 months'),
            'valid_until' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['Issued', 'Revoked', 'Expired']),
            'seal_path' => null,
            'signature_path' => null,
        ];
    }

    /**
     * State: Active documents only
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Issued',
                'valid_until' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            ];
        });
    }

    /**
     * State: Expired documents
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Expired',
                'valid_until' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            ];
        });
    }

    /**
     * State: Revoked documents
     */
    public function revoked()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'Revoked',
            ];
        });
    }
}
