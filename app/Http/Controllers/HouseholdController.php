<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Purok;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public function index()
    {
        $households = Household::with("purok")->get();
        $puroks = Purok::orderBy("purok_name")->get();
        
        return view("households.index", compact("households", "puroks"));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "purok_id" => "required|exists:puroks,id",
            "head_first_name" => "required|string",
            "head_middle_name" => "nullable|string",
            "head_last_name" => "required|string",
            "head_contact_number" => "nullable|string",
            "notes" => "nullable|string",
            "status" => "required|in:active,inactive",
        ]);

        $household = Household::create($validated);

        if ($request->ajax()) {
            return response()->json(["success" => "Household created successfully."]);
        }

        return redirect()->route("households.index")->with("success", "Household created successfully.");
    }

    public function edit(Household $household)
    {
        return response()->json($household);
    }

    public function update(Request $request, Household $household)
    {
        $validated = $request->validate([
            "purok_id" => "required|exists:puroks,id",
            "head_first_name" => "required|string",
            "head_middle_name" => "nullable|string",
            "head_last_name" => "required|string",
            "head_contact_number" => "nullable|string",
            "notes" => "nullable|string",
            "status" => "required|in:active,inactive",
        ]);

        $household->update($validated);

        if ($request->ajax()) {
            return response()->json(["success" => "Household updated successfully."]);
        }

        return redirect()->route("households.index")->with("success", "Household updated successfully.");
    }

    public function destroy(Household $household)
    {
        $household->delete();

        if (request()->ajax()) {
            return response()->json(["success" => "Household deleted successfully."]);
        }

        return redirect()->route("households.index")->with("success", "Household deleted successfully.");
    }
}