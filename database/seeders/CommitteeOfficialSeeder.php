<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeRecord;
use App\Models\Official;
use App\Models\OfficialAssignment;
use App\Models\Resident;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommitteeOfficialSeeder extends Seeder
{
    public function run(): void
    {
        $kagawadRole = Role::firstOrCreate(['role_name' => 'Kagawad']);
        $roles = Role::all()->values();
        $residents = Resident::limit(20)->get()->values();

        foreach ($residents->take(10) as $index => $resident) {
            Official::firstOrCreate(
                ['resident_id' => $resident->id],
                [
                    'official_number' => 'BO-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'role_id' => $roles[$index % max($roles->count(), 1)]->id,
                    'term_start' => '2025-11-30',
                    'term_end' => '2028-11-30',
                    'is_active' => true,
                ]
            );
        }

        $chairOfficials = $this->ensureKagawadChairPool($residents, $kagawadRole->id);

        foreach ($this->committees() as $committeeData) {
            $committee = Committee::updateOrCreate(
                ['name' => $committeeData['name']],
                [
                    'slug' => $committeeData['slug'],
                    'chair_label' => $committeeData['chair_label'],
                    'description' => $committeeData['description'],
                    'allowed_record_types' => $committeeData['record_types'],
                ]
            );

            $chairperson = $this->findNamedChairperson($chairOfficials, $committeeData['chair_terms'])
                ?? $chairOfficials->shift();

            if ($chairperson) {
                $committee->update(['chairperson_id' => $chairperson->id]);

                OfficialAssignment::updateOrCreate(
                    [
                        'official_id' => $chairperson->id,
                        'committee_id' => $committee->id,
                    ],
                    ['designation' => 'Chairperson']
                );
            }
        }
    }

    private function ensureKagawadChairPool($residents, int $roleId)
    {
        $officials = Official::query()
            ->with('resident')
            ->where('role_id', $roleId)
            ->where('is_active', true)
            ->get();

        $needed = max(0, 8 - $officials->count());

        foreach ($residents as $resident) {
            if ($needed === 0) {
                break;
            }

            $alreadyUsed = $officials->contains(fn (Official $official) => $official->resident_id === $resident->id);

            if ($alreadyUsed) {
                continue;
            }

            $officials->push(Official::create([
                'official_number' => 'KGD-CHAIR-' . Str::upper(Str::random(6)),
                'resident_id' => $resident->id,
                'role_id' => $roleId,
                'term_start' => '2025-11-30',
                'term_end' => '2028-11-30',
                'is_active' => true,
            ])->load('resident'));

            $needed--;
        }

        return $officials->values();
    }

    private function findNamedChairperson($officials, array $terms): ?Official
    {
        if ($terms === []) {
            return null;
        }

        $match = $officials->first(function (Official $official) use ($terms) {
            $name = Str::lower(trim(($official->resident?->first_name ?? '') . ' ' . ($official->resident?->last_name ?? '')));

            return collect($terms)->contains(fn (string $term) => str_contains($name, Str::lower($term)));
        });

        if (! $match) {
            return null;
        }

        $officials->forget($officials->search($match));

        return $match;
    }

    private function committees(): array
    {
        $base = ['photo', 'video', 'activity', 'accomplishment', 'report', 'attendance'];

        return [
            [
                'name' => 'Peace and Order',
                'slug' => 'peace-order',
                'chair_label' => 'Kap Robert',
                'chair_terms' => ['Robert'],
                'description' => 'Patrols, checkpoints, assemblies, public safety activities, incidents, policies, partnerships, trainings, and BPSO records.',
                'record_types' => array_values(array_unique(array_merge($base, ['blotter_incident', 'resolution_policy', 'partnership', 'training_seminar', 'personnel_list']))),
            ],
            [
                'name' => 'Health',
                'slug' => 'health',
                'chair_label' => 'Kgd Doc Twinkle',
                'chair_terms' => ['Twinkle'],
                'description' => 'Medical missions, vaccination, feeding programs, health reports, medical inventory, trainings, certificates, and clinic staff records.',
                'record_types' => array_values(array_unique(array_merge($base, ['inventory', 'partnership', 'certificate', 'personnel_list']))),
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'chair_label' => 'Kgd Fred Sicat',
                'chair_terms' => ['Fred', 'Sicat'],
                'description' => 'School events, scholarship support, literacy programs, education reports, attendance, awards, and institutional partnerships.',
                'record_types' => array_values(array_unique(array_merge($base, ['partnership', 'certificate']))),
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'chair_label' => 'Kgd Euler',
                'chair_terms' => ['Euler'],
                'description' => 'Project documentation, inspections, construction and maintenance activities, proposals, materials, budgets, permits, and contracts.',
                'record_types' => array_values(array_unique(array_merge($base, ['project_proposal', 'inventory', 'financial_record', 'permit_contract']))),
            ],
            [
                'name' => 'Environment',
                'slug' => 'environment',
                'chair_label' => 'Kgd Medel',
                'chair_terms' => ['Medel'],
                'description' => 'Clean-up drives, tree planting, waste management reports, volunteer attendance, tools, seedlings, PPEs, partners, awards, and street sweeper lists.',
                'record_types' => array_values(array_unique(array_merge($base, ['inventory', 'partnership', 'certificate', 'personnel_list']))),
            ],
            [
                'name' => 'Livelihood',
                'slug' => 'livelihood',
                'chair_label' => 'Kgd Fred',
                'chair_terms' => ['Fred'],
                'description' => 'Skills trainings, livelihood assistance, fairs, beneficiary monitoring, equipment issued, partner records, and completion certificates.',
                'record_types' => array_values(array_unique(array_merge($base, ['inventory', 'partnership', 'certificate', 'training_seminar']))),
            ],
            [
                'name' => 'Transport and Communication',
                'slug' => 'transport-communication',
                'chair_label' => 'Kgd Bem',
                'chair_terms' => ['Bem'],
                'description' => 'Transport meetings, terminal inspections, road safety, communication tools, policies, TODA records, and driver/operator profiling.',
                'record_types' => array_values(array_unique(array_merge($base, ['inventory', 'partnership', 'resolution_policy', 'driver_operator_profile']))),
            ],
            [
                'name' => 'Barangay Disaster Risk Reduction and Management',
                'slug' => 'bdrrm',
                'chair_label' => 'Kgd Joel',
                'chair_terms' => ['Joel'],
                'description' => 'Disaster drills, relief distribution, rescue operations, risk assessments, emergency logs, inventory, partners, certificates, and evacuation center records.',
                'record_types' => array_values(array_unique(array_merge($base, ['inventory', 'partnership', 'certificate', 'emergency_log', 'evacuation_center_record']))),
            ],
        ];
    }
}
