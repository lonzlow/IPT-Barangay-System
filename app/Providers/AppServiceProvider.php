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
        $secretary = ['Secretary', 'Barangay Secretary'];
        $treasurer = ['Treasurer', 'Barangay Treasurer'];
        $kagawad   = ['Kagawad'];
        $sk        = ['SK Chair', 'SK Chairperson'];
        $tanod     = ['Tanod', 'Barangay Tanod'];
        $bhw       = ['BHW', 'Health Worker / BHW'];
        $bdrrm     = ['BDRRM Coordinator'];
        $encoder   = ['Encoder', 'Encoder / Data Entry Clerk'];
        $auditor   = ['Auditor', 'Auditor / Inspector'];

        $recordsManagers = array_merge($admin, $secretary, $encoder);
        $recordsDeleters = array_merge($admin, $secretary);
        $readAll = array_merge($admin, $captain, $auditor);

        Gate::define('dashboard.view', fn (User $u) => true);

        Gate::define('residents.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary, $bhw, $bdrrm, $encoder)));
        Gate::define('residents.manage', fn (User $u) => $u->official?->hasAnyRole($recordsManagers));
        Gate::define('residents.delete', fn (User $u) => $u->official?->hasAnyRole($recordsDeleters));

        Gate::define('documents.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary, $treasurer, $encoder)));
        Gate::define('documents.manage', fn (User $u) => $u->official?->hasAnyRole($recordsManagers));
        Gate::define('documents.delete', fn (User $u) => $u->official?->hasAnyRole($recordsDeleters));
        Gate::define('documents.approve', fn (User $u) => $u->official?->hasAnyRole(array_merge($captain, $admin)));
        Gate::define('document-templates.manage', fn (User $u) => $u->official?->hasAnyRole($admin));

        Gate::define('blotter.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary, $tanod, $encoder)));
        Gate::define('blotter.manage', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary, $tanod, $encoder)));
        Gate::define('blotter.delete', fn (User $u) => $u->official?->hasAnyRole($recordsDeleters));

        Gate::define('households.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary, $bhw, $bdrrm, $encoder)));
        Gate::define('households.manage', fn (User $u) => $u->official?->hasAnyRole($recordsManagers));
        Gate::define('households.delete', fn (User $u) => $u->official?->hasAnyRole($recordsDeleters));

        Gate::define('business.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary, $treasurer, $encoder)));
        Gate::define('business.manage', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary, $treasurer, $encoder)));
        Gate::define('business.permits.manage', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary, $treasurer)));
        Gate::define('business.delete', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary, $treasurer)));

        Gate::define('officials.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($readAll, $secretary)));
        Gate::define('officials.manage', fn (User $u) => $u->official?->hasAnyRole($admin));
        Gate::define('committees.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($captain, $admin, $secretary, $kagawad, $sk, $bhw, $bdrrm, $auditor)));
        Gate::define('reports.view', fn (User $u) => $u->official?->hasAnyRole(array_merge($captain, $admin, $secretary, $treasurer, $auditor)));
        Gate::define('users.view', fn (User $u) => $u->official?->hasAnyRole($admin));

        Gate::define('committees.manage', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary)));
        Gate::define('committee-records.manage', fn (User $u) => $u->official?->hasAnyRole(array_merge($admin, $secretary, $kagawad, $sk, $bhw, $bdrrm)));
        Gate::define('signatures.manage', fn (User $u) => $u->official?->hasAnyRole($admin));
    }
}
