<?php

use App\Models\Committee;
use App\Models\CommitteeRecord;
use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CommitteeOfficialSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

function committeeTestUser(string $roleName): User
{
    if (Role::count() === 0) {
        (new RoleSeeder())->run();
    }

    $roleName = [
        'Secretary' => 'Barangay Secretary',
        'Treasurer' => 'Barangay Treasurer',
        'SK Chair' => 'SK Chairperson',
        'Tanod' => 'Barangay Tanod',
        'BHW' => 'Health Worker / BHW',
        'Encoder' => 'Encoder / Data Entry Clerk',
        'Auditor' => 'Auditor / Inspector',
    ][$roleName] ?? $roleName;

    $role = Role::where('role_name', $roleName)->firstOrFail();
    $purok = Purok::firstOrCreate(['purok_name' => 'Committee Test Purok']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => fake()->numerify('###'),
        'street' => fake()->streetName(),
        'family_size' => 4,
    ]);
    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);
    $official = Official::create([
        'official_number' => 'OFF-COM-' . fake()->unique()->numerify('######'),
        'resident_id' => $resident->id,
        'role_id' => $role->id,
        'term_start' => now()->subYear()->toDateString(),
        'term_end' => now()->addYear()->toDateString(),
        'is_active' => true,
    ]);

    return User::create([
        'official_id' => $official->id,
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'Active',
    ]);
}

function seedCommitteeResidents(int $count = 20): void
{
    $purok = Purok::firstOrCreate(['purok_name' => 'Committee Seeder Purok']);

    for ($i = 1; $i <= $count; $i++) {
        $household = Household::forceCreate([
            'purok_id' => $purok->id,
            'house_number' => (string) (100 + $i),
            'street' => 'Committee Street ' . $i,
            'family_size' => 4,
        ]);

        Resident::factory()->create([
            'household_id' => $household->id,
            'residency_status' => 'Active',
        ]);
    }
}

test('admin and kagawad can view committee dashboard', function () {
    (new RoleSeeder())->run();
    seedCommitteeResidents();
    (new CommitteeOfficialSeeder())->run();

    $this->actingAs(committeeTestUser('Admin'))
        ->get(route('committees.index'))
        ->assertOk()
        ->assertSee('Committee Dashboard')
        ->assertSee('Peace and Order');

    $this->actingAs(committeeTestUser('Kagawad'))
        ->get(route('committees.index'))
        ->assertOk();
});

test('unauthorized user cannot view committee dashboard', function () {
    (new RoleSeeder())->run();

    $this->actingAs(committeeTestUser('Encoder'))
        ->get(route('committees.index'))
        ->assertForbidden();
});

test('committee chairman is saved through chairperson id', function () {
    (new RoleSeeder())->run();
    $admin = committeeTestUser('Admin');
    $chairUser = committeeTestUser('Kagawad');
    $chairperson = $chairUser->official;

    $this->actingAs($admin)
        ->postJson(route('committees.store'), [
            'name' => 'Test Committee',
            'slug' => 'test-committee',
            'chair_label' => 'Kgd Test',
            'chairperson_id' => $chairperson->id,
            'description' => 'Test committee description',
            'allowed_record_types' => ['photo', 'report'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.chairperson_id', $chairperson->id);

    $this->assertDatabaseHas('committees', [
        'name' => 'Test Committee',
        'chairperson_id' => $chairperson->id,
    ]);
});

test('official chairperson designation updates committee chairperson', function () {
    (new RoleSeeder())->run();
    $admin = committeeTestUser('Admin');
    $chairUser = committeeTestUser('Kagawad');
    $committee = Committee::create([
        'name' => 'Designation Committee',
        'slug' => 'designation-committee',
        'description' => 'Designation sync test',
    ]);

    $this->actingAs($admin)
        ->postJson(route('officials.assignDesignation'), [
            'official_id' => $chairUser->official_id,
            'committee_id' => $committee->id,
            'designation' => 'Chairperson',
        ])
        ->assertOk();

    expect($committee->refresh()->chairperson_id)->toBe($chairUser->official_id);

    $this->assertDatabaseHas('official_assignments', [
        'official_id' => $chairUser->official_id,
        'committee_id' => $committee->id,
        'designation' => 'Chairperson',
    ]);

    $this->actingAs($admin)
        ->get(route('committees.index'))
        ->assertOk()
        ->assertSee('Designation Committee')
        ->assertSee($chairUser->official->resident->first_name);
});

test('committee seeder creates eight target committees with kagawad chairmen', function () {
    (new RoleSeeder())->run();
    seedCommitteeResidents();
    (new CommitteeOfficialSeeder())->run();

    $targetCommittees = [
        'Peace and Order',
        'Health',
        'Education',
        'Infrastructure',
        'Environment',
        'Livelihood',
        'Transport and Communication',
        'Barangay Disaster Risk Reduction and Management',
    ];

    expect(Committee::whereIn('name', $targetCommittees)->count())->toBe(8);

    Committee::whereIn('name', $targetCommittees)
        ->with('headOfficial')
        ->get()
        ->each(function (Committee $committee) {
            expect($committee->headOfficial)->not->toBeNull();
            expect($committee->headOfficial->role?->role_name)->toBe('Kagawad');
        });
});

test('committee records accept every supported record type', function () {
    (new RoleSeeder())->run();
    $admin = committeeTestUser('Admin');
    $committee = Committee::create([
        'name' => 'Records Committee',
        'slug' => 'records-committee',
        'description' => 'Records test',
        'allowed_record_types' => array_keys(CommitteeRecord::TYPES),
    ]);

    foreach (array_keys(CommitteeRecord::TYPES) as $type) {
        $this->actingAs($admin)
            ->postJson(route('committees.records.store', $committee), [
                'record_type' => $type,
                'title' => str($type)->headline()->toString(),
                'record_date' => now()->toDateString(),
                'description' => 'Record test',
            ])
            ->assertCreated();
    }

    expect($committee->records()->count())->toBe(count(CommitteeRecord::TYPES));
});

test('committee detail workspace displays grouped records and counts', function () {
    (new RoleSeeder())->run();
    $admin = committeeTestUser('Admin');
    $committee = Committee::create([
        'name' => 'Workspace Committee',
        'slug' => 'workspace-committee',
        'description' => 'Workspace description',
        'allowed_record_types' => ['photo', 'report'],
    ]);

    CommitteeRecord::create([
        'committee_id' => $committee->id,
        'record_type' => 'photo',
        'title' => 'Patrol Photo',
        'recorded_at' => now(),
    ]);
    CommitteeRecord::create([
        'committee_id' => $committee->id,
        'record_type' => 'report',
        'title' => 'Monthly Report',
        'recorded_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('committees.show', $committee))
        ->assertOk()
        ->assertSee('Workspace Committee')
        ->assertSee('Patrol Photo')
        ->assertSee('Monthly Report')
        ->assertSee('Total records');
});

test('assigned official only sees and opens assigned committees', function () {
    (new RoleSeeder())->run();
    $assignedUser = committeeTestUser('Kagawad');
    $assignedCommittee = Committee::create([
        'name' => 'Assigned Committee',
        'slug' => 'assigned-committee',
        'description' => 'Visible committee',
    ]);
    $otherCommittee = Committee::create([
        'name' => 'Other Committee',
        'slug' => 'other-committee',
        'description' => 'Hidden committee',
    ]);

    $assignedCommittee->assignments()->create([
        'official_id' => $assignedUser->official_id,
        'designation' => 'Member',
    ]);

    $this->actingAs($assignedUser)
        ->get(route('committees.index'))
        ->assertOk()
        ->assertSee('Assigned Committee')
        ->assertDontSee('Other Committee');

    $this->actingAs($assignedUser)
        ->get(route('committees.show', $assignedCommittee))
        ->assertOk()
        ->assertSee('Assigned Committee');

    $this->actingAs($assignedUser)
        ->get(route('committees.show', $otherCommittee))
        ->assertForbidden();
});

test('assigned official cannot create records for unassigned committee', function () {
    (new RoleSeeder())->run();
    $assignedUser = committeeTestUser('Kagawad');
    $assignedCommittee = Committee::create([
        'name' => 'Record Assigned Committee',
        'slug' => 'record-assigned-committee',
        'description' => 'Allowed committee',
    ]);
    $otherCommittee = Committee::create([
        'name' => 'Record Other Committee',
        'slug' => 'record-other-committee',
        'description' => 'Blocked committee',
    ]);

    $assignedCommittee->assignments()->create([
        'official_id' => $assignedUser->official_id,
        'designation' => 'Member',
    ]);

    $this->actingAs($assignedUser)
        ->postJson(route('committees.records.store', $assignedCommittee), [
            'record_type' => 'report',
            'title' => 'Allowed Report',
            'record_date' => now()->toDateString(),
        ])
        ->assertCreated();

    $this->actingAs($assignedUser)
        ->postJson(route('committees.records.store', $otherCommittee), [
            'record_type' => 'report',
            'title' => 'Blocked Report',
            'record_date' => now()->toDateString(),
        ])
        ->assertForbidden();
});

test('committee user with no assignments sees assigned empty state', function () {
    (new RoleSeeder())->run();
    $user = committeeTestUser('Kagawad');

    Committee::create([
        'name' => 'Unassigned Existing Committee',
        'slug' => 'unassigned-existing-committee',
        'description' => 'Should not be visible',
    ]);

    $this->actingAs($user)
        ->get(route('committees.index'))
        ->assertOk()
        ->assertSee('No committees assigned to your account yet')
        ->assertDontSee('Unassigned Existing Committee');
});

test('committee photo and video records can upload media files', function () {
    Storage::fake('public');
    (new RoleSeeder())->run();
    $admin = committeeTestUser('Admin');
    $committee = Committee::create([
        'name' => 'Media Committee',
        'slug' => 'media-committee',
        'description' => 'Media upload test',
        'allowed_record_types' => ['photo', 'video'],
    ]);

    $photoResponse = $this->actingAs($admin)
        ->post(route('committees.records.store', $committee), [
            'record_type' => 'photo',
            'title' => 'Checkpoint Photo',
            'record_date' => now()->toDateString(),
            'media_file' => UploadedFile::fake()->image('checkpoint.jpg', 900, 600),
        ])
        ->assertCreated();

    expect($photoResponse->json('data.file_path'))->toStartWith('/storage/committee-media/');

    $photoPath = str($photoResponse->json('data.file_path'))->after('/storage/')->toString();
    Storage::disk('public')->assertExists($photoPath);

    $videoResponse = $this->actingAs($admin)
        ->post(route('committees.records.store', $committee), [
            'record_type' => 'video',
            'title' => 'Patrol Video',
            'record_date' => now()->toDateString(),
            'media_file' => UploadedFile::fake()->create('patrol.mp4', 1024, 'video/mp4'),
        ])
        ->assertCreated();

    expect($videoResponse->json('data.file_path'))->toStartWith('/storage/committee-media/');

    $videoPath = str($videoResponse->json('data.file_path'))->after('/storage/')->toString();
    Storage::disk('public')->assertExists($videoPath);

    $this->actingAs($admin)
        ->get(route('committees.show', $committee))
        ->assertOk()
        ->assertSee('Checkpoint Photo')
        ->assertSee('Patrol Video')
        ->assertSee('<img src="' . $photoResponse->json('data.file_path'), false)
        ->assertSee('<video src="' . $videoResponse->json('data.file_path'), false);
});
