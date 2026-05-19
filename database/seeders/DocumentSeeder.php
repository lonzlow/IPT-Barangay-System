<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Resident;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create document templates first
        $templates = [
            [
                'name' => 'Barangay Clearance',
                'description' => 'Certificate of good moral character and residency.',
                'template_html' => '<h2>Barangay Clearance</h2><p>This certifies that [RESIDENT_NAME] is a resident of good moral character.</p>',
                'fields_required' => ['resident_name', 'purpose', 'date_issued'],
                'validity_days' => 365,
                'is_active' => true,
            ],
            [
                'name' => 'Residency Certificate',
                'description' => 'Proof of residency in the barangay.',
                'template_html' => '<h2>Residency Certificate</h2><p>This certifies that [RESIDENT_NAME] is a bonafide resident of the barangay.</p>',
                'fields_required' => ['resident_name', 'address', 'date_issued'],
                'validity_days' => 730,
                'is_active' => true,
            ],
            [
                'name' => 'Indigency Certificate',
                'description' => 'Certificate for indigent/low-income residents.',
                'template_html' => '<h2>Indigency Certificate</h2><p>This certifies that [RESIDENT_NAME] is an indigent resident of this barangay.</p>',
                'fields_required' => ['resident_name', 'purpose', 'date_issued'],
                'validity_days' => 365,
                'is_active' => true,
            ],
            [
                'name' => 'Good Moral Certificate',
                'description' => 'Certificate of good moral character.',
                'template_html' => '<h2>Good Moral Character Certificate</h2><p>This certifies that [RESIDENT_NAME] has a good moral character.</p>',
                'fields_required' => ['resident_name', 'institution', 'date_issued'],
                'validity_days' => 365,
                'is_active' => true,
            ],
            [
                'name' => 'Business Clearance',
                'description' => 'Clearance for business registration.',
                'template_html' => '<h2>Business Clearance</h2><p>This certifies that [RESIDENT_NAME] is cleared to register a business in this barangay.</p>',
                'fields_required' => ['resident_name', 'business_name', 'date_issued'],
                'validity_days' => 180,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            DocumentTemplate::firstOrCreate(
                ['name' => $templateData['name']],
                $templateData
            );
        }

        // Get active residents
        $residents = Resident::where('residency_status', 'Active')->get();

        if ($residents->isEmpty()) {
            $this->command->warn('No active residents found. Creating 50 residents first...');
            $residents = Resident::factory(50)->create();
        }

        // Create documents for each resident
        $purposes = [
            'Employment',
            'Scholarship',
            'Travel',
            'Business Registration',
            'Loan Application',
            'Government Benefits',
            'Education',
            'Medical Assistance',
            'OFW Application',
            'Barangay Transaction',
        ];

        $issuedBy = [
            'Hon. Sample Captain',
            'Barangay Secretary',
            'Barangay Treasurer',
            'Kagawad Juan',
            'Kagawad Maria',
        ];

        // Create 100 documents with mixed statuses
        $this->command->info('Creating 100 documents...');
        
        $counter = 1;
        $year = now()->year;
        $month = now()->month;

        // 70% Active documents
        $this->command->info('Creating 70 active documents...');
        for ($i = 0; $i < 70; $i++) {
            Document::factory()
                ->active()
                ->create([
                    'resident_id' => $residents->random()->id,
                    'document_template_id' => DocumentTemplate::inRandomOrder()->first()->id,
                    'reference_number' => 'BRG-' . $year . sprintf('%02d', $month) . '-' . sprintf('%05d', $counter++),
                    'purpose' => collect($purposes)->random(),
                    'issued_by' => collect($issuedBy)->random(),
                ]);
        }

        // 20% Expired documents
        $this->command->info('Creating 20 expired documents...');
        for ($i = 0; $i < 20; $i++) {
            Document::factory()
                ->expired()
                ->create([
                    'resident_id' => $residents->random()->id,
                    'document_template_id' => DocumentTemplate::inRandomOrder()->first()->id,
                    'reference_number' => 'BRG-' . $year . sprintf('%02d', $month) . '-' . sprintf('%05d', $counter++),
                    'purpose' => collect($purposes)->random(),
                    'issued_by' => collect($issuedBy)->random(),
                ]);
        }

        // 10% Revoked documents
        $this->command->info('Creating 10 revoked documents...');
        for ($i = 0; $i < 10; $i++) {
            Document::factory()
                ->revoked()
                ->create([
                    'resident_id' => $residents->random()->id,
                    'document_template_id' => DocumentTemplate::inRandomOrder()->first()->id,
                    'reference_number' => 'BRG-' . $year . sprintf('%02d', $month) . '-' . sprintf('%05d', $counter++),
                    'purpose' => collect($purposes)->random(),
                    'issued_by' => collect($issuedBy)->random(),
                ]);
        }

        $this->command->info('Documents seeded successfully!');
    }
}
