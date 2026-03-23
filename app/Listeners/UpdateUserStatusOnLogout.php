<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout as EventsLogout;

class UpdateUserStatusOnLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventsLogout $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'Logout',
            'module' => 'Authentication',
        ]);

        $event->user->update([
            'status' => 'Inactive',
        ]);
    }
}
