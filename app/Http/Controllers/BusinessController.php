<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessOwner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('businesses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('businesses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'date_established' => 'required|date',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $business = Business::create([
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_address' => $validated['business_address'],
            'date_established' => $validated['date_established'],
            'status' => $validated['status'],
        ]);

        $this->upsertOwnerFromName($business, $validated['owner_name']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Business created successfully.',
                'business' => $business->load('business_owners.resident'),
            ], 201);
        }
        return redirect()->route('businesses.index')->with('success', 'Business created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::with('business_owners.resident')->findOrFail($id);
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'data' => $business]);
        }
        return view('businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $business = Business::with('business_owners.resident')->findOrFail($id);
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $business->id,
                    'business_name' => $business->business_name,
                    'owner_name' => $this->formatOwnerName($business->business_owners->first()),
                    'business_type' => $business->business_type,
                    'business_address' => $business->business_address,
                    'date_established' => Carbon::parse($business->date_established)->format('Y-m-d'),
                    'status' => $business->status,
                ],
            ]);
        }
        return view('businesses.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'date_established' => 'required|date',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $business->update([
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_address' => $validated['business_address'],
            'date_established' => $validated['date_established'],
            'status' => $validated['status'],
        ]);

        $this->upsertOwnerFromName($business, $validated['owner_name']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Business updated successfully.',
                'business' => $business->load('business_owners.resident'),
            ]);
        }
        return redirect()->route('businesses.index')->with('success', 'Business updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $business = Business::findOrFail($id);
        $business->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Business deleted successfully']);
        }
        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully');
    }

    public function data()
    {
        $businesses = Business::with('business_owners.resident')->latest();

        return DataTables::of($businesses)
            ->addColumn('owner_names', function ($business) {
                if ($business->business_owners->isEmpty()) {
                    return '—';
                }

                return $business->business_owners->map(function ($owner) {
                    if ($owner->resident_id && $owner->resident) {
                        $residentName = trim($owner->resident->first_name . ' ' . $owner->resident->middle_name . ' ' . $owner->resident->last_name . ' ' . $owner->resident->suffix);
                        return e($residentName);
                    }

                    $name = $owner->organization_name ?: trim($owner->first_name . ' ' . $owner->middle_name . ' ' . $owner->last_name . ' ' . $owner->suffix);
                    $details = collect([$owner->contact_number, $owner->email, $owner->address])
                        ->filter()
                        ->implode(' | ');

                    $safeName = e($name);
                    $safeDetails = e($details);

                    return $details ? $safeName . '<br><small>' . $safeDetails . '</small>' : $safeName;
                })->implode('<br>');
            })
            ->addColumn('status_badge', function ($business) {
                $status = $business->status ?? 'Inactive';
                $class = match ($status) {
                    'Active' => 'bg-success-subtle text-success',
                    'Closed' => 'bg-danger-subtle text-danger',
                    default => 'bg-secondary-subtle text-secondary',
                };

                return '<span class="badge ' . $class . '">' . e($status) . '</span>';
            })
            ->addColumn('action', function ($business) {
                return '<button type="button" class="btn btn-sm btn-light" data-business-action="edit" data-business-id="' . e($business->id) . '"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-business-action="delete" data-business-id="' . e($business->id) . '"><i class="bi bi-trash"></i></button>';
            })
            ->rawColumns(['owner_names', 'status_badge', 'action'])
            ->toJson();
    }

    private function upsertOwnerFromName(Business $business, string $ownerName): BusinessOwner
    {
        $ownerData = $this->parseOwnerName($ownerName);
        $owner = $business->business_owners()->first();

        if ($owner) {
            $owner->update($ownerData);
            return $owner;
        }

        $owner = BusinessOwner::create($ownerData);
        $business->business_owners()->attach($owner->id, ['ownership_role' => 'Owner']);

        return $owner;
    }

    private function parseOwnerName(string $ownerName): array
    {
        $cleanName = trim(preg_replace('/\s+/', ' ', $ownerName));
        $parts = $cleanName === '' ? [] : explode(' ', $cleanName);

        $firstName = $parts[0] ?? null;
        $lastName = null;
        $middleName = null;

        if (count($parts) === 1) {
            $lastName = null;
        } elseif (count($parts) === 2) {
            $lastName = $parts[1];
        } elseif (count($parts) > 2) {
            $lastName = array_pop($parts);
            $middleName = implode(' ', array_slice($parts, 1));
        }

        return [
            'owner_type' => 'Non-resident',
            'resident_id' => null,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => null,
            'organization_name' => null,
            'contact_number' => null,
            'email' => null,
            'address' => null,
        ];
    }

    private function formatOwnerName(?BusinessOwner $owner): string
    {
        if (! $owner) {
            return '';
        }

        if ($owner->resident_id && $owner->resident) {
            return trim($owner->resident->first_name . ' ' . $owner->resident->middle_name . ' ' . $owner->resident->last_name . ' ' . $owner->resident->suffix);
        }

        return $owner->organization_name ?: trim($owner->first_name . ' ' . $owner->middle_name . ' ' . $owner->last_name . ' ' . $owner->suffix);
    }
}
