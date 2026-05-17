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
        $puroks = Purok::with('leader', 'households')
            ->withCount('households')
            ->paginate(15);

        $statistics = [
            'total_puroks' => Purok::count(),
            'total_households' => 0, // Loaded dynamically in view
        ];

        return view('puroks.index', compact('puroks', 'statistics'));
    }

    /**
     * Show form to create new purok
     */
    public function create()
    {
        $residents = Resident::orderBy('first_name')->get();

        return view('puroks.create', compact('residents'));
    }

    /**
     * Store new purok
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name',
            'description' => 'nullable|string',
            'leader_id' => 'nullable|uuid|exists:residents,id',
        ]);

        Purok::create($validated);

        return redirect()->route('puroks.index')
            ->with('success', 'Purok created successfully.');
    }

    /**
     * Display specific purok
     */
    public function show(Purok $purok)
    {
        $purok->load('leader', 'households');
        $householdCount = $purok->households()->count();
        $residentCount = Resident::whereIn('household_id', $purok->households()->pluck('id'))->count();
        $voterCount = Resident::whereIn('household_id', $purok->households()->pluck('id'))
            ->where('voter_status', 'Registered Voter')
            ->count();

        return view('puroks.show', compact('purok', 'householdCount', 'residentCount', 'voterCount'));
    }

    /**
     * Show form to edit purok
     */
    public function edit(Purok $purok)
    {
        $residents = Resident::orderBy('first_name')->get();

        return view('puroks.edit', compact('purok', 'residents'));
    }

    /**
     * Update purok
     */
    public function update(Request $request, Purok $purok)
    {
        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name,' . $purok->id,
            'description' => 'nullable|string',
            'leader_id' => 'nullable|uuid|exists:residents,id',
        ]);

        $purok->update($validated);

        return redirect()->route('puroks.show', $purok)
            ->with('success', 'Purok updated successfully.');
    }

    /**
     * Delete purok
     */
    public function destroy(Purok $purok)
    {
        // Check if purok has households
        if ($purok->households()->count() > 0) {
            return redirect()->route('puroks.index')
                ->with('error', 'Cannot delete purok with households. Please reassign households first.');
        }

        $purok->delete();

        return redirect()->route('puroks.index')
            ->with('success', 'Purok deleted successfully.');
    }
}
