<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Official;
use App\Models\Resident;
use App\Models\Signature;
use App\Support\LogsDocumentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class DocumentController extends Controller
{
    use LogsDocumentActivity;

    public function index()
    {
        $templates = DocumentTemplate::where('is_active', true)->orderBy('name')->get();
        $residents = Resident::with('household.purok')
            ->where('residency_status', 'Active')
            ->orderBy('last_name')
            ->get();

        $signatures = Signature::with('official.resident')->latest()->get();

        $officials = Official::with(['resident', 'role'])
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($o) => $o->resident?->last_name);

        $auditLogs = ActivityLog::with('user')
            ->where('module', 'Document Issuance')
            ->latest()
            ->limit(20)
            ->get();

        $monthStart = now()->startOfMonth();

        $stats = [
            'issued_this_month' => Document::where('status', 'Issued')->where('issued_date', '>=', $monthStart)->count(),
            'ready_to_print' => Document::where('status', 'Issued')->count(),
            'revoked_today' => Document::where('status', 'Revoked')->whereDate('updated_at', today())->count(),
            'total_issued' => Document::count(),
        ];

        $templateStats = Document::query()
            ->selectRaw('document_template_id, count(*) as total')
            ->where('issued_date', '>=', $monthStart)
            ->groupBy('document_template_id')
            ->pluck('total', 'document_template_id');

        return view('documents.index', compact(
            'templates',
            'residents',
            'stats',
            'templateStats',
            'signatures',
            'officials',
            'auditLogs',
        ));
    }

    public function auditLogs(): JsonResponse
    {
        $this->authorize('documents.view');

        $logs = ActivityLog::with('user')
            ->where('module', 'Document Issuance')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->email ?? 'System',
                'created_at' => $log->created_at->format('M d, Y g:i A'),
                'created_at_human' => $log->created_at->diffForHumans(),
            ]);

        return response()->json($logs->values()->all());
    }

    public function create()
    {
        $residents = Resident::where('residency_status', 'Active')->orderBy('last_name')->get();
        $templates = DocumentTemplate::where('is_active', true)->orderBy('name')->get();
        $signatures = Signature::with('official.resident')->get();

        return view('documents.create', compact('residents', 'templates', 'signatures'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedDocumentData($request);
        $resident = $this->residentForDocument($validated['resident_id']);
        $template = DocumentTemplate::findOrFail($validated['document_template_id']);
        $business = $this->validatedBusiness($template, $resident, $validated['business_id'] ?? null);
        $referenceNumber = Document::generateReferenceNumber($this->referencePrefix($template));
        $issuedDate = now();
        $validUntil = $this->validUntil($template, $issuedDate);

        $signature = isset($validated['signature_id']) ? Signature::find($validated['signature_id']) : null;
        if ($signature && empty($validated['issued_by_official_id'])) {
            $validated['issued_by_official_id'] = $signature->official_id;
        }

        $renderedHtml = $template->render($resident, [
            'reference_number' => $referenceNumber,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'issued_by' => $validated['issued_by'],
            'issued_date' => $issuedDate,
            'signature_url' => $this->signatureRenderUrl($signature),
            'business' => $business,
        ]);

        $document = Document::create([
            'resident_id' => $resident->id,
            'document_template_id' => $template->id,
            'business_id' => $business?->id,
            'reference_number' => $referenceNumber,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'rendered_html' => $renderedHtml,
            'issued_by' => $validated['issued_by'],
            'issued_by_official_id' => $validated['issued_by_official_id'] ?? ($signature?->official_id ?? null),
            'signature_id' => $signature?->id,
            'signature_path' => $signature?->path,
            'issued_date' => $issuedDate->toDateString(),
            'valid_until' => $validUntil,
            'status' => 'Issued',
            'seal_path' => 'images/logo/Barangay New Era Logo.jpg',
        ]);

        $document->load(['resident', 'template', 'business']);
        $this->logDocumentActivity('Issued Document', $document);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Document issued successfully!',
                'document' => $document,
            ], 201);
        }

        return redirect()->route('documents.show', $document)->with('success', 'Document issued successfully!');
    }

    public function show(Request $request, Document $document)
    {
        $document->load(['resident.household.purok', 'template', 'business']);
        $document->loadMissing('signature.official.resident');

        $residents = Resident::with('household.purok')
            ->where('residency_status', 'Active')
            ->orderBy('last_name')
            ->get();

        if ($document->resident && !$residents->contains('id', $document->resident_id)) {
            $residents->prepend($document->resident);
        }

        $templates = DocumentTemplate::where('is_active', true)->orderBy('name')->get();
        $signatures = Signature::with('official.resident')->get();

        if ($document->template && !$templates->contains('id', $document->document_template_id)) {
            $templates->prepend($document->template);
        }

        if ($request->expectsJson()) {
            return response()->json($document);
        }

        return view('documents.show', compact('document', 'residents', 'templates', 'signatures'));
    }

    public function edit(Document $document)
    {
        $residents = Resident::where('residency_status', 'Active')->orderBy('last_name')->get();
        $templates = DocumentTemplate::where('is_active', true)->orderBy('name')->get();
        $signatures = Signature::with('official.resident')->get();

        return view('documents.edit', compact('document', 'residents', 'templates', 'signatures'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $this->validatedDocumentData($request, true);
        $resident = $this->residentForDocument($validated['resident_id']);
        $template = DocumentTemplate::findOrFail($validated['document_template_id']);
        $business = $this->validatedBusiness($template, $resident, $validated['business_id'] ?? null);
        $issuedDate = $document->issued_date ?? now();
        $validUntil = $this->validUntil($template, $issuedDate);

        $signature = isset($validated['signature_id']) ? Signature::find($validated['signature_id']) : null;
        if ($signature && empty($validated['issued_by_official_id'])) {
            $validated['issued_by_official_id'] = $signature->official_id;
        }

        $renderedHtml = $template->render($resident, [
            'reference_number' => $document->reference_number,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'issued_by' => $validated['issued_by'],
            'issued_date' => $issuedDate,
            'signature_url' => $this->signatureRenderUrl($signature),
            'business' => $business,
        ]);

        $previousStatus = $document->status;

        $document->update([
            'resident_id' => $resident->id,
            'document_template_id' => $template->id,
            'business_id' => $business?->id,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'issued_by' => $validated['issued_by'],
            'issued_by_official_id' => $validated['issued_by_official_id'] ?? ($signature?->official_id ?? $document->issued_by_official_id),
            'signature_id' => $signature?->id,
            'signature_path' => $signature?->path,
            'status' => $validated['status'],
            'rendered_html' => $renderedHtml,
            'valid_until' => $validUntil,
            'seal_path' => $document->seal_path ?: 'images/logo/Barangay New Era Logo.jpg',
        ]);

        $document->load(['resident', 'template', 'business']);
        $extra = $previousStatus !== $document->status
            ? "Status {$previousStatus} → {$document->status}"
            : null;
        $this->logDocumentActivity('Updated Document', $document, $extra);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Document updated successfully!',
                'document' => $document,
            ]);
        }

        return redirect()->route('documents.show', $document)->with('success', 'Document updated successfully!');
    }

    public function destroy(Request $request, Document $document)
    {
        $document->load(['resident', 'template']);
        $this->logDocumentActivity('Deleted Document', $document);
        $document->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Document deleted successfully!']);
        }

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully!');
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $this->validatedDocumentData($request, false, true);
        $resident = $this->residentForDocument($validated['resident_id']);
        $template = DocumentTemplate::findOrFail($validated['document_template_id']);
        $business = $this->validatedBusiness($template, $resident, $validated['business_id'] ?? null);
        $issuedDate = now();
        $referenceNumber = Document::generateReferenceNumber($this->referencePrefix($template));
        $validUntil = $this->validUntil($template, $issuedDate);
        $issuedBy = $validated['issued_by'] ?? 'Barangay Official';

        $signature = isset($validated['signature_id']) ? Signature::find($validated['signature_id']) : null;

        $document = new Document([
            'resident_id' => $resident->id,
            'document_template_id' => $template->id,
            'business_id' => $business?->id,
            'reference_number' => $referenceNumber,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'issued_by' => $issuedBy,
            'issued_date' => $issuedDate->toDateString(),
            'valid_until' => $validUntil,
            'status' => 'Issued',
            'seal_path' => 'images/logo/Barangay New Era Logo.jpg',
        ]);

        $document->rendered_html = $template->render($resident, [
            'reference_number' => $referenceNumber,
            'purpose' => $validated['purpose'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'issued_by' => $issuedBy,
            'issued_date' => $issuedDate,
            'signature_url' => $this->signatureRenderUrl($signature),
            'business' => $business,
        ]);

        $document->setRelation('resident', $resident);
        $document->setRelation('signature', $signature);
        $document->setRelation('template', $template);
        $document->setRelation('business', $business);

        return response()->json([
            'html' => view('documents.partials.print-document', compact('document'))->render(),
            'reference_number_preview' => $referenceNumber,
            'valid_until' => $validUntil?->format('Y-m-d'),
        ]);
    }

    public function exportPdf(Document $document)
    {
        $document->load(['resident.household.purok', 'template', 'business', 'signature', 'signature.official']);

        $signature = $document->signature;
        $rendered = $document->template->render($document->resident, [
            'reference_number' => $document->reference_number,
            'purpose' => $document->purpose,
            'additional_notes' => $document->additional_notes,
            'issued_by' => $document->issued_by,
            'issued_date' => $document->issued_date,
            'signature_url' => $this->signatureRenderUrl($signature, true),
            'business' => $document->business,
        ]);

        $document->rendered_html = $rendered;

        return Pdf::loadView('documents.print', compact('document'))
            ->setPaper('a4')
            ->stream('document-' . $document->reference_number . '.pdf');
    }

    public function downloadPdf(Document $document)
    {
        return $this->exportPdf($document);
    }

    public function getResidentDocuments(Resident $resident): JsonResponse
    {
        $documents = Document::where('resident_id', $resident->id)
            ->with(['template', 'business'])
            ->latest()
            ->get();

        return response()->json($documents);
    }

    public function residentBusinesses(Resident $resident): JsonResponse
    {
        $businesses = Business::query()
            ->where('status', 'Active')
            ->whereHas('business_owners', fn ($query) => $query->where('resident_id', $resident->id))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'business_type', 'business_address', 'status']);

        return response()->json($businesses);
    }

    public function byTemplate($templateId)
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $documents = Document::where('document_template_id', $templateId)
            ->with(['resident', 'business'])
            ->latest()
            ->paginate(10);

        return view('documents.by-template', compact('template', 'documents'));
    }

    public function data()
    {
        $documents = Document::with(['resident', 'template', 'business'])->latest();

        if ($residentId = request('resident_id')) {
            $documents->where('resident_id', $residentId);
        }

        if ($templateId = request('document_template_id')) {
            $documents->where('document_template_id', $templateId);
        }

        return DataTables::of($documents)
            ->addColumn('resident_name', fn ($document) => trim($document->resident->first_name . ' ' . $document->resident->last_name))
            ->addColumn('template_name', fn ($document) => $document->template->name ?? 'N/A')
            ->addColumn('business_name', fn ($document) => $document->business->business_name ?? 'N/A')
            ->addColumn('issued_date_formatted', fn ($document) => $document->issued_date->format('M d, Y'))
            ->addColumn('valid_until_formatted', fn ($document) => $document->valid_until ? $document->valid_until->format('M d, Y') : 'No expiration')
            ->addColumn('status_badge', function ($document) {
                $statusClass = match ($document->status) {
                    'Issued' => 'success',
                    'Revoked' => 'danger',
                    'Expired' => 'warning',
                    default => 'secondary',
                };

                return '<span class="badge bg-' . $statusClass . '">' . e($document->status) . '</span>';
            })
            ->addColumn('action', fn ($document) => view('documents.partials.actions', compact('document'))->render())
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    private function signatureRenderUrl(?Signature $signature, bool $forPdf = false): string
    {
        if (!$signature) {
            return '';
        }

        if ($forPdf) {
            return $signature->absolutePath() ?? '';
        }

        return $signature->publicUrl();
    }

    private function validatedDocumentData(Request $request, bool $isUpdate = false, bool $isPreview = false): array
    {
        $request->merge([
            'signature_id' => $request->input('signature_id') ?: null,
            'business_id' => $request->input('business_id') ?: null,
            'issued_by_official_id' => $request->input('issued_by_official_id') ?: null,
        ]);

        return $request->validate([
            'resident_id' => ['required', 'uuid', 'exists:residents,id'],
            'document_template_id' => ['required', 'uuid', 'exists:document_templates,id'],
            'business_id' => ['nullable', 'uuid', 'exists:businesses,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'issued_by' => [$isPreview ? 'nullable' : 'required', 'string', 'max:100'],
            'issued_by_official_id' => ['nullable', 'uuid', 'exists:officials,id'],
            'signature_id' => ['nullable', 'uuid', 'exists:signatures,id'],
            'status' => [Rule::requiredIf($isUpdate), 'nullable', Rule::in(['Issued', 'Revoked', 'Expired'])],
        ]);
    }

    private function residentForDocument(string $residentId): Resident
    {
        return Resident::with(['household.purok', 'business_owner.businesses'])->findOrFail($residentId);
    }

    private function validatedBusiness(DocumentTemplate $template, Resident $resident, ?string $businessId): ?Business
    {
        if (!$this->isBusinessClearance($template)) {
            return null;
        }

        if (!$businessId) {
            throw ValidationException::withMessages([
                'business_id' => 'Please select an active business for Business Clearance.',
            ]);
        }

        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->whereHas('business_owners', fn ($query) => $query->where('resident_id', $resident->id))
            ->first();

        if (!$business) {
            throw ValidationException::withMessages([
                'business_id' => 'The selected business must be active and linked to the selected resident.',
            ]);
        }

        return $business;
    }

    private function isBusinessClearance(DocumentTemplate $template): bool
    {
        return str_contains(strtolower($template->name), 'business');
    }

    private function referencePrefix(DocumentTemplate $template): string
    {
        $name = strtolower($template->name);

        return match (true) {
            str_contains($name, 'indigency') => 'IND',
            str_contains($name, 'residency') => 'RES',
            str_contains($name, 'good moral') => 'GMC',
            str_contains($name, 'business') => 'BUS',
            str_contains($name, 'clearance') => 'CLR',
            default => 'DOC',
        };
    }

    private function validUntil(DocumentTemplate $template, $issuedDate)
    {
        if (!$template->validity_days) {
            return null;
        }

        return $issuedDate->copy()->addDays($template->validity_days);
    }
}
