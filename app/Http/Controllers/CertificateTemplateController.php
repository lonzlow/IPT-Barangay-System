<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class CertificateTemplateController extends Controller
{
    /**
     * Display a listing of certificate templates
     */
    public function index()
    {
        $templates = DocumentTemplate::latest()->paginate(10);

        return view('certificate-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('certificate-templates.create');
    }

    /**
     * Store a newly created template in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:document_templates|max:100',
            'description' => 'nullable|string|max:500',
            'template_html' => 'required|string',
            'fields_required' => 'nullable|array',
            'validity_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        DocumentTemplate::create($validated);

        return redirect()
            ->route('certificate-templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Display the specified template
     */
    public function show(DocumentTemplate $certificateTemplate)
    {
        $certificateTemplate->load('documents');

        return view('certificate-templates.show', ['template' => $certificateTemplate]);
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(DocumentTemplate $certificateTemplate)
    {
        return view('certificate-templates.edit', ['template' => $certificateTemplate]);
    }

    /**
     * Update the specified template in storage
     */
    public function update(Request $request, DocumentTemplate $certificateTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:document_templates,name,' . $certificateTemplate->id,
            'description' => 'nullable|string|max:500',
            'template_html' => 'required|string',
            'fields_required' => 'nullable|array',
            'validity_days' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $certificateTemplate->update($validated);

        return redirect()
            ->route('certificate-templates.show', $certificateTemplate->id)
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Delete the specified template
     */
    public function destroy(DocumentTemplate $certificateTemplate)
    {
        // Prevent deletion if template has documents
        if ($certificateTemplate->documents()->count() > 0) {
            return redirect()
                ->route('certificate-templates.index')
                ->with('error', 'Cannot delete template that has issued documents!');
        }

        $certificateTemplate->delete();

        return redirect()
            ->route('certificate-templates.index')
            ->with('success', 'Template deleted successfully!');
    }
}
