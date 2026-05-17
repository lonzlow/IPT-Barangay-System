<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Resident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index()
    {
        $documents = Document::with(['resident', 'template'])
            ->latest()
            ->paginate(10);

        $templates = DocumentTemplate::where('is_active', true)->get();

        return view('documents.index', compact('documents', 'templates'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        $residents = Resident::where('residency_status', 'Active')
            ->orderBy('last_name')
            ->get();

        $templates = DocumentTemplate::where('is_active', true)->get();

        return view('documents.create', compact('residents', 'templates'));
    }

    /**
     * Store a newly created document in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resident_id' => 'required|uuid|exists:residents,id',
            'document_template_id' => 'required|uuid|exists:document_templates,id',
            'purpose' => 'nullable|string|max:255',
            'issued_by' => 'required|string|max:100',
        ]);

        $resident = Resident::findOrFail($validated['resident_id']);
        $template = DocumentTemplate::findOrFail($validated['document_template_id']);

        // Render the template with resident data
        $renderedHtml = $template->render($resident);

        // Generate reference number (can customize based on template type)
        $referenceNumber = Document::generateReferenceNumber('DOC');

        // Calculate valid_until date if template has validity_days
        $validUntil = null;
        if ($template->validity_days) {
            $validUntil = now()->addDays($template->validity_days)->toDateString();
        }

        $document = Document::create([
            'resident_id' => $validated['resident_id'],
            'document_template_id' => $validated['document_template_id'],
            'reference_number' => $referenceNumber,
            'purpose' => $validated['purpose'],
            'rendered_html' => $renderedHtml,
            'issued_by' => $validated['issued_by'],
            'issued_date' => now()->toDateString(),
            'valid_until' => $validUntil,
            'status' => 'Issued',
        ]);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Document issued successfully!');
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        $document->load(['resident', 'template']);

        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        $residents = Resident::where('residency_status', 'Active')->orderBy('last_name')->get();
        $templates = DocumentTemplate::where('is_active', true)->get();

        return view('documents.edit', compact('document', 'residents', 'templates'));
    }

    /**
     * Update the specified document in storage
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:255',
            'issued_by' => 'required|string|max:100',
            'status' => 'required|in:Issued,Revoked,Expired',
        ]);

        $document->update($validated);

        return redirect()
            ->route('documents.show', $document->id)
            ->with('success', 'Document updated successfully!');
    }

    /**
     * Delete the specified document
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully!');
    }

    /**
     * Export document as PDF
     */
    public function exportPdf(Document $document)
    {
        $document->load(['resident', 'template']);

        $html = view('documents.print', compact('document'))->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->stream('document-' . $document->reference_number . '.pdf');
    }

    /**
     * Download document as PDF for printing
     */
    public function downloadPdf(Document $document)
    {
        return $this->exportPdf($document);
    }

    /**
     * Get documents by resident (API endpoint for resident detail page)
     */
    public function getResidentDocuments($residentId)
    {
        $documents = Document::where('resident_id', $residentId)
            ->with('template')
            ->latest()
            ->get();

        return response()->json($documents);
    }

    /**
     * List documents by template type
     */
    public function byTemplate($templateId)
    {
        $template = DocumentTemplate::findOrFail($templateId);
        $documents = Document::where('document_template_id', $templateId)
            ->with('resident')
            ->latest()
            ->paginate(10);

        return view('documents.by-template', compact('template', 'documents'));
    }
}
