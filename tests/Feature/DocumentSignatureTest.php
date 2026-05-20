<?php

use App\Models\Signature;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can upload a signature image', function () {
    Storage::fake('public');
    $user = adminUser();
    $official = $user->official;

    $file = UploadedFile::fake()->image('signature.png', 200, 80);

    $response = $this->actingAs($user)->post('/signatures', [
        'official_id' => $official->id,
        'label' => 'Captain',
        'signature' => $file,
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('message', 'Signature uploaded');

    $signature = Signature::first();
    expect($signature)->not->toBeNull()
        ->and($signature->official_id)->toBe($official->id)
        ->and($signature->label)->toBe('Captain');

    Storage::disk('public')->assertExists($signature->path);
});

test('non-admin cannot upload or delete signatures', function () {
    Storage::fake('public');
    $user = documentUser();
    $official = $user->official;
    $signature = Signature::factory()->create(['official_id' => $official->id]);
    $file = UploadedFile::fake()->image('signature.png');

    $this->actingAs($user)->post('/signatures', [
        'official_id' => $official->id,
        'signature' => $file,
    ])->assertForbidden();

    $this->actingAs($user)->delete("/signatures/{$signature->id}")
        ->assertForbidden();
});

test('admin can delete a signature', function () {
    Storage::fake('public');
    $user = adminUser();
    $path = 'signatures/test-sig.png';
    Storage::disk('public')->put($path, 'fake-image-data');

    $signature = Signature::factory()->create([
        'official_id' => $user->official_id,
        'path' => $path,
    ]);

    $this->actingAs($user)->deleteJson("/signatures/{$signature->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Signature deleted');

    expect(Signature::find($signature->id))->toBeNull();
    expect(Signature::withTrashed()->find($signature->id))->not->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
