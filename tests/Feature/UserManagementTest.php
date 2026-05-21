<?php

use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create a user linked to a selected official', function () {
    $admin = adminUser();
    $originalRole = Role::create([
        'role_name' => 'Tanod',
        'description' => 'Barangay tanod',
    ]);
    $newRole = Role::create([
        'role_name' => 'Barangay Secretary',
        'description' => 'Document issuer',
    ]);
    $official = userManagementOfficial('OFF-USER-001', 'Selectable', 'Official', $originalRole);

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'official_id' => $official->id,
        'email' => 'new-user@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $newRole->id,
    ]);

    $response
        ->assertRedirect(route('users.index'))
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'new-user@example.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->official_id)->toBe($official->id)
        ->and($user->status)->toBe('Active')
        ->and(Hash::check('password123', $user->password))->toBeTrue()
        ->and($official->refresh()->role_id)->toBe($newRole->id);
});

test('create user page only offers officials without user accounts', function () {
    $admin = adminUser();
    $role = Role::create([
        'role_name' => 'Kagawad',
        'description' => 'Council member',
    ]);
    $availableOfficial = userManagementOfficial('OFF-USER-002', 'Available', 'Candidate', $role);
    $linkedOfficial = userManagementOfficial('OFF-USER-003', 'Linked', 'Accounted', $role);

    User::create([
        'official_id' => $linkedOfficial->id,
        'email' => 'linked-official@example.test',
        'password' => 'password123',
        'status' => 'Active',
    ]);

    $response = $this->actingAs($admin)->get(route('users.create'));

    $response
        ->assertOk()
        ->assertSee($availableOfficial->resident->last_name)
        ->assertDontSee($linkedOfficial->resident->last_name);
});

function userManagementOfficial(string $number, string $firstName, string $lastName, Role $role): Official
{
    $purok = Purok::create(['purok_name' => $number]);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '100',
        'street' => 'Mabini Street',
        'family_size' => 1,
    ]);
    $resident = Resident::factory()->create([
        'household_id' => $household->id,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'residency_status' => 'Active',
    ]);

    return Official::create([
        'official_number' => $number,
        'resident_id' => $resident->id,
        'role_id' => $role->id,
        'term_start' => now()->subYear()->toDateString(),
        'term_end' => now()->addYear()->toDateString(),
        'is_active' => true,
    ]);
}
