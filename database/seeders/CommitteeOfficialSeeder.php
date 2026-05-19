<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\Official;
use App\Models\Resident;
use App\Models\Role;
use App\Models\OfficialAssignment;
use Illuminate\Database\Seeder;

class CommitteeOfficialSeeder extends Seeder
{
    public function run(): void
    {
        // SEEDING COMMITTEE DATA
        $committees = [
            'Peace and Order',
            'Health',
            'Education',
            'Infrastructure',
            'Environment',
            'Livelihood',
            'Transport and Communication',
            'Barangay Disaster Risk Reduction and Management',
        ];

        foreach ($committees as $committeeName) {
            Committee::firstOrCreate([
                'name' => $committeeName,
            ]);
        }

        // SEEDING OFFICIAL DATA
        $committees = Committee::all();
        $residents = Resident::limit(10)->get();
        $roles = Role::all();
        $i = 0;

        foreach ($residents as $resident) {
            $official = Official::firstOrCreate([
                'official_number' =>
                    'BO-' .
                    str_pad(fake()->unique()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT) . '-' .
                    str_pad(fake()->unique()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT),
                'resident_id' => $resident->id,
                'role_id' => $roles[$i % $roles->count()]->id,
                'term_start' => '2025-11-30',
                'term_end' => '2028-11-30',
                'is_active' => true,
            ]);

            // Assign each official to a committee
            $committee = $committees[$i % $committees->count()];
            OfficialAssignment::firstOrCreate([
                'official_id' => $official->id,
                'committee_id' => $committee->id,
                'designation' => 'Member',
            ]);

            $i++;
        }

        // Assign chairpersons (first N officials as heads of committees)
        $officials = Official::limit($committees->count())->get();
        foreach ($committees as $index => $committee) {
            OfficialAssignment::updateOrCreate(
                [
                    'official_id' => $officials[$index]->id,
                    'committee_id' => $committee->id,
                ],
                [
                    'designation' => 'Chairperson',
                ]
            );
        }
    }
}
