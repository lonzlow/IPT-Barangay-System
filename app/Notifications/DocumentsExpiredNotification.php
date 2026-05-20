<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DocumentsExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $count,
        public Collection $documents,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $references = $this->documents->pluck('reference_number')->take(15)->join(', ');

        return (new MailMessage)
            ->subject('Barangay Documents Auto-Expired')
            ->line("{$this->count} issued document(s) were automatically marked as Expired.")
            ->line('Reference numbers: '.$references)
            ->action('View Document Issuance', url('/documents'));
    }
}
