<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessOwner;
use App\Models\BusinessPermit;
use App\Models\PermitRenewal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        $owners = BusinessOwner::with('resident')->get();

        return view('businesses.create', compact('owners'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',

            'business_owner_ids' => 'required|array|min:1',
            'business_owner_ids.*' => 'required|exists:business_owners,id',

            'ownership_roles' => 'nullable|array',
            'ownership_roles.*' => 'nullable|in:Owner,Co-owner,Representative',

            'ownership_percentages' => 'nullable|array',
            'ownership_percentages.*' => 'nullable|numeric|min:0|max:100',

            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'date_established' => 'required|date',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $totalPercentage = collect($request->ownership_percentages)
            ->filter(fn($value) => $value !== null && $value !== '')
            ->sum();

        if (
            count($request->business_owner_ids) > 1 &&
            $totalPercentage != 100
        ) {
            return response()->json([
                'message' => 'Ownership percentage must total exactly 100%.',
                'errors' => [
                    'ownership_percentages' => [
                        'Total ownership percentage must equal 100%.'
                    ]
                ]
            ], 422);
        }

        $business = Business::create([
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_address' => $validated['business_address'],
            'date_established' => $validated['date_established'],
            'status' => $validated['status'],
        ]);

        $attachData = [];

        foreach ($request->business_owner_ids as $index => $ownerId) {

            $attachData[$ownerId] = [
                'ownership_role' => $request->ownership_roles[$index] ?? 'Owner',
                'ownership_percentage' => $request->ownership_percentages[$index] ?? null,
            ];
        }

        $business->business_owners()->attach($attachData);

        if ($request->expectsJson()) {

            return response()->json([
                'message' => 'Business created successfully.',
                'business' => $business->load('business_owners.resident'),
            ], 201);
        }

        return redirect()
            ->route('businesses.index')
            ->with('success', 'Business created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::with('business_owners.resident', 'business_permits.renewals')->findOrFail($id);
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

        $owners = BusinessOwner::with('resident')->get();

        if (request()->expectsJson()) {

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $business->id,
                    'business_name' => $business->business_name,
                    'business_type' => $business->business_type,
                    'business_address' => $business->business_address,
                    'date_established' => Carbon::parse($business->date_established)->format('Y-m-d'),
                    'status' => $business->status,

                    'owners' => $business->business_owners->map(function ($owner) {

                        return [
                            'business_owner_id' => $owner->id,
                            'ownership_role' => $owner->pivot->ownership_role,
                            'ownership_percentage' => $owner->pivot->ownership_percentage,
                        ];
                    }),
                ],

                'owners' => $owners->map(function ($owner) {

                    if ($owner->resident_id && $owner->resident) {

                        $name = trim(
                            $owner->resident->first_name . ' ' .
                            $owner->resident->middle_name . ' ' .
                            $owner->resident->last_name . ' ' .
                            $owner->resident->suffix
                        );

                    } else {

                        $name = $owner->organization_name
                            ?: trim(
                                $owner->first_name . ' ' .
                                $owner->middle_name . ' ' .
                                $owner->last_name . ' ' .
                                $owner->suffix
                            );
                    }

                    return [
                        'id' => $owner->id,
                        'name' => $name,
                    ];
                }),
            ]);
        }

        return view('businesses.edit', compact('business', 'owners'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',

            'business_owner_ids' => 'required|array|min:1',
            'business_owner_ids.*' => 'required|exists:business_owners,id',

            'ownership_roles' => 'required|array',
            'ownership_roles.*' => 'required|in:Owner,Co-owner,Representative',

            'ownership_percentages' => 'nullable|array',
            'ownership_percentages.*' => 'nullable|numeric|min:0|max:100',

            'business_type' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'date_established' => 'required|date',
            'status' => 'required|in:Active,Inactive,Closed',
        ]);

        $totalPercentage = collect($request->ownership_percentages)
            ->filter(fn($value) => $value !== null && $value !== '')
            ->sum();

        if (
            count($request->business_owner_ids) > 1 &&
            $totalPercentage != 100
        ) {
            return response()->json([
                'message' => 'Ownership percentage must total exactly 100%.',
                'errors' => [
                    'ownership_percentages' => [
                        'Total ownership percentage must equal 100%.'
                    ]
                ]
            ], 422);
        }

        $business->update([
            'business_name' => $validated['business_name'],
            'business_type' => $validated['business_type'],
            'business_address' => $validated['business_address'],
            'date_established' => $validated['date_established'],
            'status' => $validated['status'],
        ]);

        $syncData = [];

        foreach ($request->business_owner_ids as $index => $ownerId) {

            $syncData[$ownerId] = [
                'ownership_role' => $request->ownership_roles[$index] ?? 'Owner',
                'ownership_percentage' => $request->ownership_percentages[$index] ?? null,
            ];
        }

        $business->business_owners()->sync($syncData);

        if ($request->expectsJson()) {

            return response()->json([
                'message' => 'Business updated successfully.',
                'business' => $business->load('business_owners.resident'),
            ]);
        }

        return redirect()
            ->route('businesses.index')
            ->with('success', 'Business updated successfully');
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
        $businesses = Business::with([
            'business_owners.resident',
            'business_permits' => fn ($query) => $query->latest('issued_date')->latest(),
            'business_permits.renewals' => fn ($query) => $query->latest('renewal_date'),
        ])->latest();

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
            ->addColumn('permit_number', function ($business) {
                return e($this->currentPermit($business)?->permit_number ?? 'No permit');
            })
            ->addColumn('permit_status_badge', function ($business) {
                $permit = $this->currentPermit($business);

                if (! $permit) {
                    return '<span class="badge bg-secondary-subtle text-secondary">Not issued</span>';
                }

                $status = $this->displayPermitStatus($permit);
                $class = match ($status) {
                    'Approved', 'Renewed' => 'bg-success-subtle text-success',
                    'Expired' => 'bg-warning-subtle text-warning',
                    'Revoked', 'Suspended' => 'bg-danger-subtle text-danger',
                    default => 'bg-secondary-subtle text-secondary',
                };

                return '<span class="badge ' . $class . '">' . e($status) . '</span>';
            })
            ->addColumn('expiry_date', function ($business) {
                $permit = $this->currentPermit($business);

                return $permit?->expiry_date ? e($permit->expiry_date->format('M d, Y')) : '—';
            })
            ->addColumn('action', function ($business) {
                $permit = $this->currentPermit($business);
                $permitId = $permit?->id;
                $permitStatus = $permit ? $this->displayPermitStatus($permit) : null;
                $hasRenewablePermit = $permit && ! in_array($permitStatus, ['Revoked', 'Suspended'], true);
                $canIssue = ! $permit || ! in_array($permitStatus, ['Approved', 'Renewed'], true);

                return '<div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-light" title="Edit business" data-business-action="edit" data-business-id="' . e($business->id) . '"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-light" title="Issue permit" data-business-action="issue" data-business-id="' . e($business->id) . '" ' . ($canIssue ? '' : 'disabled') . '><i class="bi bi-receipt"></i></button>
                    <button type="button" class="btn btn-light" title="Renew permit" data-business-action="renew" data-business-id="' . e($business->id) . '" data-permit-id="' . e($permitId ?? '') . '" ' . ($hasRenewablePermit ? '' : 'disabled') . '><i class="bi bi-arrow-clockwise"></i></button>
                    <button type="button" class="btn btn-light" title="Permit history" data-business-action="history" data-business-id="' . e($business->id) . '"><i class="bi bi-clock-history"></i></button>
                    <button type="button" class="btn btn-light" title="Delete business" data-business-action="delete" data-business-id="' . e($business->id) . '"><i class="bi bi-trash"></i></button>
                </div>';
            })
            ->rawColumns(['owner_names', 'status_badge', 'permit_status_badge', 'action'])
            ->toJson();
    }

    public function issuePermit(Request $request, Business $business): JsonResponse
    {
        $officialId = $this->currentOfficialId();

        $validated = $request->validate([
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
        ]);

        $issuedDate = Carbon::parse($validated['issued_date'] ?? now()->toDateString())->startOfDay();
        $expiryDate = isset($validated['expiry_date'])
            ? Carbon::parse($validated['expiry_date'])->startOfDay()
            : $this->annualExpiryFrom($issuedDate);

        $activePermit = $business->business_permits()
            ->whereIn('permit_status', ['Approved', 'Renewed'])
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->first();

        if ($activePermit) {
            throw ValidationException::withMessages([
                'permit' => 'This business already has an active permit.',
            ]);
        }

        $permit = DB::transaction(function () use ($business, $officialId, $issuedDate, $expiryDate) {
            return BusinessPermit::create([
                'business_id' => $business->id,
                'permit_number' => $this->nextPermitNumber($issuedDate),
                'issued_date' => $issuedDate->toDateString(),
                'expiry_date' => $expiryDate->toDateString(),
                'permit_status' => 'Approved',
                'issued_by' => $officialId,
            ]);
        });

        return response()->json([
            'message' => 'Business permit issued successfully.',
            'permit' => $this->formatPermit($permit->load('renewals')),
        ], 201);
    }

    public function renewPermit(Request $request, Business $business, BusinessPermit $permit): JsonResponse
    {
        if ($permit->business_id !== $business->id) {
            abort(404);
        }

        if (in_array($permit->permit_status, ['Revoked', 'Suspended'], true)) {
            throw ValidationException::withMessages([
                'permit' => 'Revoked or suspended permits cannot be renewed.',
            ]);
        }

        $officialId = $this->currentOfficialId();

        $validated = $request->validate([
            'renewal_date' => ['nullable', 'date'],
            'new_expiry_date' => ['nullable', 'date', 'after_or_equal:renewal_date'],
            'fee_paid' => ['required', 'numeric', 'min:0'],
        ]);

        $renewalDate = Carbon::parse($validated['renewal_date'] ?? now()->toDateString())->startOfDay();
        $newExpiryDate = isset($validated['new_expiry_date'])
            ? Carbon::parse($validated['new_expiry_date'])->startOfDay()
            : $this->annualExpiryFrom($renewalDate);

        $renewal = DB::transaction(function () use ($permit, $officialId, $validated, $renewalDate, $newExpiryDate) {
            $renewal = PermitRenewal::create([
                'permit_id' => $permit->id,
                'fee_paid' => $validated['fee_paid'],
                'renewal_date' => $renewalDate->toDateString(),
                'new_expiry_date' => $newExpiryDate->toDateString(),
                'processed_by' => $officialId,
            ]);

            $permit->update([
                'expiry_date' => $newExpiryDate->toDateString(),
                'permit_status' => 'Renewed',
            ]);

            return $renewal;
        });

        return response()->json([
            'message' => 'Business permit renewed successfully.',
            'renewal' => [
                'id' => $renewal->id,
                'fee_paid' => number_format((float) $renewal->fee_paid, 2, '.', ''),
                'renewal_date' => $renewal->renewal_date->format('Y-m-d'),
                'new_expiry_date' => $renewal->new_expiry_date?->format('Y-m-d'),
            ],
            'permit' => $this->formatPermit($permit->fresh('renewals')),
        ]);
    }

    public function permitHistory(Business $business): JsonResponse
    {
        $business->load([
            'business_permits' => fn ($query) => $query->latest('issued_date')->latest(),
            'business_permits.renewals' => fn ($query) => $query->latest('renewal_date'),
            'business_permits.issuer.resident',
            'business_permits.renewals.processor.resident',
        ]);

        return response()->json([
            'business' => [
                'id' => $business->id,
                'business_name' => $business->business_name,
            ],
            'permits' => $business->business_permits->map(fn (BusinessPermit $permit) => $this->formatPermit($permit)),
        ]);
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

    private function currentOfficialId(): string
    {
        $officialId = request()->user()?->official_id;

        if (! $officialId) {
            throw ValidationException::withMessages([
                'issued_by' => 'Your user account must be linked to an official before processing business permits.',
            ]);
        }

        return $officialId;
    }

    private function annualExpiryFrom(Carbon $date): Carbon
    {
        return $date->copy()->addYear()->subDay();
    }

    private function nextPermitNumber(Carbon $issuedDate): string
    {
        $year = $issuedDate->format('Y');
        $latest = BusinessPermit::query()
            ->where('permit_number', 'like', "BP-{$year}-%")
            ->orderByDesc('permit_number')
            ->value('permit_number');

        $sequence = $latest ? ((int) substr($latest, -5)) + 1 : 1;

        return sprintf('BP-%s-%05d', $year, $sequence);
    }

    private function currentPermit(Business $business): ?BusinessPermit
    {
        return $business->business_permits
            ->sortByDesc(fn (BusinessPermit $permit) => $permit->issued_date?->timestamp ?? $permit->created_at?->timestamp ?? 0)
            ->first();
    }

    private function displayPermitStatus(BusinessPermit $permit): string
    {
        if (! in_array($permit->permit_status, ['Revoked', 'Suspended'], true) && $permit->expiry_date?->lt(now()->startOfDay())) {
            return 'Expired';
        }

        return $permit->permit_status;
    }

    private function formatPermit(BusinessPermit $permit): array
    {
        return [
            'id' => $permit->id,
            'permit_number' => $permit->permit_number,
            'issued_date' => $permit->issued_date?->format('Y-m-d'),
            'expiry_date' => $permit->expiry_date?->format('Y-m-d'),
            'permit_status' => $permit->permit_status,
            'display_status' => $this->displayPermitStatus($permit),
            'issued_by' => $this->formatOfficialName($permit->issuer),
            'renewals' => $permit->renewals->map(fn (PermitRenewal $renewal) => [
                'id' => $renewal->id,
                'fee_paid' => number_format((float) $renewal->fee_paid, 2, '.', ''),
                'renewal_date' => $renewal->renewal_date?->format('Y-m-d'),
                'new_expiry_date' => $renewal->new_expiry_date?->format('Y-m-d'),
                'processed_by' => $this->formatOfficialName($renewal->processor),
            ])->values(),
        ];
    }

    private function formatOfficialName($official): string
    {
        if (! $official?->resident) {
            return 'N/A';
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', [
            $official->resident->first_name,
            $official->resident->middle_name,
            $official->resident->last_name,
            $official->resident->suffix,
        ])));
    }
}
