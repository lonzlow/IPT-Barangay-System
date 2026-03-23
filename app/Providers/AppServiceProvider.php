<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // POLICIES
        Gate::policies(User::class, UserPolicy::class);

        $admin     = ['Admin'];
        $captain   = ['Punong Barangay'];
        $secretary = ['Barangay Secretary'];
        $treasurer = ['Barangay Treasurer'];
        $kagawad   = ['Kagawad'];
        $sk        = ['SK Chairperson'];
        $tanod     = ['Barangay Tanod'];
        $bhw       = ['Health Worker / BHW'];
        $bdrrm     = ['BDRRM Coordinator'];
        $encoder   = ['Encoder / Data Entry Clerk'];
        $auditor   = ['Auditor'];

        Gate::define('dashboard.view', fn (User $u) => true);

        Gate::define('residents.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $bhw, $bdrrm, $encoder, $auditor)));
        Gate::define('documents.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $treasurer, $auditor)));
        Gate::define('blotter.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $tanod, $auditor)));
        Gate::define('households.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $bhw, $bdrrm, $encoder, $auditor)));
        Gate::define('business.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $treasurer, $auditor)));
        Gate::define('officials.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $auditor)));
        Gate::define('committees.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $kagawad, $sk, $bhw, $bdrrm, $auditor)));
        Gate::define('reports.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $treasurer, $auditor)));
        Gate::define('users.view', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin, $secretary, $auditor)));

        Gate::define('documents.approve', fn (User $u) => $u->hasAnyRole(array_merge($captain, $admin)));
    }
}
