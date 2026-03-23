<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;

class UpdateUserStatusOnLogin
{
    public function handle(Login $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'Login Success',
            'module' => 'Authentication',
        ]);

        $event->user->update([
            'last_accessed' => now(),
            'status' => 'Active',
        ]);
    }
}
