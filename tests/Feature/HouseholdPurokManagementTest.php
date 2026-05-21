<?php

use App\Models\Household;
use App\Models\Official;
use App\Models\Purok;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function householdViewerUser(): User
{
    $role = Role::create([
        'role_name' => 'Health Worker / BHW',
        'description' => 'Household viewer',
    ]);

    $purok = Purok::create(['purok_name' => 'Viewer Purok']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => 'VIEW-1',
        'street' => 'Viewer Street',
        'family_size' => 1,
    ]);
    $resident = Resident::factory()->create(['household_id' => $household->id]);

    $official = Official::create([
        'official_number' => 'OFF-HH-VIEW',
        'resident_id' => $resident->id,
        'role_id' => $role->id,
        'term_start' => now()->subYear()->toDateString(),
        'term_end' => now()->addYear()->toDateString(),
        'is_active' => true,
    ]);

    return User::create([
        'official_id' => $official->id,
        'email' => 'household-viewer@example.test',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'Active',
    ]);
}

test('authorized user can create a household', function () {
    $user = adminUser();
    $purok = Purok::factory()->create();

    $response = $this->actingAs($user)->postJson(route('households.store'), [
        'purok_id' => $purok->id,
        'house_number' => '24B',
        'street' => 'Mabini Street',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Household created successfully.');

    $this->assertDatabaseHas('households', [
        'purok_id' => $purok->id,
        'house_number' => '24B',
        'street' => 'Mabini Street',
        'family_size' => 0,
    ]);
});

test('authorized user can create a purok with any resident as leader', function () {
    $user = adminUser();
    $existingPurok = Purok::factory()->create(['purok_name' => 'Existing Leader Purok']);
    $household = Household::forceCreate([
        'purok_id' => $existingPurok->id,
        'house_number' => '88',
        'street' => 'Leader Source Street',
        'family_size' => 0,
    ]);
    $leader = Resident::factory()->create(['household_id' => $household->id]);

    $this->actingAs($user)->post(route('puroks.store'), [
        'purok_name' => 'New Purok With Leader',
        'description' => 'Created with an assigned leader',
        'leader_id' => $leader->id,
    ])->assertRedirect(route('puroks.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('puroks', [
        'purok_name' => 'New Purok With Leader',
        'leader_id' => $leader->id,
    ]);
});

test('household web forms create and update records with redirects', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Web']);

    $createResponse = $this->actingAs($user)->post(route('households.store'), [
        'purok_id' => $purok->id,
        'house_number' => '31',
        'street' => 'Bonifacio Street',
    ]);

    $createResponse->assertRedirect(route('households.index'))
        ->assertSessionHas('success');

    $household = Household::where('house_number', '31')->firstOrFail();

    $updateResponse = $this->actingAs($user)->put(route('households.update', $household), [
        'purok_id' => $purok->id,
        'house_number' => '31-A',
        'street' => 'Bonifacio Avenue',
        'head_resident_id' => null,
    ]);

    $updateResponse->assertRedirect(route('households.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('households', [
        'id' => $household->id,
        'house_number' => '31-A',
        'street' => 'Bonifacio Avenue',
    ]);
});

test('view-only user cannot manage or delete households and puroks', function () {
    $viewer = householdViewerUser();
    $purok = Purok::factory()->create(['purok_name' => 'Restricted Purok']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '44',
        'street' => 'Restricted Street',
        'family_size' => 0,
    ]);

    $this->actingAs($viewer)->get(route('households.index'))->assertOk();
    $this->actingAs($viewer)->get(route('puroks.index'))->assertOk();

    $this->actingAs($viewer)->post(route('households.store'), [
        'purok_id' => $purok->id,
        'house_number' => '45',
        'street' => 'Restricted Street',
    ])->assertForbidden();

    $this->actingAs($viewer)->put(route('households.update', $household), [
        'purok_id' => $purok->id,
        'house_number' => '44',
        'street' => 'Restricted Street',
    ])->assertForbidden();

    $this->actingAs($viewer)->delete(route('households.destroy', $household))->assertForbidden();

    $this->actingAs($viewer)->post(route('puroks.store'), [
        'purok_name' => 'Blocked Purok',
    ])->assertForbidden();

    $this->actingAs($viewer)->put(route('puroks.update', $purok), [
        'purok_name' => 'Restricted Purok Updated',
    ])->assertForbidden();

    $this->actingAs($viewer)->delete(route('puroks.destroy', $purok))->assertForbidden();
});

test('household data uses assigned residents for family size and voters', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Data']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '10',
        'street' => 'Rizal Street',
        'family_size' => 99,
    ]);

    Resident::factory()->create([
        'household_id' => $household->id,
        'voter_status' => 'Registered',
    ]);
    Resident::factory()->create([
        'household_id' => $household->id,
        'voter_status' => 'Unregistered',
    ]);

    $response = $this->actingAs($user)->getJson(route('households.data'));

    $response->assertOk();

    $row = collect($response->json('data'))->firstWhere('id', $household->id);

    expect($row['family_size'])->toBe(2)
        ->and($row['registered_voters'])->toBe(1)
        ->and($row['action'])->toContain('data-household-action="edit"')
        ->and($row['action'])->toContain('data-household-id="' . $household->id . '"');
});

test('household head must be a resident of the same household', function () {
    $user = adminUser();
    $purok = Purok::factory()->create();
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '1',
        'street' => 'Alpha Street',
        'family_size' => 0,
    ]);
    $otherHousehold = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '2',
        'street' => 'Beta Street',
        'family_size' => 0,
    ]);
    $resident = Resident::factory()->create(['household_id' => $household->id]);
    $otherResident = Resident::factory()->create(['household_id' => $otherHousehold->id]);

    $this->actingAs($user)->putJson(route('households.update', $household), [
        'purok_id' => $purok->id,
        'house_number' => '1',
        'street' => 'Alpha Street',
        'head_resident_id' => $otherResident->id,
    ])->assertUnprocessable();

    $this->actingAs($user)->putJson(route('households.update', $household), [
        'purok_id' => $purok->id,
        'house_number' => '1',
        'street' => 'Alpha Street',
        'head_resident_id' => $resident->id,
    ])->assertOk();

    expect($household->fresh()->head_resident_id)->toBe($resident->id);
});

test('purok leader can be any registered resident', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Leader']);
    $otherPurok = Purok::factory()->create(['purok_name' => 'Purok Other']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '1',
        'street' => 'Leader Street',
        'family_size' => 0,
    ]);
    $otherHousehold = Household::forceCreate([
        'purok_id' => $otherPurok->id,
        'house_number' => '9',
        'street' => 'Other Street',
        'family_size' => 0,
    ]);
    $leader = Resident::factory()->create(['household_id' => $household->id]);
    $otherResident = Resident::factory()->create(['household_id' => $otherHousehold->id]);

    $this->actingAs($user)->put(route('puroks.update', $purok), [
        'purok_name' => $purok->purok_name,
        'description' => 'Updated',
        'leader_id' => $otherResident->id,
    ])->assertRedirect(route('puroks.show', $purok));

    expect($purok->fresh()->leader_id)->toBe($otherResident->id);

    $this->actingAs($user)->put(route('puroks.update', $purok), [
        'purok_name' => $purok->purok_name,
        'description' => 'Updated',
        'leader_id' => $leader->id,
    ])->assertRedirect(route('puroks.show', $purok));

    expect($purok->fresh()->leader_id)->toBe($leader->id);
});

test('purok web forms create update and delete empty records with redirects', function () {
    $user = adminUser();

    $this->actingAs($user)->post(route('puroks.store'), [
        'purok_name' => 'Purok Web Flow',
        'description' => 'Created from web form',
    ])->assertRedirect(route('puroks.index'))
        ->assertSessionHas('success');

    $purok = Purok::where('purok_name', 'Purok Web Flow')->firstOrFail();

    $this->actingAs($user)->put(route('puroks.update', $purok), [
        'purok_name' => 'Purok Web Flow Updated',
        'description' => 'Updated from web form',
        'leader_id' => null,
    ])->assertRedirect(route('puroks.show', $purok))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('puroks', [
        'id' => $purok->id,
        'purok_name' => 'Purok Web Flow Updated',
    ]);

    $this->actingAs($user)->delete(route('puroks.destroy', $purok))
        ->assertRedirect(route('puroks.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('puroks', ['id' => $purok->id]);
});

test('purok show counts only registered voters', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Voters']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '7',
        'street' => 'Voter Street',
        'family_size' => 0,
    ]);

    Resident::factory()->create(['household_id' => $household->id, 'voter_status' => 'Registered']);
    Resident::factory()->create(['household_id' => $household->id, 'voter_status' => 'Suspended']);

    $this->actingAs($user)->get(route('puroks.show', $purok))
        ->assertOk()
        ->assertSee('Registered Voters')
        ->assertSee('1');
});

test('household statistics use derived family size and voter totals', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Stats']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '3',
        'street' => 'Stats Street',
        'family_size' => 50,
    ]);

    Resident::factory()->create(['household_id' => $household->id, 'voter_status' => 'Registered']);
    Resident::factory()->create(['household_id' => $household->id, 'voter_status' => 'Unregistered']);

    $this->actingAs($user)->get(route('households.statistics'))
        ->assertOk()
        ->assertSee('Purok Stats')
        ->assertSee('2')
        ->assertSee('1');
});

test('household and purok deletion are blocked when records have dependents', function () {
    $user = adminUser();
    $purok = Purok::factory()->create();
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '8',
        'street' => 'Dependent Street',
        'family_size' => 0,
    ]);
    Resident::factory()->create(['household_id' => $household->id]);

    $this->actingAs($user)->deleteJson(route('households.destroy', $household))
        ->assertUnprocessable();

    $this->actingAs($user)->delete(route('puroks.destroy', $purok))
        ->assertRedirect(route('puroks.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('households', ['id' => $household->id]);
    $this->assertDatabaseHas('residents', ['household_id' => $household->id]);
    $this->assertDatabaseHas('puroks', ['id' => $purok->id]);
});

test('empty household and purok records can be deleted', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Empty Delete']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '99',
        'street' => 'Empty Street',
        'family_size' => 0,
    ]);

    $this->actingAs($user)->deleteJson(route('households.destroy', $household))
        ->assertOk()
        ->assertJsonPath('message', 'Household deleted successfully.');

    $this->assertDatabaseMissing('households', ['id' => $household->id]);

    $this->actingAs($user)->delete(route('puroks.destroy', $purok))
        ->assertRedirect(route('puroks.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('puroks', ['id' => $purok->id]);
});

test('purok delete block returns json without removing dependents', function () {
    $user = adminUser();
    $purok = Purok::factory()->create(['purok_name' => 'Purok Json Block']);
    $household = Household::forceCreate([
        'purok_id' => $purok->id,
        'house_number' => '11',
        'street' => 'Json Street',
        'family_size' => 0,
    ]);

    $this->actingAs($user)->deleteJson(route('puroks.destroy', $purok))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Cannot delete purok with households. Please reassign households first.');

    $this->assertDatabaseHas('puroks', ['id' => $purok->id]);
    $this->assertDatabaseHas('households', ['id' => $household->id]);
});
