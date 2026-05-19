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
        $records = CommitteeRecord::with('committee')->paginate(15);
        return view('reports.index', compact('records'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        $committees = Committee::all();
        $recordTypes = ['photo', 'video', 'activity', 'accomplishment', 'report', 'attendance', 'inventory', 'partnership', 'certificate'];
        return view('reports.create', compact('committees', 'recordTypes'));
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'record_type' => 'required|in:photo,video,activity,accomplishment,report,attendance,inventory,partnership,certificate',
            'title' => 'required|string',
            'description' => 'nullable|string',
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
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $record->load('committee')
            ]);
        }
        $committees = Committee::all();
        $recordTypes = ['photo', 'video', 'activity', 'accomplishment', 'report', 'attendance', 'inventory', 'partnership', 'certificate'];
        return view('reports.edit', compact('record', 'committees', 'recordTypes'));
    }

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, CommitteeRecord $record)
    {
        $validated = $request->validate([
            'committee_id' => 'required|exists:committees,id',
            'record_type' => 'required|in:photo,video,activity,accomplishment,report,attendance,inventory,partnership,certificate',
            'title' => 'required|string',
            'description' => 'nullable|string',
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
        $record->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Report deleted successfully'
            ], 200);
        }

        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }
}
