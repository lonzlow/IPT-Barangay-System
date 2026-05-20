<?php

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Artisan;

test('documents expire command marks past-due issued documents as expired', function () {
    $user = documentUser();
    $resident = activeResident();
    $template = documentTemplate();

    $expired = Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'status' => 'Issued',
        'valid_until' => now()->subDay()->toDateString(),
        'issued_date' => now()->subMonths(3)->toDateString(),
    ]);

    $stillValid = Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => $template->id,
        'status' => 'Issued',
        'valid_until' => now()->addMonth()->toDateString(),
        'issued_date' => now()->toDateString(),
    ]);

    $noExpiry = Document::factory()->create([
        'resident_id' => $resident->id,
        'document_template_id' => DocumentTemplate::create([
            'name' => 'No Expiry Cert',
            'description' => 'Test',
            'template_html' => '<p>{{resident_name}}</p>',
            'fields_required' => ['resident_name'],
            'validity_days' => null,
            'is_active' => true,
        ])->id,
        'status' => 'Issued',
        'valid_until' => null,
        'issued_date' => now()->subYear()->toDateString(),
    ]);

    Artisan::call('documents:expire');

    expect($expired->fresh()->status)->toBe('Expired')
        ->and($stillValid->fresh()->status)->toBe('Issued')
        ->and($noExpiry->fresh()->status)->toBe('Issued');

    $log = ActivityLog::where('action', 'Auto-Expired Documents')->first();
    expect($log)->not->toBeNull()
        ->and($log->module)->toBe('Document Issuance');
});
