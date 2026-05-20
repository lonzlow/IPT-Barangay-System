<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businesses = Business::with('business_owners.resident')->get();
        return view('businesses.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('businesses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'date_established' => 'required|date',
            'status' => 'required|in:active,inactive',
        ]);

        $business = Business::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => $business], 201);
        }
        return redirect()->route('businesses.index')->with('success', 'Business created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::findOrFail($id);
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'data' => $business]);
        }
        return view('businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $business = Business::findOrFail($id);
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'data' => $business]);
        }
        return view('businesses.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $business->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => $business]);
        }
        return redirect()->route('businesses.index')->with('success', 'Business updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $business = Business::findOrFail($id);
        $business->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Business deleted successfully']);
        }
        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully');
    }
}
