<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Document;

trait LogsDocumentActivity
{
    protected function logDocumentActivity(string $action, Document $document, ?string $extra = null): void
    {
        $residentName = $document->resident
            ? trim($document->resident->first_name.' '.$document->resident->last_name)
            : 'Unknown resident';

        $templateName = $document->template?->name ?? 'Unknown template';

        $description = sprintf(
            '%s · %s · Ref %s',
            $residentName,
            $templateName,
            $document->reference_number
        );

        if ($extra) {
            $description .= ' · '.$extra;
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => 'Document Issuance',
            'description' => $description,
        ]);
    }
}
