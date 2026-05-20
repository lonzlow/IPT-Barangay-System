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
        $this->call(CertificateTemplateSeeder::class);

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
