<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // 1. Tignan muna natin kung umiiral ang email sa database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // 2. Kung walang nahanap na user, i-log natin bilang 'null' ang user_id para hindi mag-crash ang database
            ActivityLog::create([
                'user_id' => null,
                'action' => 'Login Failed',
                'module' => 'Authentication',
                'description' => "Attempting to login with unregistered email: " . $request->email,
            ]);

            // 3. Ibato ang error pabalik sa screen ng user
            throw ValidationException::withMessages([
                'email' => __('This email is not registered in our system.'),
            ]);
        }

        // 4. Kung umiiral ang email, subukan nating i-authenticate (check password)
        try {
            $request->authenticate();
            $request->session()->regenerate();

            // I-log ang matagumpay na pag-login
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Login Success',
                'module' => 'Authentication',
                'description' => 'User logged in successfully.',
            ]);

            return redirect()->intended(route('dashboard')); // Siguraduhing tugma ito sa redirect route mo

        } catch (ValidationException $e) {
            // 5. Kung nag-error dito, ibig sabihin tama ang email pero MALI ANG PASSWORD
            ActivityLog::create([
                'user_id' => $user->id, // Dahil nahanap naman ang user sa itaas, may ID tayo rito
                'action' => 'Login Failed',
                'module' => 'Authentication',
                'description' => 'Password mismatch for registered email: ' . $request->email,
            ]);

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
