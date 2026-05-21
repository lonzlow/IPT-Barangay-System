<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeRecord;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $committees = Committee::query()
            ->with(['headOfficial.resident', 'headOfficial.role'])
            ->withCount([
                'records',
                'records as media_count' => fn ($query) => $query->whereIn('record_type', ['photo', 'video']),
                'records as report_count' => fn ($query) => $query->whereIn('record_type', ['report', 'blotter_incident', 'resolution_policy', 'project_proposal', 'financial_record', 'permit_contract']),
                'records as activity_count' => fn ($query) => $query->whereIn('record_type', ['activity', 'training_seminar']),
            ])
            ->orderBy('id')
            ->paginate(12);

        $chairmanCandidates = $this->chairmanCandidates();
        $recordTypes = CommitteeRecord::TYPES;

        return view('committees.index', compact('committees', 'chairmanCandidates', 'recordTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $officials = $this->chairmanCandidates();
        return view('committees.create', compact('officials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('committees.manage');

        $validated = $request->validate([
            'name' => 'required|string|unique:committees',
            'slug' => 'nullable|string|max:120|unique:committees,slug',
            'chairperson_id' => [
                'nullable',
                Rule::exists('officials', 'id')->where('role_id', 5)->where('is_active', true),
            ],
            'chair_label' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'allowed_record_types' => 'nullable|array',
            'allowed_record_types.*' => ['string', Rule::in(array_keys(CommitteeRecord::TYPES))],
        ]);

        $committee = Committee::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee created successfully',
                'data' => $committee->load('headOfficial.resident')
            ], 201);
        }

        return redirect()->route('committees.index')->with('success', 'Committee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Committee $committee)
    {
        $committee->load([
            'headOfficial.resident',
            'headOfficial.role',
            'assignments.official.resident',
            'records.uploadedBy.official.resident',
        ]);

        $recordGroups = [
            'media' => $committee->records->whereIn('record_type', ['photo', 'video']),
            'activities' => $committee->records->whereIn('record_type', ['activity', 'training_seminar']),
            'accomplishments' => $committee->records->where('record_type', 'accomplishment'),
            'reports' => $committee->records->whereIn('record_type', ['report', 'blotter_incident', 'resolution_policy', 'project_proposal', 'financial_record', 'permit_contract', 'emergency_log', 'evacuation_center_record']),
            'attendance' => $committee->records->where('record_type', 'attendance'),
            'inventory' => $committee->records->whereIn('record_type', ['inventory', 'personnel_list', 'driver_operator_profile']),
            'partnerships' => $committee->records->whereIn('record_type', ['partnership', 'certificate']),
        ];

        $recordTypes = $committee->recordTypeLabels();
        $allRecordTypes = CommitteeRecord::TYPES;
        $chairmanCandidates = $this->chairmanCandidates();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $committee,
            ]);
        }

        return view('committees.show', compact('committee', 'recordGroups', 'recordTypes', 'allRecordTypes', 'chairmanCandidates'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Committee $committee)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $committee->load('headOfficial.resident')
            ]);
        }
        $officials = $this->chairmanCandidates();
        return view('committees.edit', compact('committee', 'officials'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Committee $committee)
    {
        $this->authorize('committees.manage');

        $validated = $request->validate([
            'name' => 'required|string|unique:committees,name,' . $committee->id,
            'slug' => 'nullable|string|max:120|unique:committees,slug,' . $committee->id,
            'chairperson_id' => [
                'nullable',
                Rule::exists('officials', 'id')->where('role_id', 5)->where('is_active', true),
            ],
            'chair_label' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'allowed_record_types' => 'nullable|array',
            'allowed_record_types.*' => ['string', Rule::in(array_keys(CommitteeRecord::TYPES))],
        ]);

        $committee->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee updated successfully',
                'data' => $committee->load('headOfficial.resident')
            ], 200);
        }

        return redirect()->route('committees.show', $committee)->with('success', 'Committee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Committee $committee, Request $request)
    {
        $this->authorize('committees.manage');

        $committee->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Committee deleted successfully'
            ], 200);
        }

        return redirect()->route('committees.index')->with('success', 'Committee deleted successfully.');
    }

    private function chairmanCandidates()
    {
        return Official::query()
            ->with(['resident', 'role'])
            ->where('is_active', true)
            ->where('role_id', 5)
            ->get()
            ->sortBy(fn (Official $official) => $official->resident?->last_name . ', ' . $official->resident?->first_name)
            ->values();
    }
}
