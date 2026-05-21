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
        $households = Household::with('purok')->orderBy('house_number')->get();

        return view('residents.create', compact('households'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $birthdate = \Carbon\Carbon::parse($request->birthdate);
        $age = $birthdate->age;


        // Validate inputs
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email|unique:residents,email', // Check duplicate email
            'contact_number' => 'required|unique:residents,contact_number',
            'birthdate' => 'required|date',
            'gender' => 'required',
            'civil_status' => 'required',
            'voter_status' => [
                'required',
                function ($attribute, $value, $fail) use ($age) {
                    // Kung 15 pababa, bawal ang 'Registered'
                    if ($age <= 15 && $value === 'Registered') {
                        $fail('Ang mga resident na edad 15 pababa ay hindi maaaring maging Registered voter.');
                    }
                },
            ],
            'residency_status' => 'required',
            'household_id' => 'required|exists:households,id',
        ]);

        // I-generate ang format na BR-YY-XXXX-XXXX
        $year = date('y'); // Halimbawa: '26' para sa 2026

        // I-generate ang random numbers
        $generateNumber = function () {
            $part1 = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $part2 = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            return $part1 . '-' . $part2;
        };

        $residentNumber = "BR-{$year}-" . $generateNumber();

        // Siguraduhin na UNIQUE ang generated number (check sa database)
        while (\App\Models\Resident::where('resident_number', $residentNumber)->exists()) {
            $residentNumber = "BR-{$year}-" . $generateNumber();
        }

        // Save sa database
        $resident = new \App\Models\Resident($request->all());
        $resident->resident_number = $residentNumber;
        $resident->save();

        return redirect()->route('residents.index')->with('success', 'Resident added successfully.');
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
        $resident = Resident::withTrashed()->findOrFail($id);
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
        $resident = Resident::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('residents', 'email')->ignore($resident->id)
            ],
            'contact_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('residents', 'contact_number')->ignore($resident->id)
            ],
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
        $user = auth()->user();

        $userResidentId = $user->official ? $user->official->resident_id : null;

        if ($userResidentId && $userResidentId == $id) {
            return response()->json(['error' => 'Hindi mo maaaring i-delete ang sarili mong account.'], 403);
        }

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
        $user = auth()->user();
        $isAdminOrSecretary = false;

        if ($user) {
            $roleId = $user->role_id ?? ($user->official->role_id ?? null);

            if ($roleId !== null && in_array((int) $roleId, [1, 3])) {
                $isAdminOrSecretary = true;
            }
        }

        // Kung admin/secretary, isama ang deleted (withTrashed). Kung hindi, active lang.
        $residents = $isAdminOrSecretary ? Resident::withTrashed() : Resident::query();
        $residents->with(['household.purok']);

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
            ->addColumn('action', function ($resident) use ($isAdminOrSecretary) {
                $user = auth()->user();

                $userResidentId = $user->official ? $user->official->resident_id : null;

                $editBtn = !$resident->trashed()
                    ? '<button type="button" class="btn btn-sm btn-light border" style="border-radius:6px;padding:3px 8px;" onclick="openEditModal(\'' . $resident->id . '\')" title="Edit"><i class="bi bi-pencil" style="font-size:13px;"></i></button>'
                    : '';

                $actionBtn = '';

                $isSelf = ($userResidentId && $userResidentId == $resident->id);

                if ($isAdminOrSecretary && !$isSelf) {
                    if ($resident->trashed()) {
                        // Kung deleted: Restore button lang
                        $actionBtn = '<button type="button" class="btn btn-sm btn-light text-success border" style="border-radius:6px;padding:3px 8px;" onclick="confirmRestore(\'' . $resident->id . '\')" title="Restore"><i class="bi bi-arrow-counterclockwise" style="font-size:13px;"></i></button>';
                    } else {
                        // Kung active: Delete button
                        $actionBtn = '<button type="button" class="btn btn-sm btn-light text-danger border" style="border-radius:6px;padding:3px 8px;" onclick="confirmDelete(\'' . $resident->id . '\')" title="Delete"><i class="bi bi-trash" style="font-size:13px;"></i></button>';
                    }
                }

                return '<div class="d-flex gap-1">' . $editBtn . $actionBtn . '</div>';
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
        $residents = Resident::with(['household.purok'])->get();

        $data = [
            'residents' => $residents,
            'generatedAt' => now()->format('M d, Y H:i A'),
        ];

        $pdf = PDF::loadView('residents.export-pdf', $data);
        return $pdf->download('residents-list-' . now()->format('Y-m-d-His') . '.pdf');
    }

    // Sa loob ng ResidentController class

    public function getDashboardStats()
    {
        $totalResidents = Resident::count();
        $activeCount = Resident::where('residency_status', 'Active')->count();
        $deceasedCount = Resident::where('residency_status', 'Deceased')->count();
        $transferredCount = Resident::where('residency_status', 'Transferred')->count();

        // Percentages
        $activePercentage = $totalResidents > 0 ? number_format(($activeCount / $totalResidents) * 100, 1) : 0;
        $deceasedPercentage = $totalResidents > 0 ? number_format(($deceasedCount / $totalResidents) * 100, 1) : 0;
        $transferredPercentage = $totalResidents > 0 ? number_format(($transferredCount / $totalResidents) * 100, 1) : 0;

        // Gender (Active Only)
        $maleCount = Resident::where('residency_status', 'Active')->where('gender', 'Male')->count();
        $femaleCount = Resident::where('residency_status', 'Active')->where('gender', 'Female')->count();

        // Age Data
        $ageGroups = ['0-12', '13-17', '18-24', '25-34', '35-49', '50-64', '65+'];
        $ageData = [];
        foreach ($ageGroups as $group) {
            $range = explode('-', str_replace('+', '', $group));
            $query = Resident::query();
            if ($group === '65+') {
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 65");
            } else {
                $query->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN ? AND ?", [$range[0], $range[1]]);
            }
            $ageData[] = $query->count();
        }

        // Voter Status
        $registeredVoters = Resident::whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 18")->where('voter_status', 'Registered')->count();
        $unregisteredVoters = Resident::whereRaw("TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 18")->where('voter_status', 'Unregistered')->count();

        return response()->json([
            'totalResidents' => number_format($totalResidents),
            'activeCount' => number_format($activeCount),
            'deceasedCount' => number_format($deceasedCount),
            'transferredCount' => number_format($transferredCount),
            'activePercentage' => $activePercentage,
            'deceasedPercentage' => $deceasedPercentage,
            'transferredPercentage' => $transferredPercentage,
            'genderData' => [$maleCount, $femaleCount],
            'ageData' => $ageData,
            'voterData' => [$registeredVoters, $unregisteredVoters]
        ]);
    }
}
