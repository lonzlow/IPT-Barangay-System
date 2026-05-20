<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentsExpiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ExpireDocuments extends Command
{
    protected $signature = 'documents:expire {--notify : Send expiry notifications to document staff}';

    protected $description = 'Mark issued documents past their valid_until date as Expired';

    public function handle(): int
    {
        $documents = Document::query()
            ->where('status', 'Issued')
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', today())
            ->with(['resident', 'template'])
            ->get();

        if ($documents->isEmpty()) {
            $this->info('No documents to expire.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($documents as $document) {
            $document->update(['status' => 'Expired']);
            $count++;
        }

        ActivityLog::create([
            'user_id' => null,
            'action' => 'Auto-Expired Documents',
            'module' => 'Document Issuance',
            'description' => sprintf(
                '%d document(s) marked as Expired: %s',
                $count,
                $documents->pluck('reference_number')->take(10)->join(', ')
            ),
        ]);

        $this->info("Marked {$count} document(s) as Expired.");

        if ($this->option('notify') || config('documents.expiry_notify')) {
            $this->notifyStaff($documents, $count);
        }

        return self::SUCCESS;
    }

    private function notifyStaff($documents, int $count): void
    {
        $recipients = User::query()
            ->where('status', 'Active')
            ->whereHas('official.role', fn ($q) => $q->whereIn('role_name', [
                'Admin',
                'Punong Barangay',
                'Barangay Secretary',
            ]))
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('No staff users found to notify.');

            return;
        }

        Notification::send($recipients, new DocumentsExpiredNotification($count, $documents));

        $this->info('Expiry notifications sent to '.$recipients->count().' user(s).');
    }
}
