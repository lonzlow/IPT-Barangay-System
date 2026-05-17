<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    /**
     * Display all households with statistics
     */
    public function index()
    {
        $households = Household::with('purok', 'head_resident', 'residents')
            ->paginate(15);

        $statistics = [
            'total_households' => Household::count(),
            'total_residents' => Resident::count(),
            'avg_family_size' => Household::avg('family_size'),
            'voters_count' => Resident::where('voter_status', 'Registered Voter')->count(),
        ];

        return view('households.index', compact('households', 'statistics'));
    }

    /**
     * Show household statistics dashboard
     */
    public function statistics()
    {
        $totalResidents = Resident::count();
        $votersCount = Resident::where('voter_status', 'Registered Voter')->count();
        $totalHouseholds = Household::count();
        
        $statistics = [
            'total_households' => $totalHouseholds,
            'total_residents' => $totalResidents,
            'avg_family_size' => $totalHouseholds > 0 ? round(Household::avg('family_size'), 2) : 0,
            'voters_count' => $votersCount,
            'voters_percentage' => $totalResidents > 0 ? round(($votersCount / $totalResidents * 100), 2) : 0,
            'largest_household_size' => Household::max('family_size') ?? 0,
            'smallest_household_size' => Household::min('family_size') ?? 0,
        ];

        // Households by purok
        $householdsByPurok = Household::select('purok_id')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(family_size) as total_residents')
            ->with('purok')
            ->groupBy('purok_id')
            ->get();

        // Voter statistics by purok
        $votersByPurok = Resident::select('households.purok_id')
            ->selectRaw('COUNT(*) as voter_count')
            ->join('households', 'residents.household_id', '=', 'households.id')
            ->where('voter_status', 'Registered Voter')
            ->groupBy('households.purok_id')
            ->get();

        return view('households.statistics', compact('statistics', 'householdsByPurok', 'votersByPurok'));
    }

    /**
     * Show form to create new household
     */
    public function create()
    {
        $puroks = Purok::orderBy('purok_name')->get();
        $residents = Resident::orderBy('first_name')->get();

        return view('households.create', compact('puroks', 'residents'));
    }

    /**
     * Store new household
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'house_number' => 'required|string',
            'street' => 'required|string',
            'family_size' => 'required|integer|min:1',
            'head_resident_id' => 'nullable|uuid|exists:residents,id',
        ]);

        Household::create($validated);

        return redirect()->route('households.index')
            ->with('success', 'Household created successfully.');
    }

    /**
     * Display specific household
     */
    public function show(Household $household)
    {
        $household->load('purok', 'head_resident', 'residents');

        return view('households.show', compact('household'));
    }

    /**
     * Show form to edit household
     */
    public function edit(Household $household)
    {
        $puroks = Purok::orderBy('purok_name')->get();
        $residents = Resident::orderBy('first_name')->get();

        return view('households.edit', compact('household', 'puroks', 'residents'));
    }

    /**
     * Update household
     */
    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'house_number' => 'required|string',
            'street' => 'required|string',
            'family_size' => 'required|integer|min:1',
            'head_resident_id' => 'nullable|uuid|exists:residents,id',
        ]);

        $household->update($validated);

        return redirect()->route('households.show', $household)
            ->with('success', 'Household updated successfully.');
    }

    /**
     * Delete household
     */
    public function destroy(Household $household)
    {
        // Check if household has residents
        if ($household->residents()->count() > 0) {
            return redirect()->route('households.index')
                ->with('error', 'Cannot delete household with residents. Please reassign residents first.');
        }

        $household->delete();

        return redirect()->route('households.index')
            ->with('success', 'Household deleted successfully.');
    }
}
