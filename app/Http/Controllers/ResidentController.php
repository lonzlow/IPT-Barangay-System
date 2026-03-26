<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
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

        return DataTables::of($residents)
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
}
