<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Http\Request;

class PurokController extends Controller
{
    /**
     * Display all puroks with statistics
     */
    public function index()
    {
        $this->authorize('households.view');

        $puroks = Purok::with([
                'leader',
                'households.residents:id,household_id,voter_status',
            ])
            ->withCount('households')
            ->paginate(15);

        $puroks->getCollection()->transform(function (Purok $purok) {
            $residents = $purok->households->flatMap->residents;
            $purok->residents_count = $residents->count();
            $purok->registered_voters_count = $residents->where('voter_status', 'Registered')->count();

            return $purok;
        });

        $statistics = [
            'total_puroks' => Purok::count(),
            'total_households' => \App\Models\Household::count(),
        ];

        return view('puroks.index', compact('puroks', 'statistics'));
    }

    /**
     * Show form to create new purok
     */
    public function create()
    {
        $this->authorize('households.manage');

        $residents = $this->leaderOptions();

        return view('puroks.create', compact('residents'));
    }

    /**
     * Store new purok
     */
    public function store(Request $request)
    {
        $this->authorize('households.manage');

        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name',
            'description' => 'nullable|string',
            'leader_id' => ['nullable', 'uuid', 'exists:residents,id'],
        ]);

        Purok::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Purok created successfully.',
            ], 201);
        }

        return redirect()->route('puroks.index')
            ->with('success', 'Purok created successfully.');
    }

    /**
     * Display specific purok
     */
    public function show(Purok $purok)
    {
        $this->authorize('households.view');

        $purok->load([
            'leader',
            'households.head_resident',
            'households.residents' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
        ]);

        $householdCount = $purok->households()->count();
        $residents = $purok->households->flatMap->residents;
        $residentCount = $residents->count();
        $voterCount = $residents->where('voter_status', 'Registered')->count();

        return view('puroks.show', compact('purok', 'householdCount', 'residentCount', 'voterCount'));
    }

    /**
     * Show form to edit purok
     */
    public function edit(Purok $purok)
    {
        $this->authorize('households.manage');

        $residents = $this->leaderOptions();
        $householdCount = $purok->households()->count();

        return view('puroks.edit', compact('purok', 'residents', 'householdCount'));
    }

    /**
     * Update purok
     */
    public function update(Request $request, Purok $purok)
    {
        $this->authorize('households.manage');

        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name,' . $purok->id,
            'description' => 'nullable|string',
            'leader_id' => ['nullable', 'uuid', 'exists:residents,id'],
        ]);

        $purok->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Purok updated successfully.',
            ]);
        }

        return redirect()->route('puroks.show', $purok)
            ->with('success', 'Purok updated successfully.');
    }

    /**
     * Delete purok
     */
    public function destroy(Purok $purok)
    {
        $this->authorize('households.delete');

        // Check if purok has households
        if ($purok->households()->count() > 0) {
            $message = 'Cannot delete purok with households. Please reassign households first.';

            if (request()->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('puroks.index')
                ->with('error', $message);
        }

        $purok->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Purok deleted successfully.']);
        }

        return redirect()->route('puroks.index')
            ->with('success', 'Purok deleted successfully.');
    }

    private function leaderOptions()
    {
        return Resident::query()
            ->with('household.purok')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
