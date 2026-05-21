<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->reference_number }}</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            margin: 0;
            background: #fff;
        }
    </style>
</head>
<body>
    @include('documents.partials.print-document', ['document' => $document, 'isPdf' => true])
</body>
</html>
