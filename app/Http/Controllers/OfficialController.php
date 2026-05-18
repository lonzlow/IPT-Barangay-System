<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Models\Resident;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $officials = Official::with('resident')->paginate(15);
        return view('officials.index', compact('officials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $residents = Resident::all();
        return view('officials.create', compact('residents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'position' => 'required|string',
            'term_start' => 'required|date',
            'term_end' => 'nullable|date|after:term_start',
            'is_active' => 'boolean',
        ]);

        $official = Official::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Official created successfully',
                'data' => $official->load('resident')
            ], 201);
        }

        return redirect()->route('officials.index')->with('success', 'Official created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Official $official)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $official->load('resident', 'assignments')
            ]);
        }
        $official->load('resident', 'assignments');
        return view('officials.show', compact('official'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Official $official)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $official->load('resident')
            ]);
        }
        $residents = Resident::all();
        return view('officials.edit', compact('official', 'residents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Official $official)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'position' => 'required|string',
            'term_start' => 'required|date',
            'term_end' => 'nullable|date|after:term_start',
            'is_active' => 'boolean',
        ]);

        $official->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Official updated successfully',
                'data' => $official->load('resident')
            ], 200);
        }

        return redirect()->route('officials.show', $official)->with('success', 'Official updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Official $official, Request $request)
    {
        $official->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Official deleted successfully'
            ], 200);
        }

        return redirect()->route('officials.index')->with('success', 'Official deleted successfully.');
    }
}
