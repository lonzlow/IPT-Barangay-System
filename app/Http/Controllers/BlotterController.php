<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use Illuminate\Http\Request;

class BlotterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $blotters = Blotter::with('filedBy')->latest()->get();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'data' => $blotters->map(function (Blotter $blotter) {
                    return [
                        'id' => $blotter->id,
                        'case_number' => $blotter->case_number,
                        'complainant' => $blotter->complainant,
                        'respondent' => $blotter->respondent,
                        'incident_date' => optional($blotter->incident_date)->toIso8601String(),
                        'status' => $blotter->status,
                    ];
                }),
            ]);
        }

        return view('blotters.index', compact('blotters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('blotters.index');
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
    public function show(Request $request, Blotter $blotter)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $blotter->load('filedBy'),
            ]);
        }

        return redirect()->route('blotters.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Blotter $blotter)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $blotter,
            ]);
        }

        return redirect()->route('blotters.index');
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
