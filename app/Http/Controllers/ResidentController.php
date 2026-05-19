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
        return view('residents.index');
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
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:residents,email'],
            'contact_number' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
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
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'civil_status' => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'])],
            'voter_status' => ['required', Rule::in(['Registered', 'Unregistered', 'Suspended'])],
            'residency_status' => ['required', Rule::in(['Active', 'Deceased', 'Transferred'])],
            'household_id' => ['required', 'exists:households,id'],
        ]);

        $resident->update($validated);

        return redirect()
            ->route('residents.edit', $resident->id)
            ->with('success', 'Resident updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $resident = Resident::findOrFail($id);
        $resident->delete();

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

        // Age range filter
        if ($request->has('age_from') && $request->input('age_from')) {
            $ageFrom = (int)$request->input('age_from');
            $residents->whereRaw("YEAR(CURDATE()) - YEAR(birthdate) >= ?", [$ageFrom]);
        }

        if ($request->has('age_to') && $request->input('age_to')) {
            $ageTo = (int)$request->input('age_to');
            $residents->whereRaw("YEAR(CURDATE()) - YEAR(birthdate) <= ?", [$ageTo]);
        }

        return DataTables::of($residents)
            ->addColumn('age', function($resident) {
                return $resident->age ?? 'N/A';
            })
            ->addColumn('household_purok', function($resident) {
                $household = $resident->household
                    ? $resident->household->house_number . ' ' . $resident->household->street . ' - '
                    : 'N/A -';
                $purok = $resident->household && $resident->household->purok
                    ? $resident->household->purok->purok_name
                    : 'N/A';
                return $household . ' - ' . $purok;
            })
            ->addColumn('voter', function ($resident) {
                if ($resident->voter_status === 'Registered') {
                    return '<span class="badge p-2 py-3 bg-success">Registered</span>';
                } elseif ($resident->voter_status === 'Unregistered') {
                    return '<span class="badge p-2 py-3 bg-danger">Unregistered</span>';
                } elseif ($resident->voter_status === 'Suspended') {
                    return '<span class="badge p-2 py-3 bg-warning">Suspended</span>';
                } else {
                    return '<span class="badge p-2 py-3 bg-secondary">Unknown</span>';
                }
            })
            ->addColumn('residency', function ($resident) {
                if ($resident->residency_status === 'Active') {
                    return '<span class="badge p-2 py-3 bg-success">Active</span>';
                } elseif ($resident->residency_status === 'Deceased') {
                    return '<span class="badge p-2 py-3 bg-danger">Deceased</span>';
                } elseif ($resident->residency_status === 'Transferred') {
                    return '<span class="badge p-2 py-3 bg-warning">Transferred</span>';
                } else {
                    return '<span class="badge p-2 py-3 bg-secondary">Unknown</span>';
                }
            })
            ->addColumn('action', function ($resident) {
                return '<div class="d-flex gap-1">
                    <a href="'.route('residents.edit', $resident->id).'" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                        <i class="bi bi-pencil" style="font-size:13px;"></i>
                    </a>
                    <a href="'.route('residents.edit', ['resident' => $resident->id, 'section' => 'status']).'" class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Update Status">
                        <i class="bi bi-shield-fill" style="font-size:13px;"></i>
                    </a>
                    <a href="'.route('residents.edit', ['resident' => $resident->id, 'section' => 'deactivate']).'" class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Deactivate">
                        <i class="bi bi-person-x-fill" style="font-size:13px;"></i>
                    </a>
                </div>';
            })
            ->rawColumns(['household_purok', 'voter', 'residency', 'action'])
            ->make(true);
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
}
