<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Official;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $committees = Committee::with('headOfficial', 'records')->paginate(15);
        return view('committees.index', compact('committees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $officials = Official::where('is_active', true)->get();
        return view('committees.create', compact('officials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:committees',
            'head_official_id' => 'nullable|exists:officials,id',
            'description' => 'nullable|string',
        ]);

        $committee = Committee::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee created successfully',
                'data' => $committee->load('headOfficial')
            ], 201);
        }

        return redirect()->route('committees.index')->with('success', 'Committee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Committee $committee)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $committee->load('headOfficial', 'records', 'assignments')
            ]);
        }
        $committee->load('headOfficial', 'records', 'assignments');
        return view('committees.show', compact('committee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Committee $committee)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $committee->load('headOfficial')
            ]);
        }
        $officials = Official::where('is_active', true)->get();
        return view('committees.edit', compact('committee', 'officials'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Committee $committee)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:committees,name,' . $committee->id,
            'head_official_id' => 'nullable|exists:officials,id',
            'description' => 'nullable|string',
        ]);

        $committee->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee updated successfully',
                'data' => $committee->load('headOfficial')
            ], 200);
        }

        return redirect()->route('committees.show', $committee)->with('success', 'Committee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Committee $committee, Request $request)
    {
        $committee->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee deleted successfully'
            ], 200);
        }

        return redirect()->route('committees.index')->with('success', 'Committee deleted successfully.');
    }
}
