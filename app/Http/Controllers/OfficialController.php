<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\Official;
use App\Models\OfficialAssignment;
use App\Models\Resident;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfficialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('officials.view');

        $officials = Official::with(['resident', 'role', 'user', 'assignments.committee'])
            ->orderByDesc('is_active')
            ->orderBy('term_start')
            ->get();

        $roles = Role::orderBy('role_name')->get();
        $residents = Resident::orderBy('last_name')->orderBy('first_name')->get();
        $committees = Committee::orderBy('name')->get();

        $activeOfficials = $officials->where('is_active', true);
        $currentTerm = $this->currentTermSummary($activeOfficials);

        $roleBreakdown = $roles->map(function (Role $role) use ($officials) {
            return [
                'label' => $role->role_name,
                'count' => $officials->where('role_id', $role->id)->count(),
            ];
        })->filter(fn (array $item) => $item['count'] > 0)->values();

        $termTrackers = $roleBreakdown->map(function (array $item) use ($officials) {
            $roleOfficials = $officials->filter(fn (Official $official) => $official->role?->role_name === $item['label']);
            $activeCount = $roleOfficials->where('is_active', true)->count();
            $percentage = $item['count'] > 0 ? round(($activeCount / $item['count']) * 100) : 0;

            return [
                'label' => $item['label'] . ($item['count'] > 1 ? ' (' . $item['count'] . ')' : ''),
                'percentage' => $percentage,
            ];
        })->take(6)->values();

        $summary = [
            'total' => $officials->count(),
            'kagawads' => $officials->filter(fn (Official $official) => Str::contains(Str::lower($official->role?->role_name ?? ''), 'kagawad'))->count(),
            'term_years' => $currentTerm['years'],
            'term_months_left' => $currentTerm['months_left'],
            'term_label' => $currentTerm['label'],
            'term_progress' => $currentTerm['progress'],
        ];

        $idPreviewOfficial = $officials->first();

        return view('officials.index', compact(
            'officials',
            'roles',
            'residents',
            'committees',
            'summary',
            'roleBreakdown',
            'termTrackers',
            'idPreviewOfficial'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('officials.manage');

        $residents = Resident::all();
        return view('officials.create', compact('residents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('officials.manage');

        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'role_id' => 'required|exists:roles,id',
            'official_number' => 'nullable|string|max:50|unique:officials,official_number',
            'term_start' => 'required|date',
            'term_end' => 'nullable|date|after:term_start',
            'is_active' => 'boolean',
        ]);

        $validated['official_number'] = $validated['official_number'] ?: $this->nextOfficialNumber();
        $validated['is_active'] = $request->boolean('is_active');

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
        $this->authorize('officials.view');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $official->load('resident', 'role', 'user', 'assignments.committee')
            ]);
        }
        $official->load('resident', 'role', 'user', 'assignments.committee');
        return view('officials.show', compact('official'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Official $official)
    {
        $this->authorize('officials.manage');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $official->load('resident', 'role', 'assignments.committee')
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
        $this->authorize('officials.manage');

        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'role_id' => 'required|exists:roles,id',
            'official_number' => 'nullable|string|max:50|unique:officials,official_number,' . $official->id,
            'term_start' => 'required|date',
            'term_end' => 'nullable|date|after:term_start',
            'is_active' => 'boolean',
        ]);

        $validated['official_number'] = $validated['official_number'] ?: $official->official_number;
        $validated['is_active'] = $request->boolean('is_active');

        $official->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Official updated successfully',
                'data' => $official->load('resident', 'role')
            ], 200);
        }

        return redirect()->route('officials.show', $official)->with('success', 'Official updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Official $official, Request $request)
    {
        $this->authorize('officials.manage');

        $official->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Official deleted successfully'
            ], 200);
        }

        return redirect()->route('officials.index')->with('success', 'Official deleted successfully.');
    }

    public function assignDesignation(Request $request)
    {
        $this->authorize('committees.manage');

        $validated = $request->validate([
            'official_id' => [
                'required',
                Rule::exists('officials', 'id')->where('is_active', true),
            ],
            'committee_id' => 'required|exists:committees,id',
            'designation' => 'required|string|max:120',
        ]);

        $committee = Committee::findOrFail($validated['committee_id']);

        $assignment = OfficialAssignment::updateOrCreate(
            [
                'official_id' => $validated['official_id'],
                'committee_id' => $validated['committee_id'],
            ],
            ['designation' => $validated['designation']]
        );

        if (Str::contains(Str::lower($validated['designation']), 'chair')) {
            OfficialAssignment::query()
                ->where('committee_id', $validated['committee_id'])
                ->where('official_id', '!=', $validated['official_id'])
                ->get()
                ->each(function (OfficialAssignment $existingAssignment) {
                    if (Str::contains(Str::lower($existingAssignment->designation), 'chair')) {
                        $existingAssignment->update(['designation' => 'Member']);
                    }
                });

            $committee->update(['chairperson_id' => $validated['official_id']]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Designation assigned successfully',
                'data' => $assignment->load('official.resident', 'committee'),
            ]);
        }

        return redirect()->route('officials.index')->with('success', 'Designation assigned successfully.');
    }

    private function nextOfficialNumber(): string
    {
        return 'BRG-OFF-' . now()->format('Y') . '-' . str_pad((string) (Official::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    private function currentTermSummary($officials): array
    {
        $withTerms = $officials->filter(fn (Official $official) => $official->term_start && $official->term_end);

        if ($withTerms->isEmpty()) {
            return [
                'years' => 0,
                'months_left' => 0,
                'label' => 'Appointive',
                'progress' => 0,
            ];
        }

        $start = Carbon::parse($withTerms->min('term_start'));
        $end = Carbon::parse($withTerms->max('term_end'));
        $now = now();
        $totalDays = max(1, $start->diffInDays($end));
        $elapsedDays = $now->lessThan($start) ? 0 : min($totalDays, $start->diffInDays($now));

        return [
            'years' => max(1, (int) round($start->diffInMonths($end) / 12)),
            'months_left' => $now->greaterThan($end) ? 0 : (int) ceil($now->diffInDays($end) / 30),
            'label' => $start->format('Y') . '-' . $end->format('Y'),
            'progress' => (int) round(($elapsedDays / $totalDays) * 100),
        ];
    }
}
