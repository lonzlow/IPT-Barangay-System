<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\BlotterEvidence;
use App\Models\BlotterRespondent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class BlotterController extends Controller
{
    private const STATUS_LABELS = [
        'pending' => 'Open',
        'under investigation' => 'Ongoing',
        'referred' => 'Ongoing',
        'resolved' => 'Resolved',
        'dismissed' => 'Dismissed',
    ];

    private const STATUS_CLASSES = [
        'pending' => 'bg-danger-subtle text-danger',
        'under investigation' => 'bg-warning-subtle text-warning',
        'referred' => 'bg-warning-subtle text-warning',
        'resolved' => 'bg-success-subtle text-success',
        'dismissed' => 'bg-secondary-subtle text-secondary',
    ];

    public function index()
    {
        return view('blotters.index', $this->dashboardPayload());
    }

    public function data(Request $request)
    {
        $blotters = Blotter::query()
            ->with(['complainant_resident', 'respondents.respondent'])
            ->latest('incident_date');

        return DataTables::of($blotters)
            ->addColumn('complainant_display', fn (Blotter $blotter) => e($this->complainantName($blotter)))
            ->addColumn('incident_type', fn (Blotter $blotter) => e($this->incidentType($blotter)))
            ->addColumn('date_filed', fn (Blotter $blotter) => $blotter->incident_date?->format('M d') ?? 'N/A')
            ->addColumn('severity_badge', function (Blotter $blotter) {
                $severity = $this->severity($blotter);
                $class = match ($severity) {
                    'High' => 'text-danger',
                    'Medium' => 'text-warning',
                    default => 'text-success',
                };

                return '<span class="fw-700 ' . $class . '">' . $severity . '</span>';
            })
            ->addColumn('status_badge', function (Blotter $blotter) {
                $status = $this->statusLabel($blotter->status);
                $class = self::STATUS_CLASSES[$blotter->status] ?? 'bg-secondary-subtle text-secondary';

                return '<span class="badge rounded-pill ' . $class . '">' . e($status) . '</span>';
            })
            ->addColumn('action', function (Blotter $blotter) {
                $showUrl = route('blotters.show', $blotter);

                return '<a href="' . e($showUrl) . '" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                    <button type="button" class="btn btn-sm btn-light" data-blotter-action="edit" data-blotter-id="' . e($blotter->id) . '" title="Edit"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-light" data-blotter-action="delete" data-blotter-id="' . e($blotter->id) . '" title="Delete"><i class="bi bi-trash"></i></button>';
            })
            ->filterColumn('complainant_display', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('complainant_name', 'like', "%{$keyword}%")
                        ->orWhereHas('complainant_resident', function ($residentQuery) use ($keyword) {
                            $residentQuery
                                ->where('first_name', 'like', "%{$keyword}%")
                                ->orWhere('last_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->rawColumns(['severity_badge', 'status_badge', 'action'])
            ->toJson();
    }

    public function export(): StreamedResponse
    {
        $fileName = 'blotter-log-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference No.', 'Complainant', 'Incident Type', 'Incident Date', 'Severity', 'Status']);

            Blotter::with('complainant_resident')
                ->latest('incident_date')
                ->chunk(100, function ($blotters) use ($handle) {
                    foreach ($blotters as $blotter) {
                        fputcsv($handle, [
                            $blotter->case_number,
                            $this->complainantName($blotter),
                            $this->incidentType($blotter),
                            $blotter->incident_date?->format('Y-m-d H:i:s'),
                            $this->severity($blotter),
                            $this->statusLabel($blotter->status),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        return redirect()->route('blotters.index');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedBlotter($request);
        $respondentName = $validated['respondent_name'] ?? null;
        unset($validated['respondent_name']);

        $validated['status'] = $this->normalizeStatus($validated['status']);
        $validated['filed_by'] = auth()->id();

        $blotter = Blotter::create($validated);
        $this->syncRespondentName($blotter, $respondentName);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter record saved successfully.',
                'data' => $blotter->load(['filedBy', 'respondents']),
                'dashboard' => $this->dashboardPayload(),
            ], 201);
        }

        return redirect()->route('blotters.index')->with('success', 'Blotter created successfully.');
    }

    public function show(Request $request, string $blotter)
    {
        $blotter = Blotter::with([
            'filedBy.official.resident',
            'complainant_resident',
            'respondents.respondent',
            'witnesses.resident_witness',
            'evidences',
        ])->findOrFail($blotter);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $this->serializeBlotter($blotter),
            ]);
        }

        return view('blotters.info', compact('blotter'));
    }

    public function edit(Request $request, Blotter $blotter)
    {
        $blotter->load(['complainant_resident', 'respondents.respondent']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $this->serializeBlotter($blotter),
            ]);
        }

        return redirect()->route('blotters.index');
    }

    public function update(Request $request, Blotter $blotter)
    {
        $validated = $this->validatedBlotter($request, $blotter);
        $respondentName = $validated['respondent_name'] ?? null;
        unset($validated['respondent_name']);

        $validated['status'] = $this->normalizeStatus($validated['status']);
        $blotter->update($validated);
        $this->syncRespondentName($blotter, $respondentName);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter record updated successfully.',
                'data' => $blotter->fresh()->load(['filedBy', 'respondents']),
                'dashboard' => $this->dashboardPayload(),
            ]);
        }

        return redirect()->route('blotters.show', $blotter)->with('success', 'Blotter updated successfully.');
    }

    public function destroy(Blotter $blotter, Request $request)
    {
        $blotter->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter record deleted successfully.',
                'dashboard' => $this->dashboardPayload(),
            ]);
        }

        return redirect()->route('blotters.index')->with('success', 'Blotter deleted successfully.');
    }

    public function uploadEvidence(Request $request, string $blotter)
    {
        $blotter = Blotter::query()
            ->whereKey($blotter)
            ->orWhere('case_number', $blotter)
            ->firstOrFail();

        $validated = $request->validate([
            'evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $validated['evidence'];
        $path = $file->store('evidence/blotters/' . $blotter->id, 'public');
        $extension = strtolower($file->getClientOriginalExtension());
        $category = $extension === 'pdf' ? 'document' : 'image';

        $evidence = BlotterEvidence::create([
            'blotter_id' => $blotter->id,
            'file_path' => Storage::url($path),
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'file_category' => $category,
            'caption' => $validated['caption'] ?? $file->getClientOriginalName(),
        ]);

        return response()->json([
            'message' => 'Supporting document attached successfully.',
            'data' => $evidence,
        ], 201);
    }

    private function validatedBlotter(Request $request, ?Blotter $blotter = null): array
    {
        return $request->validate([
            'case_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blotters', 'case_number')->ignore($blotter?->id),
            ],
            'complainant_name' => ['required_without:complainant_id', 'nullable', 'string', 'max:255'],
            'complainant_id' => ['nullable', 'exists:residents,id'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'incident_description' => ['required', 'string'],
            'incident_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['open', 'ongoing', 'pending', 'under investigation', 'resolved', 'referred', 'dismissed'])],
        ]);
    }

    private function dashboardPayload(): array
    {
        $blotters = Blotter::with('complainant_resident')->latest('incident_date')->get();
        $total = $blotters->count();
        $open = $blotters->where('status', 'pending')->count();
        $ongoing = $blotters->whereIn('status', ['under investigation', 'referred'])->count();
        $resolved = $blotters->where('status', 'resolved')->count();
        $dismissed = $blotters->where('status', 'dismissed')->count();

        $types = $blotters
            ->groupBy(fn (Blotter $blotter) => $this->incidentType($blotter))
            ->map->count()
            ->sortDesc();

        return [
            'summary' => [
                'total' => $total,
                'open' => $open,
                'ongoing' => $ongoing,
                'resolved' => $resolved,
                'resolved_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
            ],
            'statusDistribution' => [
                'Open' => $open,
                'Ongoing' => $ongoing,
                'Resolved' => $resolved,
                'Dismissed' => $dismissed,
            ],
            'typeBreakdown' => $types->isNotEmpty() ? $types : collect(['Noise' => 0, 'Altercation' => 0, 'Theft' => 0, 'Damage' => 0, 'Domestic' => 0, 'Trespass' => 0, 'Other' => 0]),
            'recentTracker' => $blotters->take(6)->map(fn (Blotter $blotter) => [
                'date' => $blotter->incident_date?->format('M d') ?? 'N/A',
                'case_number' => $blotter->case_number,
                'status' => $this->statusLabel($blotter->status),
                'description' => str($blotter->incident_description)->limit(70)->toString(),
                'tone' => match ($blotter->status) {
                    'resolved' => 'success',
                    'under investigation', 'referred' => 'warning',
                    'dismissed' => 'secondary',
                    default => 'primary',
                },
            ]),
        ];
    }

    private function serializeBlotter(Blotter $blotter): array
    {
        $respondent = $blotter->respondents->first();

        return [
            'id' => $blotter->id,
            'case_number' => $blotter->case_number,
            'complainant_name' => $blotter->complainant_name ?: $this->complainantName($blotter),
            'respondent_name' => $respondent?->respondent_name ?: ($respondent?->respondent ? $this->residentName($respondent->respondent) : ''),
            'location' => $blotter->location,
            'incident_description' => $blotter->incident_description,
            'incident_date' => $blotter->incident_date?->format('Y-m-d\TH:i'),
            'status' => $blotter->status,
        ];
    }

    private function syncRespondentName(Blotter $blotter, ?string $respondentName): void
    {
        $respondentName = trim((string) $respondentName);

        if ($respondentName === '') {
            return;
        }

        BlotterRespondent::updateOrCreate(
            ['blotter_id' => $blotter->id, 'respondent_id' => null],
            ['respondent_name' => $respondentName, 'role' => 'Respondent']
        );
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'open' => 'pending',
            'ongoing' => 'under investigation',
            default => $status,
        };
    }

    private function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status ?? ''] ?? str($status ?? 'Pending')->headline()->toString();
    }

    private function complainantName(Blotter $blotter): string
    {
        if ($blotter->complainant_resident) {
            return $this->residentName($blotter->complainant_resident);
        }

        return $blotter->complainant_name ?: 'N/A';
    }

    private function residentName($resident): string
    {
        return trim(collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->last_name,
            $resident->suffix,
        ])->filter()->implode(' '));
    }

    private function incidentType(Blotter $blotter): string
    {
        $text = strtolower($blotter->incident_description . ' ' . $blotter->location);

        return match (true) {
            str_contains($text, 'noise') => 'Noise',
            str_contains($text, 'altercation'), str_contains($text, 'fight'), str_contains($text, 'physical') => 'Altercation',
            str_contains($text, 'theft'), str_contains($text, 'steal'), str_contains($text, 'stolen') => 'Theft',
            str_contains($text, 'damage'), str_contains($text, 'property') => 'Damage',
            str_contains($text, 'domestic') => 'Domestic',
            str_contains($text, 'trespass') => 'Trespass',
            default => 'Other',
        };
    }

    private function severity(Blotter $blotter): string
    {
        $text = strtolower($blotter->incident_description);

        if (str_contains($text, 'physical') || str_contains($text, 'theft') || str_contains($text, 'domestic')) {
            return 'High';
        }

        if (str_contains($text, 'damage') || str_contains($text, 'verbal') || $blotter->status === 'under investigation') {
            return 'Medium';
        }

        return 'Low';
    }
}
