<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\Official;
use App\Models\Resident;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommitteeOfficialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SEEDING COMMITEE DATA
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

        foreach ($committees as $committee) {
            Committee::firstOrCreate([
                'committee_name' => $committee,
            ]);
        }

        // SEEDING OFFICIAL DATA
        $committees = Committee::all();
        $residents = Resident::limit(10)->get();
        $roles = Role::all();
        $i = 0;

        foreach ($residents as $resident) {
            Official::firstOrCreate([
                'official_number' =>
                    'BO' . '-' .
                    str_pad(fake()->unique()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT) . '-' .
                    str_pad(fake()->unique()->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT),
                'resident_id' => $resident->id,
                'role_id' => $roles[$i % $roles->count()]->id,
                'committee_id' => $committees[$i % $committees->count()]->id,
                'term_start' => '2025-11-30',
                'term_end' => '2028-11-30',
            ]);
            $i++;
        }

        $officials = Official::limit(count($committees))->get();
        $i = 0;

        foreach ($committees as $committee) {
            $committee->update([
                'chairperson_id' => $officials[$i++]->id,
            ]);
        }
    }
}
