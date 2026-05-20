<?php

use App\Models\Business;
use App\Models\BusinessOwner;
use App\Models\Document;
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

test('user with documents permission can issue a standard certificate', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();

    $response = $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'purpose' => 'Employment',
        'additional_notes' => 'Bring valid ID.',
        'issued_by' => 'Barangay Secretary',
    ]);

    $response->assertCreated()->assertJsonPath('message', 'Document issued successfully!');

    $document = Document::firstWhere('resident_id', $resident->id);
    expect($document)->not->toBeNull()
        ->and($document->business_id)->toBeNull()
        ->and($document->rendered_html)->toContain('Juan Dela Cruz')
        ->and($document->rendered_html)->toContain('Employment')
        ->and($document->rendered_html)->toContain('Bring valid ID.')
        ->and($document->rendered_html)->toContain('Barangay Secretary')
        ->and($document->rendered_html)->not->toContain('{{');
});

test('business clearance requires a linked active business', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate('Business Clearance');

    $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'purpose' => 'Business renewal',
        'issued_by' => 'Barangay Secretary',
    ])->assertUnprocessable()->assertJsonValidationErrors('business_id');
});

test('business clearance can be issued with a resident business', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = DocumentTemplate::create([
        'name' => 'Business Clearance',
        'description' => 'Business Clearance',
        'template_html' => '<div class="title">Business Clearance</div><p>{{resident_name}} {{business_name}} {{business_type}} {{business_address}} {{purpose}}</p>',
        'fields_required' => ['resident_name', 'business_name'],
        'validity_days' => 365,
        'is_active' => true,
    ]);

    $business = Business::factory()->create([
        'business_name' => 'Juan Store',
        'business_type' => 'Sari-Sari',
        'business_address' => 'Market Road',
        'status' => 'Active',
    ]);

    $owner = BusinessOwner::create([
        'owner_type' => 'Resident',
        'resident_id' => $resident->id,
    ]);
    $owner->businesses()->attach($business->id);

    $response = $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'business_id' => $business->id,
        'purpose' => 'Business renewal',
        'issued_by' => 'Barangay Secretary',
    ]);

    $response->assertCreated();

    $document = Document::firstWhere('business_id', $business->id);
    expect($document)->not->toBeNull()
        ->and($document->rendered_html)->toContain('Juan Store')
        ->and($document->rendered_html)->toContain('Sari-Sari')
        ->and($document->rendered_html)->not->toContain('{{');
});

test('document pdf export responds successfully', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();
    $document = Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'rendered_html' => '<div class="title">Barangay Clearance</div><p>Rendered certificate</p>',
        'status' => 'Issued',
    ]);

    $this->actingAs($user)
        ->get(route('documents.downloadPdf', $document))
        ->assertOk();
});

test('documents datatable returns expected columns', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();
    Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'reference_number' => 'CLR-202605-00001',
        'status' => 'Issued',
    ]);

    $response = $this->actingAs($user)->getJson('/documents/data?draw=1&start=0&length=10');

    $response->assertOk()
        ->assertJsonPath('data.0.reference_number', 'CLR-202605-00001')
        ->assertJsonPath('data.0.resident_name', 'Juan Dela Cruz')
        ->assertJsonPath('data.0.template_name', 'Barangay Clearance');

    expect($response->json('data.0.status_badge'))->toContain('Issued')
        ->and($response->json('data.0.action'))->toContain('documents/');
});
