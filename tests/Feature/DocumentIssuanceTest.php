<?php

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\BusinessOwner;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Signature;
use Illuminate\Support\Facades\Storage;

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

test('issuing a document writes an activity log entry', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();

    $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'purpose' => 'Scholarship',
        'issued_by' => 'Barangay Secretary',
    ])->assertCreated();

    $document = Document::first();
    $log = ActivityLog::where('action', 'Issued Document')->latest()->first();

    expect($log)->not->toBeNull()
        ->and($log->module)->toBe('Document Issuance')
        ->and($log->user_id)->toBe($user->id)
        ->and($log->description)->toContain($document->reference_number)
        ->and($log->description)->toContain('Juan Dela Cruz');
});

test('updating and deleting documents write activity log entries', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();
    $document = Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'status' => 'Issued',
        'issued_by' => 'Barangay Secretary',
    ]);

    $this->actingAs($user)->putJson("/documents/{$document->id}", [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'purpose' => 'Updated purpose',
        'issued_by' => 'Barangay Secretary',
        'status' => 'Revoked',
    ])->assertOk();

    $updateLog = ActivityLog::where('action', 'Updated Document')->latest()->first();
    expect($updateLog)->not->toBeNull()
        ->and($updateLog->description)->toContain('Revoked');

    $this->actingAs($user)->deleteJson("/documents/{$document->id}")->assertOk();

    $deleteLog = ActivityLog::where('action', 'Deleted Document')->latest()->first();
    expect($deleteLog)->not->toBeNull()
        ->and($deleteLog->description)->toContain($document->reference_number);
});

test('document audit logs endpoint returns issuance activity', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();

    $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'issued_by' => 'Barangay Secretary',
    ])->assertCreated();

    $response = $this->actingAs($user)->getJson(route('documents.auditLogs'));

    $response->assertOk();

    expect(collect($response->json())->pluck('action'))->toContain('Issued Document');
});

test('empty signature_id does not fail validation on issue', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();

    $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'signature_id' => '',
        'issued_by' => 'Barangay Secretary',
    ])->assertCreated();
});

test('document can be issued with a selected signature', function () {
    Storage::fake('public');
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();
    $path = 'signatures/test.png';
    Storage::disk('public')->put($path, 'img');

    $signature = Signature::factory()->create([
        'official_id' => $user->official_id,
        'path' => $path,
    ]);

    $response = $this->actingAs($user)->postJson('/documents', [
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'purpose' => 'Employment',
        'issued_by' => 'Barangay Secretary',
        'signature_id' => $signature->id,
    ]);

    $response->assertCreated();

    $document = Document::first();
    expect($document->signature_id)->toBe($signature->id)
        ->and($document->signature_path)->toBe($path);
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
