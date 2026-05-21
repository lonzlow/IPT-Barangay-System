<?php

use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

test('dashboard sends admin users to residents first', function () {
    $user = dashboardRedirectUser('Admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('residents.index'));
});

test('dashboard sends users without resident access to their first allowed module', function (string $roleName, string $routeName) {
    $user = dashboardRedirectUser($roleName);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($routeName));
})->with([
    'treasurer uses documents' => ['Barangay Treasurer', 'documents.index'],
    'tanod uses blotters' => ['Barangay Tanod', 'blotters.index'],
    'kagawad uses committees' => ['Kagawad', 'committees.index'],
]);

function dashboardRedirectUser(string $roleName): User
{
    (new RoleSeeder())->run();

    $role = Role::where('role_name', $roleName)->firstOrFail();
    $purok = Purok::firstOrCreate(['purok_name' => 'Dashboard Redirect Purok']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => fake()->unique()->numerify('DR-###'),
        'street' => 'Dashboard Redirect Street',
        'family_size' => 4,
    ]);
    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'residency_status' => 'Active',
    ]);
    $official = Official::create([
        'official_number' => 'OFF-DR-' . fake()->unique()->numerify('######'),
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
