<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $document->reference_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .document-container {
            max-width: 8.5in;
            height: 11in;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 15px;
        }
        .barangay-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .government-text {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
        }
        .document-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 20px 0 15px 0;
            text-decoration: underline;
            color: #0f172a;
        }
        .reference-info {
            text-align: right;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 15px;
        }
        .content {
            text-align: justify;
            font-size: 12px;
            line-height: 1.8;
            color: #1e293b;
            margin-bottom: 30px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }
        .signature-block {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            margin-top: 30px;
            font-weight: bold;
            font-size: 12px;
            color: #0f172a;
        }
        .signature-title {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 72px;
            font-weight: 900;
            color: rgba(26, 86, 219, 0.05);
            pointer-events: none;
            z-index: -1;
            letter-spacing: 10px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: none;
            }
            .document-container {
                max-width: 100%;
                height: auto;
                margin: 0;
                padding: 40mm;
                box-shadow: none;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL</div>
    <div class="document-container">
        <div class="header">
            <div class="barangay-name">{{ config('app.barangay_name', 'BARANGAY') }}</div>
            <div class="government-text">Barangay Government</div>
        </div>

        <div class="reference-info">
            <strong>Ref No:</strong> {{ $document->reference_number }}<br>
            <strong>Date:</strong> {{ $document->issued_date->format('F d, Y') }}
        </div>

        <div class="document-title">{{ $document->template->name }}</div>

        <div class="content">
            {!! strip_tags($document->rendered_html, '<p><br><strong><em><u>') !!}
        </div>

        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line">{{ $document->issued_by }}</div>
                <div class="signature-title">Barangay Official</div>
            </div>
        </div>
    </div>
</body>
</html>
