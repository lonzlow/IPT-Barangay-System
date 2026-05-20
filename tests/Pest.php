<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

use App\Models\DocumentTemplate;
use App\Models\Official;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function documentUser(): User
{
    $role = Role::create([
        'role_name' => 'Barangay Secretary',
        'description' => 'Document issuer',
    ]);

    $purok = \App\Models\Purok::create(['purok_name' => 'Purok 1']);
    $household = \App\Models\Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '123',
        'street' => 'Mabini Street',
        'family_size' => 4,
    ]);

    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);

    $official = Official::create([
        'official_number' => 'OFF-TEST-001',
        'resident_id' => $resident->id,
        'role_id' => $role->id,
        'term_start' => now()->subYear()->toDateString(),
        'term_end' => now()->addYear()->toDateString(),
        'is_active' => true,
    ]);

    return User::create([
        'official_id' => $official->id,
        'email' => 'secretary@example.test',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'Active',
    ]);
}

function adminUser(): User
{
    $role = Role::create([
        'role_name' => 'Admin',
        'description' => 'System administrator',
    ]);

    $purok = \App\Models\Purok::create(['purok_name' => 'Purok Admin']);
    $household = \App\Models\Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '1',
        'street' => 'Admin Street',
        'family_size' => 2,
    ]);

    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);

    $official = Official::create([
        'official_number' => 'OFF-ADMIN-001',
        'resident_id' => $resident->id,
        'role_id' => $role->id,
        'term_start' => now()->subYear()->toDateString(),
        'term_end' => now()->addYear()->toDateString(),
        'is_active' => true,
    ]);

    return User::create([
        'official_id' => $official->id,
        'email' => 'admin@example.test',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'Active',
    ]);
}

function activeResident(): Resident
{
    $purok = \App\Models\Purok::first() ?? \App\Models\Purok::create(['purok_name' => 'Purok 1']);
    $household = \App\Models\Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '456',
        'street' => 'Rizal Street',
        'family_size' => 3,
    ]);

    return Resident::factory()->create([
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);
}

function documentTemplate(string $name = 'Barangay Clearance'): DocumentTemplate
{
    return DocumentTemplate::create([
        'name' => $name,
        'description' => $name,
        'template_html' => '<div class="title">'.$name.'</div><p>{{resident_name}} {{purpose}} {{additional_notes}} {{reference_number}} {{issued_by}}</p>',
        'fields_required' => ['resident_name', 'purpose'],
        'validity_days' => 90,
        'is_active' => true,
    ]);
}
