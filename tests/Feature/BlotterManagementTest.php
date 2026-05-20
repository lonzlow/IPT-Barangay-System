<?php

use App\Models\Blotter;
use App\Models\BlotterEvidence;
use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated user can view the blotter dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('blotters.index'))
        ->assertOk()
        ->assertSee('Blotter Management')
        ->assertSee('Attach Supporting Documents');
});

test('blotter data endpoint returns datatables rows', function () {
    $user = User::factory()->create();
    Blotter::create([
        'case_number' => 'BLT-2026-0001',
        'complainant_name' => 'Maria Santos',
        'location' => 'Purok 1',
        'incident_description' => 'Noise complaint received.',
        'incident_date' => now(),
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->getJson(route('blotters.data'))
        ->assertOk()
        ->assertJsonPath('data.0.case_number', 'BLT-2026-0001')
        ->assertJsonPath('data.0.incident_type', 'Noise');
});

test('blotter records can be created and updated through json requests', function () {
    $user = User::factory()->create();

    $createResponse = $this->actingAs($user)
        ->postJson(route('blotters.store'), [
            'case_number' => 'SHOULD-BE-IGNORED',
            'complainant_name' => 'Juan dela Cruz',
            'respondent_name' => 'Pedro Reyes',
            'location' => 'Barangay Hall',
            'incident_description' => 'Physical altercation reported.',
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'status' => 'ongoing',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Blotter record saved successfully.');

    $blotter = Blotter::where('case_number', 'BL-000001')->firstOrFail();
    expect($blotter->status)->toBe('under investigation');
    expect($blotter->respondents()->first()?->respondent_name)->toBe('Pedro Reyes');

    $this->actingAs($user)
        ->putJson(route('blotters.update', $blotter), [
            'case_number' => 'SHOULD-STILL-BE-IGNORED',
            'complainant_name' => 'Juan dela Cruz',
            'respondent_name' => 'Pedro Reyes',
            'location' => 'Barangay Hall',
            'incident_description' => 'Physical altercation settled through mediation.',
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'status' => 'resolved',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Blotter record updated successfully.');

    expect($createResponse->json('dashboard.summary.total'))->toBeGreaterThan(0);
    expect($blotter->fresh()->status)->toBe('resolved')
        ->and($blotter->fresh()->case_number)->toBe('BL-000001');
});

test('blotter case numbers increment from the highest formatted BL record and ignore legacy numbers', function () {
    $user = User::factory()->create();

    Blotter::create([
        'case_number' => 'BLT-2026-9999',
        'complainant_name' => 'Legacy Person',
        'location' => 'Purok 1',
        'incident_description' => 'Legacy complaint.',
        'incident_date' => now(),
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->postJson(route('blotters.store'), [
            'complainant_name' => 'First New Case',
            'location' => 'Barangay Hall',
            'incident_description' => 'First generated complaint.',
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'status' => 'pending',
        ])
        ->assertCreated()
        ->assertJsonPath('data.case_number', 'BL-000001');

    $this->actingAs($user)
        ->postJson(route('blotters.store'), [
            'complainant_name' => 'Second New Case',
            'location' => 'Barangay Hall',
            'incident_description' => 'Second generated complaint.',
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'status' => 'pending',
        ])
        ->assertCreated()
        ->assertJsonPath('data.case_number', 'BL-000002');
});

test('blotter records can be soft deleted through json requests', function () {
    $user = User::factory()->create();
    $blotter = Blotter::create([
        'case_number' => 'BLT-2026-0003',
        'complainant_name' => 'Ana Reyes',
        'location' => 'Purok 2',
        'incident_description' => 'Property damage complaint.',
        'incident_date' => now(),
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('blotters.destroy', $blotter))
        ->assertOk()
        ->assertJsonPath('message', 'Blotter record deleted successfully.');

    expect(Blotter::withTrashed()->find($blotter->id)?->trashed())->toBeTrue();
});

test('supporting evidence can be uploaded by case number', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $blotter = Blotter::create([
        'case_number' => 'BLT-2026-0004',
        'complainant_name' => 'Rosa Aquino',
        'location' => 'Purok 3',
        'incident_description' => 'Trespassing complaint received.',
        'incident_date' => now(),
        'status' => 'pending',
        'filed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->postJson(route('blotters.evidence', $blotter->case_number), [
            'evidence' => UploadedFile::fake()->image('evidence.jpg'),
            'caption' => 'Photo evidence',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Supporting document attached successfully.');

    expect(BlotterEvidence::where('blotter_id', $blotter->id)->exists())->toBeTrue();
});

test('blotter case stores resident and non-resident parties with multiple evidence files', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $purok = Purok::factory()->create();
    $household = Household::factory()->create(['purok_id' => $purok->id]);
    $complainant = Resident::factory()->create(['household_id' => $household->id]);
    $respondent = Resident::factory()->create(['household_id' => $household->id]);
    $witnesses = Resident::factory()->count(2)->create(['household_id' => $household->id]);

    $this->actingAs($user)
        ->post(route('blotters.store'), [
            'incident_title' => 'Market disturbance',
            'complainant_mode' => 'resident',
            'complainant_id' => $complainant->id,
            'complainant_name' => 'Should Be Cleared',
            'respondents' => [
                ['mode' => 'resident', 'resident_id' => $respondent->id, 'name' => 'Should Be Cleared'],
                ['mode' => 'manual', 'resident_id' => $respondent->id, 'name' => 'Non Resident Person'],
            ],
            'witnesses' => [
                ['mode' => 'resident', 'resident_id' => $witnesses[0]->id, 'name' => 'Should Be Cleared'],
                ['mode' => 'manual', 'resident_id' => $witnesses[1]->id, 'name' => 'Non Resident Witness'],
            ],
            'location' => 'Public Market',
            'incident_description' => 'Verbal altercation with supporting documentation.',
            'incident_date' => now()->format('Y-m-d H:i:s'),
            'status' => 'pending',
            'evidences' => [
                UploadedFile::fake()->image('photo.jpg'),
                UploadedFile::fake()->create('statement.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('clip.mp4', 200, 'video/mp4'),
            ],
        ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('message', 'Blotter record saved successfully.');

    $blotter = Blotter::where('case_number', 'BL-000001')->firstOrFail();

    expect($blotter->complainant_id)->toBe($complainant->id)
        ->and($blotter->complainant_name)->toBeNull()
        ->and($blotter->respondents()->count())->toBe(2)
        ->and($blotter->respondents()->where('respondent_id', $respondent->id)->exists())->toBeTrue()
        ->and($blotter->respondents()->where('respondent_id', $respondent->id)->value('respondent_name'))->toBeNull()
        ->and($blotter->respondents()->whereNull('respondent_id')->where('respondent_name', 'Non Resident Person')->exists())->toBeTrue()
        ->and($blotter->witnesses()->count())->toBe(2)
        ->and($blotter->witnesses()->where('witness_id', $witnesses[0]->id)->value('witness_name'))->toBeNull()
        ->and($blotter->witnesses()->whereNull('witness_id')->where('witness_name', 'Non Resident Witness')->exists())->toBeTrue()
        ->and($blotter->evidences()->count())->toBe(3);
});

test('blotter export downloads a csv file', function () {
    $user = User::factory()->create();
    Blotter::create([
        'case_number' => 'BLT-2026-0005',
        'complainant_name' => 'Carlo Mendoza',
        'location' => 'Purok 4',
        'incident_description' => 'Theft complaint logged.',
        'incident_date' => now(),
        'status' => 'resolved',
        'filed_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('blotters.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
