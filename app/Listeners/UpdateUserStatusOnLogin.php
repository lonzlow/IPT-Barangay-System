<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateUserStatusOnLogin
{
    public function handle(Login $event): void
    {
        $event->user->update([
            'last_accessed' => now(),
            'status' => 'Active',
        ]);
    }
}
