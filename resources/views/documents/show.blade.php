@extends('layouts.app')

@section('title', 'View Document')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .main-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .card-modern {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .card-modern .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
        }

        .card-modern .card-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .card-modern .card-body {
            padding: 22px;
        }

        .doc-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px;
        }

        .doc-summary-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            margin-bottom: 10px;
        }

        .doc-summary-value {
            font-size: 13px;
            color: #0f172a;
            margin: 0;
        }

        .doc-preview-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="main-container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Document Details</h1>
                <div class="text-muted">{{ $document->reference_number }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Back</a>
                <a href="{{ route('documents.downloadPdf', $document->id) }}" class="btn btn-primary">Download PDF</a>
            </div>
        </div>

        <div class="card card-modern">
            <div class="card-header">
                <h5><i class="bi bi-search me-2"></i>Find Document Record</h5>
            </div>
            <div class="card-body">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6">
                        <label for="showResidentSelect" class="form-label"><strong>Resident</strong></label>
                        <select id="showResidentSelect" class="form-control">
                            <option value=""></option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}" @selected($document->resident_id === $resident->id)>
                                    {{ $resident->first_name }} {{ $resident->last_name }}
                                    ({{ $resident->household?->purok?->purok_name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label for="showTemplateSelect" class="form-label"><strong>Document Type</strong></label>
                        <select id="showTemplateSelect" class="form-control">
                            <option value=""></option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @selected($document->document_template_id === $template->id)>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="doc-summary mt-4">
                    <div class="doc-summary-label">Selected Record</div>
                    <p class="doc-summary-value mb-2"><strong>Purpose:</strong> {{ $document->purpose ?: 'No purpose provided' }}</p>
                    <p class="doc-summary-value mb-2"><strong>Additional Notes:</strong> {{ $document->additional_notes ?: 'No additional notes' }}</p>
                    <p class="doc-summary-value mb-2"><strong>Business:</strong> {{ $document->business?->business_name ?? 'N/A' }}</p>
                    <p class="doc-summary-value mb-2"><strong>Issued By:</strong> {{ $document->issued_by }}</p>
                    <p class="doc-summary-value mb-2"><strong>Status:</strong> {{ $document->status }}</p>
                    <p class="doc-summary-value mb-2"><strong>Issued Date:</strong> {{ optional($document->issued_date)->format('M d, Y') }}</p>
                    <p class="doc-summary-value mb-0"><strong>Valid Until:</strong> {{ $document->valid_until ? $document->valid_until->format('M d, Y') : 'No expiration' }}</p>
                </div>
            </div>
        </div>

        <div class="card card-modern">
            <div class="card-header">
                <h5><i class="bi bi-file-earmark-text me-2"></i>Rendered Document</h5>
            </div>
            <div class="card-body">
                <div class="doc-preview-wrap p-4 bg-light">
                    @include('documents.partials.print-document', ['document' => $document])
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.$ !== 'undefined' && typeof $.fn.select2 === 'function') {
                $('#showResidentSelect, #showTemplateSelect').select2({
                    width: '100%',
                });
            }
        });
    </script>
@endsection
