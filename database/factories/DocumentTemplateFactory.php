<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $templates = [
            [
                'name' => 'Barangay Clearance',
                'description' => 'Certificate of good moral character and residency.',
                'validity_days' => 365,
            ],
            [
                'name' => 'Residency Certificate',
                'description' => 'Proof of residency in the barangay.',
                'validity_days' => 730,
            ],
            [
                'name' => 'Indigency Certificate',
                'description' => 'Certificate for indigent/low-income residents.',
                'validity_days' => 365,
            ],
            [
                'name' => 'Good Moral Certificate',
                'description' => 'Certificate of good moral character.',
                'validity_days' => 365,
            ],
            [
                'name' => 'Business Clearance',
                'description' => 'Clearance for business registration.',
                'validity_days' => 180,
            ],
        ];

        $template = $this->faker->randomElement($templates);

        return [
            'name' => $template['name'],
            'description' => $template['description'],
            'template_html' => '<p>Sample template for ' . $template['name'] . '</p>',
            'fields_required' => ['resident_name', 'purpose', 'date_issued'],
            'validity_days' => $template['validity_days'],
            'is_active' => true,
        ];
    }
}
