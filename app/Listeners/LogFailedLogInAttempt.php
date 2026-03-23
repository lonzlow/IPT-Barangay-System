<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogInAttempt
{
    public function handle(Failed $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user?->id,
            'action' => 'Login Failed',
            'module' => 'Authentication',
            'description' => 'Attempting to login with email: ' . $event->credentials['email'],
        ]);
    }
}
