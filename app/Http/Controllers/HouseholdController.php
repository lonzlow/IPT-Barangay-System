<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Purok;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HouseholdController extends Controller
{
    public function index()
    {
        $puroks = Purok::orderBy("purok_name")->get();

        return view("households.index", compact("puroks"));
    }

    public function create()
    {
        $puroks = Purok::orderBy("purok_name")->get();

        return view("households.create", compact("puroks"));
    }

    public function data()
    {
        $households = Household::query()
            ->with(["purok", "head_resident"])
            ->withCount([
                "residents as member_count",
                "residents as registered_voter_count" => fn ($query) => $query->where("voter_status", "Registered"),
            ]);

        return DataTables::of($households)
            ->addColumn("purok_name", fn (Household $household) => e($household->purok?->purok_name ?? "N/A"))
            ->addColumn("address", fn (Household $household) => e($household->house_number . " " . $household->street))
            ->addColumn("head_name", fn (Household $household) => e($this->residentName($household->head_resident) ?: "Unassigned"))
            ->addColumn("family_size", fn (Household $household) => $household->member_count)
            ->addColumn("registered_voters", fn (Household $household) => $household->registered_voter_count)
            ->addColumn("action", function (Household $household) {
                return '<div class="btn-group btn-group-sm" role="group">
                    <a href="' . route("households.show", $household) . '" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                    <button type="button" class="btn btn-sm btn-outline-warning" data-household-action="edit" data-household-id="' . e($household->id) . '" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-household-action="delete" data-household-id="' . e($household->id) . '" title="Delete"><i class="bi bi-trash"></i></button>
                </div>';
            })
            ->rawColumns(["action"])
            ->toJson();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "purok_id" => ["required", "exists:puroks,id"],
            "house_number" => ["required", "string", "max:255"],
            "street" => ["required", "string", "max:255"],
        ]);

        $household = Household::create($validated + ["family_size" => 0]);

        if ($request->expectsJson()) {
            return response()->json([
                "message" => "Household created successfully.",
                "household" => $household,
            ], 201);
        }

        return redirect()->route("households.index")->with("success", "Household created successfully.");
    }

    public function show(Household $household)
    {
        $household->load([
            "purok",
            "head_resident",
            "residents" => fn ($query) => $query->orderBy("last_name")->orderBy("first_name"),
        ]);

        $statistics = [
            "family_size" => $household->residents->count(),
            "registered_voters" => $household->residents->where("voter_status", "Registered")->count(),
            "active_residents" => $household->residents->where("residency_status", "Active")->count(),
        ];

        return view("households.show", compact("household", "statistics"));
    }

    public function edit(Household $household)
    {
        $household->load(["residents" => fn ($query) => $query->orderBy("last_name")->orderBy("first_name")]);

        if (request()->expectsJson()) {
            return response()->json([
                "id" => $household->id,
                "purok_id" => $household->purok_id,
                "house_number" => $household->house_number,
                "street" => $household->street,
                "head_resident_id" => $household->head_resident_id,
                "residents" => $household->residents->map(fn (Resident $resident) => [
                    "id" => $resident->id,
                    "name" => $this->residentName($resident),
                ])->values(),
            ]);
        }

        $puroks = Purok::orderBy("purok_name")->get();
        $residents = $household->residents;

        return view("households.edit", compact("household", "puroks", "residents"));
    }

    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            "purok_id" => ["required", "exists:puroks,id"],
            "house_number" => ["required", "string", "max:255"],
            "street" => ["required", "string", "max:255"],
            "head_resident_id" => [
                "nullable",
                "uuid",
                Rule::exists("residents", "id")->where(fn ($query) => $query->where("household_id", $household->id)),
            ],
        ]);

        $household->update($validated);

        if ($request->expectsJson()) {
            return response()->json(["message" => "Household updated successfully."]);
        }

        return redirect()->route("households.index")->with("success", "Household updated successfully.");
    }

    public function destroy(Household $household)
    {
        if ($household->residents()->exists()) {
            $message = "Cannot delete a household with assigned residents. Reassign residents first.";

            if (request()->expectsJson()) {
                return response()->json(["message" => $message], 422);
            }

            return redirect()->route("households.index")->with("error", $message);
        }

        $household->delete();

        if (request()->expectsJson()) {
            return response()->json(["message" => "Household deleted successfully."]);
        }

        return redirect()->route("households.index")->with("success", "Household deleted successfully.");
    }

    public function statistics()
    {
        $totalHouseholds = Household::count();
        $totalResidents = Resident::count();
        $votersCount = Resident::where("voter_status", "Registered")->count();

        $householdsByPurok = Purok::query()
            ->withCount([
                "households",
                "households as residents_count" => fn ($query) => $query
                    ->join("residents", "residents.household_id", "=", "households.id")
                    ->select(DB::raw("count(residents.id)")),
                "households as registered_voters_count" => fn ($query) => $query
                    ->join("residents", "residents.household_id", "=", "households.id")
                    ->where("residents.voter_status", "Registered")
                    ->select(DB::raw("count(residents.id)")),
            ])
            ->orderBy("purok_name")
            ->get();

        $householdSizes = Household::withCount("residents")->get()->pluck("residents_count");

        $statistics = [
            "total_households" => $totalHouseholds,
            "total_residents" => $totalResidents,
            "avg_family_size" => $totalHouseholds > 0 ? number_format($totalResidents / $totalHouseholds, 1) : "0.0",
            "voters_count" => $votersCount,
            "voters_percentage" => $totalResidents > 0 ? number_format(($votersCount / $totalResidents) * 100, 1) : "0.0",
            "largest_household_size" => $householdSizes->max() ?? 0,
            "smallest_household_size" => $householdSizes->min() ?? 0,
        ];

        return view("households.statistics", compact("statistics", "householdsByPurok"));
    }

    private function residentName(?Resident $resident): string
    {
        if (! $resident) {
            return "";
        }

        return trim(collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->last_name,
            $resident->suffix,
        ])->filter()->implode(" "));
    }
}
