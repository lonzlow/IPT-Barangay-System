<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommitteeRecordController extends Controller
{
    public function store(Request $request, Committee $committee)
    {
        $this->authorize('committee-records.manage');
        abort_unless($this->canAccessCommittee($request, $committee), 403);

        $validated = $this->validateRecord($request);
        $validated['committee_id'] = $committee->id;
        $validated['uploaded_by_id'] = $request->user()?->id;
        $validated['recorded_at'] = $validated['recorded_at'] ?? $validated['record_date'] ?? now();
        $validated = $this->storeUploadedMedia($request, $committee, $validated);

        $record = CommitteeRecord::create($validated);

        return response()->json([
            'message' => 'Committee record created successfully',
            'data' => $record->load('committee'),
        ], 201);
    }

    public function update(Request $request, CommitteeRecord $record)
    {
        $this->authorize('committee-records.manage');
        abort_unless($this->canAccessCommittee($request, $record->committee), 403);

        $validated = $this->validateRecord($request);
        $validated['recorded_at'] = $validated['recorded_at'] ?? $validated['record_date'] ?? $record->recorded_at ?? now();
        $validated = $this->storeUploadedMedia($request, $record->committee, $validated, $record);

        $record->update($validated);

        return response()->json([
            'message' => 'Committee record updated successfully',
            'data' => $record->fresh()->load('committee'),
        ]);
    }

    public function destroy(CommitteeRecord $record)
    {
        $this->authorize('committee-records.manage');
        abort_unless($this->canAccessCommittee(request(), $record->committee), 403);

        $this->deleteStoredMedia($record->file_path);
        $record->delete();

        return response()->json([
            'message' => 'Committee record deleted successfully',
        ]);
    }

    private function validateRecord(Request $request): array
    {
        $mediaRules = ['nullable', 'file', 'max:102400'];

        if ($request->input('record_type') === 'photo') {
            $mediaRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'];
        }

        if ($request->input('record_type') === 'video') {
            $mediaRules = ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv', 'max:204800'];
        }

        return $request->validate([
            'record_type' => ['required', 'string', Rule::in(array_keys(CommitteeRecord::TYPES))],
            'category' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'record_date' => ['nullable', 'date'],
            'recorded_at' => ['nullable', 'date'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:80'],
            'metadata' => ['nullable', 'array'],
            'file_path' => ['nullable', 'string', 'max:2048'],
            'media_file' => $mediaRules,
        ]);
    }

    private function storeUploadedMedia(Request $request, Committee $committee, array $validated, ?CommitteeRecord $record = null): array
    {
        unset($validated['media_file']);

        if (! $request->hasFile('media_file')) {
            return $validated;
        }

        $file = $request->file('media_file');
        $slug = $committee->slug ?: Str::slug($committee->name);
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path = $file->storeAs(
            'committee-media/' . $slug,
            Str::uuid() . ($extension ? '.' . $extension : ''),
            'public'
        );

        if ($record?->file_path) {
            $this->deleteStoredMedia($record->file_path);
        }

        $metadata = $validated['metadata'] ?? [];
        $metadata['uploaded_file'] = [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'disk' => 'public',
            'path' => $path,
        ];

        $validated['file_path'] = Storage::disk('public')->url($path);
        $validated['metadata'] = $metadata;

        return $validated;
    }

    private function deleteStoredMedia(?string $filePath): void
    {
        if (! $filePath || ! Str::startsWith($filePath, '/storage/committee-media/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($filePath, '/storage/'));
    }

    private function canAccessCommittee(Request $request, Committee $committee): bool
    {
        if ($request->user()?->official?->hasAnyRole([
            'Admin',
            'Punong Barangay',
            'Secretary',
            'Barangay Secretary',
            'Auditor',
            'Auditor / Inspector',
        ])) {
            return true;
        }

        $officialId = $request->user()?->official_id;

        if (! $officialId) {
            return false;
        }

        return $committee->assignments()
            ->where('official_id', $officialId)
            ->exists();
    }
}
