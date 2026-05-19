<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use Illuminate\Http\Request;

class BlotterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blotters = Blotter::with('filedBy')->paginate(15);
        return view('blotters.index', compact('blotters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blotters.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_number' => 'required|string|unique:blotters',
            'complainant' => 'required|string',
            'respondent' => 'required|string',
            'incident_description' => 'required|string',
            'incident_date' => 'required|date',
            'status' => 'required|in:open,ongoing,resolved,referred',
            'parties' => 'nullable|json',
        ]);

        $validated['filed_by'] = auth()->id();
        $blotter = Blotter::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter created successfully',
                'data' => $blotter->load('filedBy')
            ], 201);
        }

        return redirect()->route('blotters.index')->with('success', 'Blotter created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blotter $blotter)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $blotter->load('filedBy')
            ]);
        }
        return view('blotters.show', compact('blotter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blotter $blotter)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $blotter
            ]);
        }
        return view('blotters.edit', compact('blotter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blotter $blotter)
    {
        $validated = $request->validate([
            'case_number' => 'required|string|unique:blotters,case_number,' . $blotter->id,
            'complainant' => 'required|string',
            'respondent' => 'required|string',
            'incident_description' => 'required|string',
            'incident_date' => 'required|date',
            'status' => 'required|in:open,ongoing,resolved,referred',
            'parties' => 'nullable|json',
        ]);

        $blotter->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter updated successfully',
                'data' => $blotter->load('filedBy')
            ], 200);
        }

        return redirect()->route('blotters.show', $blotter)->with('success', 'Blotter updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blotter $blotter, Request $request)
    {
        $blotter->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter deleted successfully'
            ], 200);
        }

        return redirect()->route('blotters.index')->with('success', 'Blotter deleted successfully.');
    }
}
