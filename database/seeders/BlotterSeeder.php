<?php

namespace Database\Seeders;

use App\Models\Blotter;
use App\Models\BlotterEvidence;
use App\Models\BlotterRespondent;
use App\Models\BlotterWitness;
use App\Models\Official;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlotterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $residents = Resident::limit(10)->get();
        $officials = Official::limit(5)->get();
        $users     = User::limit(3)->get();

        // Create 3 blotter cases
        for ($i = 1; $i <= 3; $i++) {
            $blotter = Blotter::create([
                'case_number' => 'BL-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'complainant_id' => $residents->random()->id,
                'complainant_name' => null, // leave blank if resident
                'location' => 'Zone ' . $i . ', Barangay Sample',
                'incident_description' => 'Incident description for case ' . $i,
                'incident_date' => now()->subDays($i * 2),
                'handled_by' => $officials->random()->id,
                'status' => ['open','ongoing','resolved','referred','dismissed'][array_rand(['open','ongoing','resolved','referred','dismissed'])],
                'filed_by' => $users->random()->id,
            ]);

            // Add multiple respondents
            foreach ($residents->random(2) as $respondent) {
                BlotterRespondent::create([
                    'blotter_id' => $blotter->id,
                    'respondent_id' => $respondent->id,
                    'respondent_name' => null,
                    'role' => 'Primary Offender',
                ]);
            }
            // Add one outsider respondent
            BlotterRespondent::create([
                'blotter_id' => $blotter->id,
                'respondent_id' => null,
                'respondent_name' => 'John Doe', // Not a resident
                'role' => 'Accomplice',
            ]);

            // Add witnesses
            foreach ($residents->random(2) as $witness) {
                BlotterWitness::create([
                    'blotter_id' => $blotter->id,
                    'witness_id' => $witness->id,
                ]);
            }

            // Add evidences
            /* BlotterEvidence::create([
                'blotter_id' => $blotter->id,
                'file_path' => 'uploads/evidence/case'.$i.'_photo.jpg',
                'file_extension' => 'jpg',
                'mime_type' => 'image/jpeg',
                'file_category' => 'image',
                'caption' => 'Photo evidence for case '.$i,
            ]);

            BlotterEvidence::create([
                'blotter_id' => $blotter->id,
                'file_path' => 'uploads/evidence/case'.$i.'_video.mp4',
                'file_extension' => 'mp4',
                'mime_type' => 'video/mp4',
                'file_category' => 'video',
                'caption' => 'Video evidence for case '.$i,
            ]); */
        }
    }
}
