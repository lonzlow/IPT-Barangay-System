@php
    $resident = $official->resident;
    $fullName = $resident ? trim(collect([$resident->first_name, $resident->middle_name, $resident->last_name, $resident->suffix])->filter()->join(' ')) : 'Unlinked Resident';
    $roleName = $official->role?->role_name ?? 'Barangay Staff';
    $term = $official->term_end ? $official->term_start?->format('Y') . '-' . $official->term_end?->format('Y') : 'Appointive';
    $assignment = $official->assignments->first();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fullName }} - Digital ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #e2e8f0;
            font-family: Arial, sans-serif;
            color: #0f172a;
        }
        .toolbar {
            position: fixed;
            top: 18px;
            right: 18px;
            display: flex;
            gap: 8px;
        }
        .toolbar a, .toolbar button {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            border-radius: 8px;
            padding: 9px 13px;
            font: 700 13px Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }
        .id-card {
            width: 3.375in;
            height: 2.125in;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .22);
            border: 1px solid #cbd5e1;
            position: relative;
        }
        .id-header {
            background: #0f172a;
            color: #fff;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .seal {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .75);
            flex: 0 0 auto;
        }
        .barangay { font-size: 12px; font-weight: 800; line-height: 1.1; }
        .id-label { font-size: 8px; letter-spacing: 1px; color: #bfdbfe; margin-top: 2px; }
        .id-body {
            padding: 13px 14px;
            display: grid;
            grid-template-columns: 66px 1fr;
            gap: 12px;
        }
        .photo {
            width: 66px;
            height: 78px;
            border-radius: 8px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            display: grid;
            place-items: center;
            color: #1a56db;
            font-size: 28px;
            font-weight: 800;
        }
        .name { font-size: 14px; font-weight: 800; text-transform: uppercase; line-height: 1.12; margin-top: 1px; }
        .role { font-size: 10px; color: #1a56db; font-weight: 800; margin-top: 5px; }
        .meta { font-size: 9px; margin-top: 7px; line-height: 1.45; color: #334155; }
        .signature {
            position: absolute;
            right: 14px;
            bottom: 10px;
            text-align: center;
            font-size: 8px;
            color: #475569;
        }
        .signature-line { width: 92px; border-top: 1px solid #64748b; margin-bottom: 3px; }
        @media print {
            @page { size: 3.375in 2.125in; margin: 0; }
            body { background: #fff; min-height: 0; display: block; }
            .toolbar { display: none; }
            .id-card { box-shadow: none; border-radius: 0; border: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('officials.index') }}"><i class="bi bi-arrow-left"></i> Back</a>
        <button type="button" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print</button>
    </div>

    <section class="id-card" aria-label="Barangay staff digital ID">
        <div class="id-header">
            <img class="seal" src="{{ asset('images/logo/Barangay New Era Logo.jpg') }}" alt="Barangay New Era Seal">
            <div>
                <div class="barangay">BARANGAY NEW ERA</div>
                <div class="id-label">OFFICIAL / STAFF DIGITAL ID</div>
            </div>
        </div>
        <div class="id-body">
            <div class="photo">{{ Str::upper(Str::substr($fullName, 0, 1)) }}</div>
            <div>
                <div class="name">{{ $fullName }}</div>
                <div class="role">{{ $roleName }}</div>
                <div class="meta">
                    ID No.: <strong>{{ $official->official_number }}</strong><br>
                    Term: {{ $term }}<br>
                    Assignment: {{ $assignment?->committee?->name ?? 'Barangay Office' }}
                </div>
            </div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            Authorized Signature
        </div>
    </section>
</body>
</html>
