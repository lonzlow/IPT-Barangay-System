<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('residents.view');

        $totalResidents = Resident::count();
        $activeCount = Resident::where('residency_status', 'Active')->count();
        $deceasedCount = Resident::where('residency_status', 'Deceased')->count();
        $transferredCount = Resident::where('residency_status', 'Transferred')->count();

        $activePercentage = $totalResidents > 0 ? number_format(($activeCount / $totalResidents) * 100, 1) : 0;
        $deceasedPercentage = $totalResidents > 0 ? number_format(($deceasedCount / $totalResidents) * 100, 1) : 0;
        $transferredPercentage = $totalResidents > 0 ? number_format(($transferredCount / $totalResidents) * 100, 1) : 0;

        $maleCount = Resident::where('residency_status', 'Active')->where('gender', 'Male')->count();
        $femaleCount = Resident::where('residency_status', 'Active')->where('gender', 'Female')->count();

        $residentsWithAge = Resident::selectRaw("
        CASE 
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 0 AND 12 THEN '0-12'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 13 AND 17 THEN '13-17'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 35 AND 49 THEN '35-49'
            WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 50 AND 64 THEN '50-64'
            ELSE '65+'
        END as age_group, COUNT(*) as count
    ")->groupBy('age_group')->pluck('count', 'age_group')->toArray();

        $ageGroups = ['0-12', '13-17', '18-24', '25-34', '35-49', '50-64', '65+'];
        $ageData = [];
        foreach ($ageGroups as $group) {
            $ageData[] = $residentsWithAge[$group] ?? 0;
        }

        $registeredVoters = Resident::whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 18")
            ->where('voter_status', 'Registered')
            ->count();

        $unregisteredVoters = Resident::whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 18")
            ->where('voter_status', 'Unregistered')
            ->count();

        return view('residents.index', compact(
            'totalResidents',
            'activeCount',
            'deceasedCount',
            'transferredCount',
            'activePercentage',
            'deceasedPercentage',
            'transferredPercentage',
            'maleCount',
            'femaleCount',
            'ageData',
            'registeredVoters',
            'unregisteredVoters'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('residents.manage');

        $households = Household::with('purok')->orderBy('house_number')->get();

        return view('residents.create', compact('households'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('residents.manage');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:residents,email'],
            'contact_number' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'civil_status' => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'])],
            'voter_status' => ['required', Rule::in(['Registered', 'Unregistered', 'Suspended'])],
            'residency_status' => ['required', Rule::in(['Active', 'Deceased', 'Transferred'])],
            'household_id' => ['required', 'exists:households,id'],
        ]);

        Resident::create($validated);

        return redirect()
            ->route('residents.index')
            ->with('success', 'New resident created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $resident = Resident::findOrFail($id);
        $households = Household::with('purok')->orderBy('house_number')->get();

        // Kung AJAX ang tumawag, JSON lang ang ibabalik para sa Modal natin
        if (request()->ajax()) {
            return response()->json($resident);
        }

        return view('residents.edit', compact('resident', 'households'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $resident = Resident::findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('residents', 'email')->ignore($resident->id)],
            'contact_number' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'civil_status' => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'])],
            'voter_status' => ['required', Rule::in(['Registered', 'Unregistered', 'Suspended'])],
            'residency_status' => ['required', Rule::in(['Active', 'Deceased', 'Transferred'])],
        ]);

        $resident->update($validated);

        // Kung AJAX ang nag-save, mag-return ng success message para sa modal closure
        if ($request->ajax()) {
            return response()->json(['success' => 'Resident updated successfully.']);
        }

        return redirect()
            ->route('residents.edit', $resident->id)
            ->with('success', 'Resident updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('residents.delete');

        $resident = Resident::findOrFail($id);
        $resident->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'Resident soft-deleted successfully.']);
        }

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident deactivated successfully.');
    }

    public function getResidents(Request $request)
    {
        $residents = Resident::with(['household.purok']);

        // Apply custom filters (not search - Yajra handles search automatically)
        if ($request->has('gender') && $request->input('gender')) {
            $residents->where('gender', $request->input('gender'));
        }

        if ($request->has('residency_status') && $request->input('residency_status')) {
            $residents->where('residency_status', $request->input('residency_status'));
        }

        if ($request->has('voter_status') && $request->input('voter_status')) {
            $residents->where('voter_status', $request->input('voter_status'));
        }

        if ($request->has('civil_status') && $request->input('civil_status')) {
            $residents->where('civil_status', $request->input('civil_status'));
        }

        if ($request->has('age_from') && $request->input('age_from')) {
            $ageFrom = (int) $request->input('age_from');
            $residents->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= ?", [$ageFrom]);
        }

        if ($request->has('age_to') && $request->input('age_to')) {
            $ageTo = (int) $request->input('age_to');
            $residents->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) <= ?", [$ageTo]);
        }

        return DataTables::of($residents)
            ->addIndexColumn()
            ->addColumn('middle_name', function ($resident) {
                return $resident->middle_name ? strtoupper(substr($resident->middle_name, 0, 1)) . '.' : '';
            })
            ->addColumn('age', function ($resident) {
                return $resident->birthdate ? Carbon::parse($resident->birthdate)->age : 'N/A';
            })
            ->addColumn('household_purok', function ($resident) {
                if ($resident->household) {
                    $houseNumber = $resident->household->house_number ?? '';
                    $street = $resident->household->street ?? '';
                    $purokName = $resident->household->purok ? $resident->household->purok->purok_name : 'No Purok';
                    return trim("{$houseNumber} {$street}") . " / {$purokName}";
                }
                return 'N/A';
            })
            ->addColumn('voter', function ($resident) {
                if ($resident->voter_status === 'Registered') {
                    return '<span class="badge p-2 bg-success">Registered</span>';
                } elseif ($resident->voter_status === 'Unregistered') {
                    return '<span class="badge p-2 bg-danger">Unregistered</span>';
                } elseif ($resident->voter_status === 'Suspended') {
                    return '<span class="badge p-2 bg-warning">Suspended</span>';
                }
                return '<span class="badge p-2 bg-secondary">Unknown</span>';
            })
            ->addColumn('civil_status', function ($resident) {
                $deletedBadge = $resident->trashed() ? ' <span class="badge bg-dark" style="font-size:10px;">Deleted</span>' : '';

                // Kunin ang civil status at gawing case-insensitive para sure (hal. "Single" o "single")
                $status = ucfirst(strtolower($resident->civil_status));

                switch ($status) {
                    case 'Single':
                        return '<span class="badge p-2 bg-primary">Single</span>' . $deletedBadge;
                    case 'Married':
                        return '<span class="badge p-2 bg-success">Married</span>' . $deletedBadge;
                    case 'Widowed':
                        return '<span class="badge p-2 bg-secondary">Widowed</span>' . $deletedBadge;
                    case 'Separated':
                        return '<span class="badge p-2 bg-warning text-dark">Separated</span>' . $deletedBadge;
                    case 'Divorced':
                        return '<span class="badge p-2 bg-danger">Divorced</span>' . $deletedBadge;
                    default:
                        return '<span class="badge p-2 bg-info text-dark">' . ($status ?: 'Unknown') . '</span>' . $deletedBadge;
                }
            })
            ->addColumn('action', function ($resident) {
                return '<div class="d-flex gap-1">
                    <a href="' . route('residents.edit', $resident->id) . '" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                        <i class="bi bi-pencil" style="font-size:13px;"></i>
                    </a>
                    <a href="' . route('residents.edit', ['resident' => $resident->id, 'section' => 'status']) . '" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Update Status">
                        <i class="bi bi-shield-fill" style="font-size:13px;"></i>
                    </a>
                    <a href="' . route('residents.edit', ['resident' => $resident->id, 'section' => 'deactivate']) . '" class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Deactivate">
                        <i class="bi bi-person-x-fill" style="font-size:13px;"></i>
                    </a>
                </div>';
            })
            ->rawColumns(['voter', 'civil_status', 'action'])
            ->make(true);
    }

    // ResidentController.php

    public function restore($id)
    {
        // Gamitin ang withTrashed() para mahanap ang deleted na record
        $resident = Resident::withTrashed()->findOrFail($id);
        $resident->restore();

        return response()->json(['success' => 'Resident recovered successfully.']);
    }

    /**
     * Display demographic statistics
     */
    public function demographics()
    {
        $this->authorize('residents.view');

        $residents = Resident::all();
        $totalResidents = $residents->count();

        // Gender breakdown
        $genderStats = Resident::select('gender')->selectRaw('count(*) as count')
            ->groupBy('gender')
            ->get()
            ->keyBy('gender');

        // Residency status breakdown
        $residencyStats = Resident::select('residency_status')->selectRaw('count(*) as count')
            ->groupBy('residency_status')
            ->get()
            ->keyBy('residency_status');

        // Voter status breakdown
        $voterStats = Resident::select('voter_status')->selectRaw('count(*) as count')
            ->groupBy('voter_status')
            ->get()
            ->keyBy('voter_status');

        // Age group breakdown
        $ageGroupStats = [
            '0-12' => Resident::whereRaw("YEAR(CURDATE()) - YEAR(birthdate) BETWEEN 0 AND 12")->count(),
            '13-17' => Resident::whereRaw("YEAR(CURDATE()) - YEAR(birthdate) BETWEEN 13 AND 17")->count(),
            '18-59' => Resident::whereRaw("YEAR(CURDATE()) - YEAR(birthdate) BETWEEN 18 AND 59")->count(),
            '60+' => Resident::whereRaw("YEAR(CURDATE()) - YEAR(birthdate) >= 60")->count(),
        ];

        // Civil status breakdown
        $civilStats = Resident::select('civil_status')->selectRaw('count(*) as count')
            ->groupBy('civil_status')
            ->get()
            ->keyBy('civil_status');

        // Calculate percentages
        $stats = [
            'totalResidents' => $totalResidents,
            'genderStats' => $genderStats,
            'residencyStats' => $residencyStats,
            'voterStats' => $voterStats,
            'ageGroupStats' => $ageGroupStats,
            'civilStats' => $civilStats,
        ];

        return view('residents.demographics', compact('stats'));
    }

    /**
     * Export residents as PDF
     */
    public function exportPDF(Request $request)
    {
        $this->authorize('residents.view');

        $residents = Resident::with(['household.purok'])->get();

        $data = [
            'residents' => $residents,
            'generatedAt' => now()->format('M d, Y H:i A'),
        ];

        $pdf = PDF::loadView('residents.export-pdf', $data);
        return $pdf->download('residents-list-' . now()->format('Y-m-d-His') . '.pdf');
    }
}
