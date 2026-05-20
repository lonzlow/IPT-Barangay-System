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
        $blotters = Blotter::with([
            'filedBy.official.resident',
            'complainant_resident',
            'respondents.respondent',
            'witnesses.resident_witness',
        ])->latest()->get();

        if ($request->expectsJson() || $request->wantsJson()) {

            $data = $blotters->map(function (Blotter $blotter) {

                $resident = optional(
                    optional($blotter->filedBy)->official
                )->resident;

                $filedByName = $resident
                    ? trim(
                        ($resident->first_name ?? '') . ' ' .
                        ($resident->last_name ?? '')
                    )
                    : 'N/A';

                $respondents = $blotter->respondents->map(function ($respondent) {

                    if ($respondent->respondent) {
                        return trim(
                            ($respondent->respondent->first_name ?? '') . ' ' .
                            ($respondent->respondent->last_name ?? '')
                        );
                    }

                    return $respondent->respondent_name;
                })->filter()->values();

                $witnesses = $blotter->witnesses->map(function ($witness) {

                    if ($witness->resident_witness) {
                        return trim(
                            ($witness->resident_witness->first_name ?? '') . ' ' .
                            ($witness->resident_witness->last_name ?? '')
                        );
                    }

                    return null;

                })->filter()->values();

                return [
                    'id' => $blotter->id,
                    'case_number' => $blotter->case_number,

                    'complainant_name' => $blotter->complainant_name,

                    'respondents' => $respondents,
                    'witnesses' => $witnesses,

                    'location' => $blotter->location,
                    'incident_description' => $blotter->incident_description,

                    'incident_date' => optional($blotter->incident_date)
                        ?->format('Y-m-d H:i:s'),

                    'status' => $blotter->status,

                    'filed_by_name' => $filedByName,
                ];
            });

            return response()->json([
                'data' => $data,
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
    public function show(Request $request, String $blotter)
    {
        $blotter = Blotter::with([
            'filedBy.official.resident',
            'complainant_resident',
            'respondents.respondent',
            'witnesses.resident_witness',
            'evidences',
        ])->findOrFail($blotter);

        // COMMENT TEMPORARILY, CAN BE USE LATER
        /* $blotter->load([
            'filedBy.official.resident',
            'respondents.respondent',
            'witnesses.resident_witness',
        ]);

        $resident = optional(
            optional($blotter->filedBy)->official
        )->resident;

        $filedByName = $resident
            ? trim(
                ($resident->first_name ?? '') . ' ' .
                ($resident->last_name ?? '')
            )
            : 'N/A';

        $respondents = $blotter->respondents->map(function ($respondent) {

            if ($respondent->respondent) {
                return trim(
                    ($respondent->respondent->first_name ?? '') . ' ' .
                    ($respondent->respondent->last_name ?? '')
                );
            }

            return $respondent->respondent_name;

        })->filter()->values();

        $witnesses = $blotter->witnesses->map(function ($witness) {

            if ($witness->resident_witness) {
                return trim(
                    ($witness->resident_witness->first_name ?? '') . ' ' .
                    ($witness->resident_witness->last_name ?? '')
                );
            }

            return null;

        })->filter()->values();

        if ($request->expectsJson() || $request->wantsJson()) {

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $blotter->id,
                    'case_number' => $blotter->case_number,

                    'complainant_name' => $blotter->complainant_name,

                    'respondents' => $respondents,
                    'witnesses' => $witnesses,

                    'location' => $blotter->location,
                    'incident_description' => $blotter->incident_description,
                    'incident_date' => $blotter->incident_date,
                    'status' => $blotter->status,

                    'filed_by_name' => $filedByName,
                ]
            ]);
        } */

        return view('blotters.info', compact('blotter'));
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
