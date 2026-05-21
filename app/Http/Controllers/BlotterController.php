<?php

namespace App\Http\Controllers;

use App\Models\Blotter;
use App\Models\BlotterEvidence;
use App\Models\BlotterRespondent;
use App\Models\BlotterWitness;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class BlotterController extends Controller
{
    private const EVIDENCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'mp4', 'mov', 'avi', 'webm', 'mkv', 'mpeg', 'mpg'];

    private const EVIDENCE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/webm',
        'video/x-matroska',
        'video/mpeg',
    ];

    private const EVIDENCE_MAX_KB = 102400;
    private const EVIDENCE_MAX_FILES = 5;

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
        $this->authorize('blotter.view');

        return view('blotters.index', $this->dashboardPayload());
    }

    public function data(Request $request)
    {
        $this->authorize('blotter.view');

        $blotters = Blotter::query()
            ->with(['complainant_resident', 'respondents.respondent'])
            ->latest('incident_date');
        $canManage = $request->user()?->can('blotter.manage') ?? false;
        $canDelete = $request->user()?->can('blotter.delete') ?? false;

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
            ->addColumn('action', function (Blotter $blotter) use ($canManage, $canDelete) {
                $showUrl = route('blotters.show', $blotter);
                $editUrl = route('blotters.edit', $blotter);
                $deleteUrl = route('blotters.destroy', $blotter);

                $actions = '<div class="btn-group btn-group-sm" role="group" aria-label="Blotter actions">';
                $actions .= '<a href="' . e($showUrl) . '" class="btn btn-light" title="View"><i class="bi bi-eye"></i></a>';

                if ($canManage) {
                    $actions .= '<button type="button" class="btn btn-light" data-blotter-action="edit" data-blotter-id="' . e($blotter->id) . '" data-blotter-edit-url="' . e($editUrl) . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                }

                if ($canDelete) {
                    $actions .= '<button type="button" class="btn btn-light" data-blotter-action="delete" data-blotter-id="' . e($blotter->id) . '" data-blotter-delete-url="' . e($deleteUrl) . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }

                $actions .= '</div>';

                return $actions;
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
        $this->authorize('blotter.view');

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

    public function residentsSearch(Request $request)
    {
        $this->authorize('blotter.manage');

        $search = trim((string) $request->query('q', ''));

        $residents = Resident::query()
            ->select(['id', 'resident_number', 'first_name', 'middle_name', 'last_name', 'suffix'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('resident_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->limit(20)
            ->get()
            ->map(fn (Resident $resident) => [
                'id' => $resident->id,
                'text' => trim($this->residentName($resident) . ' - ' . $resident->resident_number),
                'name' => $this->residentName($resident),
            ]);

        return response()->json(['results' => $residents]);
    }

    public function store(Request $request)
    {
        $this->authorize('blotter.manage');

        $validated = $this->validatedBlotter($request);
        $respondents = $this->normalizePeople($validated['respondents'] ?? [], 'respondent');
        $witnesses = $this->normalizePeople($validated['witnesses'] ?? [], 'witness');
        $evidences = $validated['evidences'] ?? [];
        unset($validated['case_number'], $validated['complainant_mode'], $validated['respondent_name'], $validated['respondents'], $validated['witnesses'], $validated['evidences']);

        $blotter = DB::transaction(function () use ($validated, $respondents, $witnesses, $evidences) {
            $validated['status'] = $this->normalizeStatus($validated['status']);
            $validated['filed_by'] = auth()->id();
            $validated['case_number'] = $this->nextCaseNumber();
            $validated = $this->normalizeComplainant($validated);

            $blotter = Blotter::create($validated);
            $this->syncRespondents($blotter, $respondents);
            $this->syncWitnesses($blotter, $witnesses);
            $this->storeEvidences($blotter, $evidences);

            return $blotter;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter record saved successfully.',
                'data' => $blotter->load(['filedBy', 'respondents.respondent', 'witnesses.resident_witness', 'evidences']),
                'dashboard' => $this->dashboardPayload(),
            ], 201);
        }

        return redirect()->route('blotters.index')->with('success', 'Blotter created successfully.');
    }

    public function show(Request $request, string $blotter)
    {
        $this->authorize('blotter.view');

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
        $this->authorize('blotter.manage');

        $blotter->load(['complainant_resident', 'respondents.respondent', 'witnesses.resident_witness', 'evidences']);

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
        $this->authorize('blotter.manage');

        $validated = $this->validatedBlotter($request, $blotter);
        $respondents = $this->normalizePeople($validated['respondents'] ?? [], 'respondent');
        $witnesses = $this->normalizePeople($validated['witnesses'] ?? [], 'witness');
        $evidences = $validated['evidences'] ?? [];
        unset($validated['case_number'], $validated['complainant_mode'], $validated['respondent_name'], $validated['respondents'], $validated['witnesses'], $validated['evidences']);

        DB::transaction(function () use ($blotter, $validated, $respondents, $witnesses, $evidences) {
            $validated['status'] = $this->normalizeStatus($validated['status']);
            $validated = $this->normalizeComplainant($validated);
            $blotter->update($validated);
            $this->syncRespondents($blotter, $respondents);
            $this->syncWitnesses($blotter, $witnesses);
            $this->storeEvidences($blotter, $evidences);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Blotter record updated successfully.',
                'data' => $blotter->fresh()->load(['filedBy', 'respondents.respondent', 'witnesses.resident_witness', 'evidences']),
                'dashboard' => $this->dashboardPayload(),
            ]);
        }

        return redirect()->route('blotters.show', $blotter)->with('success', 'Blotter updated successfully.');
    }

    public function destroy(Blotter $blotter, Request $request)
    {
        $this->authorize('blotter.delete');

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
        $this->authorize('blotter.manage');

        $blotter = Blotter::query()
            ->whereKey($blotter)
            ->orWhere('case_number', $blotter)
            ->firstOrFail();

        $validated = $request->validate([
            'evidence' => $this->evidenceValidationRules(required: true),
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $validated['evidence'];
        $path = $file->store('evidence/blotters/' . $blotter->id, 'public');
        $extension = strtolower($file->getClientOriginalExtension());
        $category = $this->fileCategory($file->getMimeType(), $extension);

        $evidence = BlotterEvidence::create([
            'blotter_id' => $blotter->id,
            'file_path' => Storage::url($path),
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
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
            'case_number' => ['nullable', 'string', 'max:255'],
            'incident_title' => ['nullable', 'string', 'max:255'],
            'complainant_mode' => ['nullable', Rule::in(['resident', 'manual'])],
            'complainant_name' => ['required_without:complainant_id', 'nullable', 'string', 'max:255'],
            'complainant_id' => ['nullable', 'exists:residents,id'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'respondents' => ['nullable', 'array'],
            'respondents.*.mode' => ['nullable', Rule::in(['resident', 'manual'])],
            'respondents.*.resident_id' => ['nullable', 'exists:residents,id'],
            'respondents.*.name' => ['nullable', 'string', 'max:255'],
            'witnesses' => ['nullable', 'array'],
            'witnesses.*.mode' => ['nullable', Rule::in(['resident', 'manual'])],
            'witnesses.*.resident_id' => ['nullable', 'exists:residents,id'],
            'witnesses.*.name' => ['nullable', 'string', 'max:255'],
            'evidences' => ['nullable', 'array', 'max:' . self::EVIDENCE_MAX_FILES],
            'evidences.*' => $this->evidenceValidationRules(),
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
            'blotterReferences' => $blotters->map(fn (Blotter $blotter) => [
                'id' => $blotter->id,
                'case_number' => $blotter->case_number,
                'label' => trim($blotter->case_number . ' - ' . ($blotter->incident_title ?: 'Untitled incident')),
            ])->values(),
        ];
    }

    private function serializeBlotter(Blotter $blotter): array
    {
        $respondent = $blotter->respondents->first();

        return [
            'id' => $blotter->id,
            'case_number' => $blotter->case_number,
            'incident_title' => $blotter->incident_title,
            'complainant_id' => $blotter->complainant_id,
            'complainant_name' => $blotter->complainant_name ?: $this->complainantName($blotter),
            'respondent_name' => $respondent?->respondent_name ?: ($respondent?->respondent ? $this->residentName($respondent->respondent) : ''),
            'respondents' => $blotter->respondents->map(fn (BlotterRespondent $respondent) => [
                'resident_id' => $respondent->respondent_id,
                'name' => $respondent->respondent_name ?: ($respondent->respondent ? $this->residentName($respondent->respondent) : ''),
            ])->values(),
            'witnesses' => $blotter->witnesses->map(fn (BlotterWitness $witness) => [
                'resident_id' => $witness->witness_id,
                'name' => $witness->witness_name ?: ($witness->resident_witness ? $this->residentName($witness->resident_witness) : ''),
            ])->values(),
            'location' => $blotter->location,
            'incident_description' => $blotter->incident_description,
            'incident_date' => $blotter->incident_date?->format('Y-m-d\TH:i'),
            'status' => $blotter->status,
        ];
    }

    private function syncRespondents(Blotter $blotter, array $respondents): void
    {
        $blotter->respondents()->delete();

        foreach ($respondents as $respondent) {
            BlotterRespondent::create([
                'blotter_id' => $blotter->id,
                'respondent_id' => $respondent['resident_id'],
                'respondent_name' => $respondent['name'],
                'role' => 'Respondent',
            ]);
        }
    }

    private function syncWitnesses(Blotter $blotter, array $witnesses): void
    {
        $blotter->witnesses()->delete();

        foreach ($witnesses as $witness) {
            BlotterWitness::create([
                'blotter_id' => $blotter->id,
                'witness_id' => $witness['resident_id'],
                'witness_name' => $witness['name'],
            ]);
        }
    }

    private function normalizePeople(array $people, string $type): array
    {
        if ($type === 'respondent' && $people === [] && request()->filled('respondent_name')) {
            $people[] = ['mode' => 'manual', 'resident_id' => null, 'name' => request()->input('respondent_name')];
        }

        return collect($people)
            ->map(function (array $person) {
                $mode = $person['mode'] ?? null;
                $residentId = $person['resident_id'] ?? null;
                $name = trim((string) ($person['name'] ?? ''));

                if ($mode === 'resident' || ($mode === null && $residentId)) {
                    return [
                        'resident_id' => $residentId ?: null,
                        'name' => null,
                    ];
                }

                return [
                    'resident_id' => null,
                    'name' => $name ?: null,
                ];
            })
            ->filter(fn (array $person) => $person['resident_id'] || $person['name'])
            ->values()
            ->all();
    }

    private function normalizeComplainant(array $validated): array
    {
        if (! empty($validated['complainant_id'])) {
            $validated['complainant_name'] = null;
        } else {
            $validated['complainant_id'] = null;
            $validated['complainant_name'] = trim((string) ($validated['complainant_name'] ?? '')) ?: null;
        }

        return $validated;
    }

    private function nextCaseNumber(): string
    {
        $lastNumber = Blotter::withTrashed()
            ->where('case_number', 'like', 'BL-%')
            ->lockForUpdate()
            ->pluck('case_number')
            ->reduce(function (int $highest, string $caseNumber) {
                if (preg_match('/^BL-(\d{6})$/', $caseNumber, $matches) !== 1) {
                    return $highest;
                }

                return max($highest, (int) $matches[1]);
            }, 0);

        return 'BL-' . str_pad((string) ($lastNumber + 1), 6, '0', STR_PAD_LEFT);
    }

    private function storeEvidences(Blotter $blotter, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('evidence/blotters/' . $blotter->id, 'public');
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();

            BlotterEvidence::create([
                'blotter_id' => $blotter->id,
                'file_path' => Storage::url($path),
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $mimeType,
                'file_extension' => $extension,
                'mime_type' => $mimeType,
                'file_category' => $this->fileCategory($mimeType, $extension),
                'caption' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function evidenceValidationRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:' . implode(',', self::EVIDENCE_EXTENSIONS),
            'mimetypes:' . implode(',', self::EVIDENCE_MIME_TYPES),
            'max:' . self::EVIDENCE_MAX_KB,
        ];
    }

    private function fileCategory(?string $mimeType, string $extension): string
    {
        if (str_starts_with((string) $mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with((string) $mimeType, 'video/')) {
            return 'video';
        }

        return in_array($extension, ['jpg', 'jpeg', 'png'], true) ? 'image' : 'document';
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
        if ($blotter->incident_title) {
            return $blotter->incident_title;
        }

        $description = strtolower($blotter->incident_description);

        return match (true) {
            str_contains($description, 'noise') => 'Noise',
            str_contains($description, 'altercation') || str_contains($description, 'physical') || str_contains($description, 'verbal') => 'Altercation',
            str_contains($description, 'theft') => 'Theft',
            str_contains($description, 'damage') => 'Damage',
            str_contains($description, 'domestic') => 'Domestic',
            str_contains($description, 'trespass') => 'Trespass',
            default => 'N/A',
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
