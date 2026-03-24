<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getResidents(Request $request)
    {
        $residents = Resident::query();

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
                    <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Edit">
                        <i class="bi bi-pencil" style="font-size:13px;"></i>
                    </button>
                    <button class="btn btn-sm btn-light" style="border-radius:6px;padding:3px 8px;" title="Assign Role">
                        <i class="bi bi-shield-fill" style="font-size:13px;"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" style="border-radius:6px;padding:3px 8px;" title="Deactivate">
                        <i class="bi bi-person-x-fill" style="font-size:13px;"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['household_purok', 'voter', 'residency', 'action'])
            ->make(true);

    }
}
