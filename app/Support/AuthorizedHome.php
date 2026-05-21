<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuthorizedHome
{
    /**
     * Keep this order aligned with the sidebar navigation.
     */
    private const ROUTES = [
        'residents.view' => 'residents.index',
        'documents.view' => 'documents.index',
        'blotter.view' => 'blotters.index',
        'households.view' => 'households.index',
        'business.view' => 'businesses.index',
        'officials.view' => 'officials.index',
        'committees.view' => 'committees.index',
        'reports.view' => 'reports.index',
        'users.view' => 'users.index',
    ];

    public static function routeName(User $user): string
    {
        foreach (self::ROUTES as $ability => $routeName) {
            if (Gate::forUser($user)->allows($ability)) {
                return $routeName;
            }
        }

        return 'profile.edit';
    }
}
