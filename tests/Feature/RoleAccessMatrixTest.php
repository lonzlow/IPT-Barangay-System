<?php

use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

function roleAccessUser(string $roleName): User
{
    (new RoleSeeder())->run();

    $role = Role::where('role_name', $roleName)->firstOrFail();
    $purok = Purok::firstOrCreate(['purok_name' => 'Role Access Purok']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => fake()->unique()->numerify('RA-###'),
        'street' => 'Role Access Street',
        'family_size' => 4,
    ]);
    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);
    $official = Official::create([
        'official_number' => 'OFF-RA-' . fake()->unique()->numerify('######'),
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

function expectAllows(User $user, array $abilities): void
{
    foreach ($abilities as $ability) {
        expect(Gate::forUser($user)->allows($ability))
            ->toBeTrue("Expected {$user->official->role->role_name} to allow {$ability}");
    }
}

function expectDenies(User $user, array $abilities): void
{
    foreach ($abilities as $ability) {
        expect(Gate::forUser($user)->denies($ability))
            ->toBeTrue("Expected {$user->official->role->role_name} to deny {$ability}");
    }
}

test('punong barangay has read and document approval access without staff write access', function () {
    $user = roleAccessUser('Punong Barangay');

    expectAllows($user, [
        'residents.view',
        'documents.view',
        'documents.approve',
        'blotter.view',
        'households.view',
        'business.view',
        'officials.view',
        'committees.view',
        'reports.view',
    ]);

    expectDenies($user, [
        'residents.manage',
        'documents.manage',
        'blotter.manage',
        'households.manage',
        'business.manage',
        'committee-records.manage',
        'users.view',
    ]);
});

test('barangay secretary can operate records without system configuration access', function () {
    $user = roleAccessUser('Barangay Secretary');

    expectAllows($user, [
        'residents.manage',
        'documents.manage',
        'blotter.manage',
        'households.manage',
        'business.manage',
        'committees.manage',
        'committee-records.manage',
        'reports.view',
    ]);

    expectDenies($user, [
        'document-templates.manage',
        'signatures.manage',
        'users.view',
    ]);
});

test('barangay treasurer is limited to finance related access', function () {
    $user = roleAccessUser('Barangay Treasurer');

    expectAllows($user, [
        'documents.view',
        'business.view',
        'business.manage',
        'business.permits.manage',
        'business.delete',
    ]);

    expectDenies($user, [
        'residents.view',
        'blotter.view',
        'households.view',
        'officials.view',
        'committees.view',
        'reports.view',
        'documents.manage',
        'documents.approve',
        'users.view',
    ]);
});

test('committee roles can only use committee workspaces and records', function (string $roleName) {
    $user = roleAccessUser($roleName);

    expectAllows($user, [
        'committees.view',
        'committee-records.manage',
    ]);

    expectDenies($user, [
        'residents.view',
        'documents.view',
        'blotter.view',
        'households.manage',
        'business.view',
        'reports.view',
        'users.view',
    ]);
})->with([
    'kagawad' => 'Kagawad',
    'sk chairperson' => 'SK Chairperson',
]);

test('operational responders are scoped to their modules', function (string $roleName, array $allowed, array $denied) {
    $user = roleAccessUser($roleName);

    expectAllows($user, $allowed);
    expectDenies($user, $denied);
})->with([
    'barangay tanod' => [
        'Barangay Tanod',
        ['blotter.view', 'blotter.manage'],
        ['residents.view', 'documents.view', 'households.view', 'business.view', 'reports.view', 'users.view'],
    ],
    'health worker' => [
        'Health Worker / BHW',
        ['residents.view', 'households.view', 'committees.view', 'committee-records.manage'],
        ['residents.manage', 'documents.view', 'blotter.view', 'business.view', 'reports.view', 'users.view'],
    ],
    'bdrrm coordinator' => [
        'BDRRM Coordinator',
        ['residents.view', 'households.view', 'committees.view', 'committee-records.manage'],
        ['residents.manage', 'documents.view', 'blotter.view', 'business.view', 'reports.view', 'users.view'],
    ],
]);

test('encoder can add and edit records but cannot delete approve or inspect analytics', function () {
    $user = roleAccessUser('Encoder / Data Entry Clerk');

    expectAllows($user, [
        'residents.manage',
        'documents.manage',
        'blotter.manage',
        'households.manage',
        'business.manage',
    ]);

    expectDenies($user, [
        'residents.delete',
        'documents.delete',
        'documents.approve',
        'blotter.delete',
        'households.delete',
        'business.delete',
        'reports.view',
        'users.view',
    ]);
});

test('auditor has full read only access', function () {
    $user = roleAccessUser('Auditor / Inspector');

    expectAllows($user, [
        'residents.view',
        'documents.view',
        'blotter.view',
        'households.view',
        'business.view',
        'officials.view',
        'committees.view',
        'reports.view',
    ]);

    expectDenies($user, [
        'residents.manage',
        'documents.manage',
        'documents.approve',
        'blotter.manage',
        'households.manage',
        'business.manage',
        'committees.manage',
        'committee-records.manage',
        'users.view',
    ]);
});
