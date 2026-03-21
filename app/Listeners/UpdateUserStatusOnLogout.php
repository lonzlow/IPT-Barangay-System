<?php

namespace App\Listeners;

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
        $event->user->update([
            'status' => 'Inactive',
        ]);
    }
}
