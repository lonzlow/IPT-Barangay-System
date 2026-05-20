<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurokController extends Controller
{
    /**
     * Display all puroks with statistics
     */
    public function index()
    {
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
        return view('puroks.create');
    }

    /**
     * Store new purok
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name',
            'description' => 'nullable|string',
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
        $residents = Resident::whereHas('household', fn ($query) => $query->where('purok_id', $purok->id))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $householdCount = $purok->households()->count();

        return view('puroks.edit', compact('purok', 'residents', 'householdCount'));
    }

    /**
     * Update purok
     */
    public function update(Request $request, Purok $purok)
    {
        $validated = $request->validate([
            'purok_name' => 'required|string|unique:puroks,purok_name,' . $purok->id,
            'description' => 'nullable|string',
            'leader_id' => [
                'nullable',
                'uuid',
                Rule::exists('residents', 'id')->where(fn ($query) => $query->whereIn(
                    'household_id',
                    $purok->households()->select('id')
                )),
            ],
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
