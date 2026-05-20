<?php

use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;

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
        ->and($row['registered_voters'])->toBe(1);
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

test('purok leader must belong to the same purok', function () {
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
    ])->assertSessionHasErrors('leader_id');

    $this->actingAs($user)->put(route('puroks.update', $purok), [
        'purok_name' => $purok->purok_name,
        'description' => 'Updated',
        'leader_id' => $leader->id,
    ])->assertRedirect(route('puroks.show', $purok));

    expect($purok->fresh()->leader_id)->toBe($leader->id);
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
});
