<?php

use App\Models\Business;
use App\Models\BusinessOwner;
use App\Models\BusinessPermit;
use App\Models\PermitRenewal;
use App\Models\Resident;

function businessPayload(?BusinessOwner $owner = null): array
{
    $owner ??= BusinessOwner::create([
        'owner_type' => 'Resident',
        'resident_id' => activeResident()->id,
    ]);

    return [
        'business_name' => 'Aegean Sari-Sari Store',
        'business_owner_ids' => [$owner->id],
        'ownership_roles' => ['Owner'],
        'ownership_percentages' => [''],
        'business_type' => 'Sari-Sari',
        'business_address' => 'Rizal Street',
        'date_established' => '2024-01-15',
        'status' => 'Active',
    ];
}

function businessWithOwner(): Business
{
    $resident = activeResident();
    $owner = BusinessOwner::create([
        'owner_type' => 'Resident',
        'resident_id' => $resident->id,
    ]);

    $business = Business::factory()->create([
        'business_name' => 'Aegean Store',
        'business_type' => 'Sari-Sari',
        'business_address' => 'Rizal Street',
        'status' => 'Active',
    ]);

    $owner->businesses()->attach($business->id, ['ownership_role' => 'Owner']);

    return $business;
}

test('authorized user can register a business with owner data', function () {
    $user = documentUser();
    $owner = BusinessOwner::create([
        'owner_type' => 'Resident',
        'resident_id' => activeResident()->id,
    ]);

    $response = $this->actingAs($user)->postJson(route('businesses.store'), businessPayload($owner));

    $response->assertCreated()
        ->assertJsonPath('message', 'Business created successfully.');

    $business = Business::firstWhere('business_name', 'Aegean Sari-Sari Store');

    expect($business)->not->toBeNull()
        ->and($business->business_owners()->whereKey($owner->id)->exists())->toBeTrue();
});

test('authorized user can view the business permit dashboard', function () {
    $user = documentUser();
    businessWithOwner();

    $this->actingAs($user)
        ->get(route('businesses.index'))
        ->assertOk()
        ->assertSee('Business Permit Management')
        ->assertSee('Issue Business Clearance')
        ->assertSee('Renewal Tracker');
});

test('business datatable returns permit columns', function () {
    $user = documentUser();
    $business = businessWithOwner();

    BusinessPermit::create([
        'business_id' => $business->id,
        'permit_number' => 'BP-2026-00001',
        'issued_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'permit_status' => 'Approved',
        'issued_by' => $user->official_id,
    ]);

    $response = $this->actingAs($user)->getJson(route('businesses.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]));

    $response->assertOk();

    $row = collect($response->json('data'))->firstWhere('id', $business->id);

    expect($row['permit_number'])->toBe('BP-2026-00001')
        ->and($row['permit_status_badge'])->toContain('Approved')
        ->and($row['expiry_date'])->toContain('Dec 31, 2026');
});

test('permit issue creates generated reference with annual validity', function () {
    $user = documentUser();
    $business = businessWithOwner();

    $response = $this->actingAs($user)->postJson(route('businesses.permits.issue', $business), [
        'issued_date' => '2026-05-21',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Business permit issued successfully.')
        ->assertJsonPath('permit.permit_number', 'BP-2026-00001')
        ->assertJsonPath('permit.expiry_date', '2027-05-20')
        ->assertJsonPath('permit.display_status', 'Approved');

    $permit = BusinessPermit::firstWhere('business_id', $business->id);

    expect($permit)->not->toBeNull()
        ->and($permit->permit_number)->toBe('BP-2026-00001')
        ->and($permit->issued_date->format('Y-m-d'))->toBe('2026-05-21')
        ->and($permit->expiry_date->format('Y-m-d'))->toBe('2027-05-20')
        ->and($permit->permit_status)->toBe('Approved')
        ->and($permit->issued_by)->toBe($user->official_id);
});

test('duplicate active permit issue is rejected', function () {
    $user = documentUser();
    $business = businessWithOwner();

    BusinessPermit::create([
        'business_id' => $business->id,
        'permit_number' => 'BP-2026-00001',
        'issued_date' => now()->toDateString(),
        'expiry_date' => now()->addYear()->toDateString(),
        'permit_status' => 'Approved',
        'issued_by' => $user->official_id,
    ]);

    $this->actingAs($user)->postJson(route('businesses.permits.issue', $business), [
        'issued_date' => now()->toDateString(),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('permit');
});

test('renewal records fee and updates existing permit expiry and status', function () {
    $user = documentUser();
    $business = businessWithOwner();
    $permit = BusinessPermit::create([
        'business_id' => $business->id,
        'permit_number' => 'BP-2026-00001',
        'issued_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'permit_status' => 'Approved',
        'issued_by' => $user->official_id,
    ]);

    $response = $this->actingAs($user)->postJson(route('businesses.permits.renew', [$business, $permit]), [
        'renewal_date' => '2026-12-15',
        'fee_paid' => '500.00',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Business permit renewed successfully.')
        ->assertJsonPath('permit.expiry_date', '2027-12-14')
        ->assertJsonPath('permit.display_status', 'Renewed');

    expect($permit->fresh()->permit_status)->toBe('Renewed')
        ->and($permit->fresh()->expiry_date->format('Y-m-d'))->toBe('2027-12-14');

    $renewal = PermitRenewal::firstWhere('permit_id', $permit->id);

    expect($renewal)->not->toBeNull()
        ->and((float) $renewal->fee_paid)->toBe(500.0)
        ->and($renewal->renewal_date->format('Y-m-d'))->toBe('2026-12-15')
        ->and($renewal->new_expiry_date->format('Y-m-d'))->toBe('2027-12-14')
        ->and($renewal->processed_by)->toBe($user->official_id);
});

test('expired permits display as expired in business data', function () {
    $user = documentUser();
    $business = businessWithOwner();

    BusinessPermit::create([
        'business_id' => $business->id,
        'permit_number' => 'BP-2024-00001',
        'issued_date' => now()->subYears(2)->toDateString(),
        'expiry_date' => now()->subDay()->toDateString(),
        'permit_status' => 'Approved',
        'issued_by' => $user->official_id,
    ]);

    $response = $this->actingAs($user)->getJson(route('businesses.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]));

    $response->assertOk();

    $row = collect($response->json('data'))->firstWhere('id', $business->id);

    expect($row['permit_status_badge'])->toContain('Expired');
});

test('authorized user can delete a business permit table row', function () {
    $user = documentUser();
    $business = businessWithOwner();

    BusinessPermit::create([
        'business_id' => $business->id,
        'permit_number' => 'BP-2026-00001',
        'issued_date' => '2026-01-01',
        'expiry_date' => '2026-12-31',
        'permit_status' => 'Approved',
        'issued_by' => $user->official_id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('businesses.destroy', $business))
        ->assertOk()
        ->assertJsonPath('message', 'Business permit row deleted successfully.');

    expect($business->fresh()->trashed())->toBeTrue();

    $response = $this->actingAs($user)->getJson(route('businesses.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]));

    $response->assertOk();

    expect(collect($response->json('data'))->firstWhere('id', $business->id))->toBeNull();
});

test('business permit routes require auth and business permission', function () {
    $business = businessWithOwner();

    $this->get(route('businesses.index'))->assertRedirect(route('login'));

    $user = documentUser();
    $user->official->role->update(['role_name' => 'Tanod']);

    $this->actingAs($user)->get(route('businesses.index'))->assertForbidden();
    $this->actingAs($user)->postJson(route('businesses.permits.issue', $business), [
        'issued_date' => '2026-05-21',
    ])->assertForbidden();
});
