<?php

namespace App\Providers;

use App\Listeners\LogFailedLogInAttempt;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\UpdateUserStatusOnLogin;
use App\Listeners\UpdateUserStatusOnLogout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            UpdateUserStatusOnLogin::class,
        ],
        Logout::class => [
            UpdateUserStatusOnLogout::class,
        ],
        Failed::class => [
            LogFailedLogInAttempt::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
