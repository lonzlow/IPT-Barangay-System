@php
    $sealPath = $document->seal_path ?: 'images/logo/Barangay New Era Logo.jpg';
    $sealSrc = !empty($isPdf)
        ? str_replace('\\', '/', public_path($sealPath))
        : asset($sealPath);
@endphp

<style>
    .issued-document {
        background: #fff;
        color: #111827;
        font-family: Arial, sans-serif;
        min-height: 960px;
        padding: 42px 48px;
        position: relative;
        border: 1px solid #e5e7eb;
    }

    .issued-document .doc-header {
        display: table;
        width: 100%;
        border-bottom: 3px solid #1a56db;
        padding-bottom: 16px;
        margin-bottom: 26px;
    }

    .issued-document .doc-seal,
    .issued-document .doc-heading {
        display: table-cell;
        vertical-align: middle;
    }

    .issued-document .doc-seal {
        width: 90px;
    }

    .issued-document .doc-seal img {
        width: 74px;
        height: 74px;
        object-fit: cover;
        border-radius: 50%;
    }

    .issued-document .doc-heading {
        text-align: center;
        padding-right: 90px;
    }

    .issued-document .doc-heading .republic {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .issued-document .doc-heading .barangay {
        font-size: 20px;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .issued-document .doc-heading .office {
        font-size: 13px;
        margin-top: 3px;
    }

    .issued-document .doc-meta {
        text-align: right;
        font-size: 12px;
        line-height: 1.6;
        margin-bottom: 24px;
    }

    .issued-document .doc-body {
        font-size: 14px;
        line-height: 1.75;
    }

    .issued-document .doc-body .title,
    .issued-document .doc-body h1,
    .issued-document .doc-body h2 {
        text-align: center;
        text-transform: uppercase;
        font-size: 20px;
        font-weight: 700;
        letter-spacing: .08em;
        margin: 0 0 28px;
        text-decoration: underline;
    }

    .issued-document .doc-body p {
        margin: 0 0 16px;
        text-align: justify;
    }

    .issued-document .doc-signatures {
        display: table;
        width: 100%;
        margin-top: 64px;
    }

    .issued-document .doc-signature-block {
        display: table-cell;
        width: 50%;
        text-align: center;
        vertical-align: bottom;
        font-size: 12px;
    }

    .issued-document .signature-name {
        display: inline-block;
        min-width: 230px;
        border-top: 1px solid #111827;
        padding-top: 7px;
        margin-top: 46px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .issued-document .doc-footer {
        margin-top: 34px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
        font-size: 10px;
        color: #6b7280;
        text-align: right;
    }

    @media print {
        .issued-document {
            border: 0;
            min-height: auto;
            padding: 0;
        }
    }
</style>

<div class="issued-document">
    <div class="doc-header">
        <div class="doc-seal">
            <img src="{{ $sealSrc }}" alt="Barangay seal">
        </div>
        <div class="doc-heading">
            <div class="republic">Republic of the Philippines</div>
            <div class="republic">City of Sample · District I</div>
            <div class="barangay">Barangay New Era</div>
            <div class="office">Office of the Punong Barangay</div>
        </div>
    </div>

    <div class="doc-meta">
        <strong>Reference No.:</strong> {{ $document->reference_number }}<br>
        <strong>Date Issued:</strong> {{ optional($document->issued_date)->format('F d, Y') }}<br>
        @if($document->valid_until)
            <strong>Valid Until:</strong> {{ $document->valid_until->format('F d, Y') }}
        @else
            <strong>Validity:</strong> No expiration
        @endif
    </div>

    <div class="doc-body">
        {!! $document->rendered_html !!}
    </div>

    <div class="doc-signatures">
        <div class="doc-signature-block">
            Requested by:
            <div class="signature-name">
                {{ trim(($document->resident?->first_name ?? '') . ' ' . ($document->resident?->last_name ?? '')) ?: 'Resident' }}
            </div>
            <div>Requesting Party</div>
        </div>
        <div class="doc-signature-block">
            Certified by:
            <div class="signature-name">{{ $document->issued_by }}</div>
            <div>Barangay Official</div>
        </div>
    </div>

    <div class="doc-footer">
        Control No.: <strong>{{ $document->reference_number }}</strong>
        @if($document->business)
            · Business: <strong>{{ $document->business->business_name }}</strong>
        @endif
    </div>
</div>
