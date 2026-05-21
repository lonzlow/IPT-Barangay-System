<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteeRecord;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of reports.
     */
    public function index()
    {
        $this->authorize('reports.view');

        $records = CommitteeRecord::with('committee')->paginate(15);
        $committees = Committee::orderBy('name')->get();
        $recordTypes = CommitteeRecord::TYPES;

        return view('reports.index', compact('records', 'committees', 'recordTypes'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        $this->authorize('committee-records.manage');

        $committees = Committee::all();
        $recordTypes = CommitteeRecord::TYPES;
        return view('reports.create', compact('committees', 'recordTypes'));
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('committee-records.manage');

        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'record_type' => 'required|in:' . implode(',', array_keys(CommitteeRecord::TYPES)),
            'category' => 'nullable|string|max:120',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'record_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'partner_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:80',
            'file_path' => 'nullable|string',
            'recorded_at' => 'required|date',
        ]);

        $record = CommitteeRecord::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Report created successfully',
                'data' => $record->load('committee')
            ], 201);
        }

        return redirect()->route('reports.index')->with('success', 'Report created successfully.');
    }

    /**
     * Display the specified report.
     */
    public function show(CommitteeRecord $record)
    {
        $this->authorize('reports.view');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $record->load('committee')
            ]);
        }
        return view('reports.show', compact('record'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(CommitteeRecord $record)
    {
        $this->authorize('committee-records.manage');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $record->load('committee')
            ]);
        }
        $committees = Committee::all();
        $recordTypes = CommitteeRecord::TYPES;
        return view('reports.edit', compact('record', 'committees', 'recordTypes'));
    }

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, CommitteeRecord $record)
    {
        $this->authorize('committee-records.manage');

        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'record_type' => 'required|in:' . implode(',', array_keys(CommitteeRecord::TYPES)),
            'category' => 'nullable|string|max:120',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'record_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:0',
            'amount' => 'nullable|numeric|min:0',
            'partner_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:80',
            'file_path' => 'nullable|string',
            'recorded_at' => 'required|date',
        ]);

        $record->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Report updated successfully',
                'data' => $record->load('committee')
            ], 200);
        }

        return redirect()->route('reports.show', $record)->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy(CommitteeRecord $record, Request $request)
    {
        $this->authorize('committee-records.manage');

        $record->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Report deleted successfully'
            ], 200);
        }

        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }
}
