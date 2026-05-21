<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Indigency Certificate',
                'description' => 'Certificate stating that a resident is indigent and may need assistance.',
                'template_html' => $this->template('Indigency Certificate', [
                    'This is to certify that <strong>{{resident_name}}</strong>, of legal age, residing at <strong>{{household_address}}</strong>, is a bonafide resident of Barangay New Era.',
                    'Based on the assessment and records available to this office, the above-named resident is recognized as indigent and may be considered for financial, medical, educational, or social assistance.',
                    'This certificate is issued upon request for <strong>{{purpose}}</strong>.',
                    '<strong>Additional Notes:</strong><br>{{additional_notes}}',
                ]),
                'fields_required' => ['resident_name', 'household_address', 'purpose'],
                'validity_days' => 180,
            ],
            [
                'name' => 'Residency Certificate',
                'description' => 'Certificate certifying the residency status of a barangay resident.',
                'template_html' => $this->template('Residency Certificate', [
                    'This is to certify that <strong>{{resident_name}}</strong> is a bonafide resident of Barangay New Era and is currently recorded under <strong>{{household_address}}</strong>.',
                    'The resident has the following record details: contact number <strong>{{contact_number}}</strong>, voter status <strong>{{voter_status}}</strong>, and residency status <strong>{{residency_status}}</strong>.',
                    'This certificate is issued upon request for <strong>{{purpose}}</strong>.',
                    '<strong>Additional Notes:</strong><br>{{additional_notes}}',
                ]),
                'fields_required' => ['resident_name', 'household_address', 'purpose'],
                'validity_days' => 365,
            ],
            [
                'name' => 'Barangay Clearance',
                'description' => 'General clearance certificate for employment, travel, or other lawful purposes.',
                'template_html' => $this->template('Barangay Clearance', [
                    'This is to certify that <strong>{{resident_name}}</strong>, {{age}} years old, {{gender}}, residing at <strong>{{household_address}}</strong>, is a bonafide resident of Barangay New Era.',
                    'Based on available barangay records, the above-named resident has no derogatory record or pending barangay case on file as of this date.',
                    'This clearance is issued upon request for <strong>{{purpose}}</strong> and for whatever lawful purpose it may serve.',
                    '<strong>Additional Notes:</strong><br>{{additional_notes}}',
                ]),
                'fields_required' => ['resident_name', 'age', 'gender', 'household_address', 'purpose'],
                'validity_days' => 90,
            ],
            [
                'name' => 'Good Moral Character Certificate',
                'description' => 'Certificate attesting to the good moral character of a resident.',
                'template_html' => $this->template('Good Moral Character Certificate', [
                    'This is to certify that <strong>{{resident_name}}</strong>, {{age}} years old, {{civil_status}}, and residing at <strong>{{household_address}}</strong>, is known to this office as a resident of good moral character.',
                    'The above-named resident has maintained a good reputation in the community based on available barangay records.',
                    'This certificate is issued upon request for <strong>{{purpose}}</strong>.',
                    '<strong>Additional Notes:</strong><br>{{additional_notes}}',
                ]),
                'fields_required' => ['resident_name', 'age', 'civil_status', 'household_address', 'purpose'],
                'validity_days' => 180,
            ],
            [
                'name' => 'Business Clearance',
                'description' => 'Clearance for active businesses linked to resident business owners.',
                'template_html' => $this->template('Business Clearance', [
                    'This is to certify that <strong>{{business_name}}</strong>, a <strong>{{business_type}}</strong> business located at <strong>{{business_address}}</strong>, is linked to <strong>{{resident_name}}</strong> and has requested barangay clearance.',
                    'Based on available barangay records, the said business is active and has no outstanding barangay violation recorded as of this date.',
                    'This clearance is issued for <strong>{{purpose}}</strong> and is valid only for the business stated herein.',
                    '<strong>Additional Notes:</strong><br>{{additional_notes}}',
                ]),
                'fields_required' => ['resident_name', 'business_name', 'business_type', 'business_address', 'purpose'],
                'validity_days' => 365,
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(['name' => $template['name']], $template + ['is_active' => true]);
        }
    }

    private function template(string $title, array $paragraphs): string
    {
        $body = collect($paragraphs)->map(fn ($paragraph) => "<p>{$paragraph}</p>")->implode("\n");

        return <<<HTML
<div class="title">{$title}</div>
<p>TO WHOM IT MAY CONCERN:</p>
{$body}
<p>Issued this <strong>{{current_date}}</strong> at Barangay New Era.</p>
HTML;
    }
}
