<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Prevent login for explicitly deactivated or soft-deleted users
        $user = \App\Models\User::withTrashed()->where('username', $request->username)->first();
        // Treat NULL (unknown) as active; only block when is_active is explicitly false
        if ($user && ($user->is_active === false || $user->trashed())) {
            throw ValidationException::withMessages([
                'username' => __('Your account is inactive. Please contact an administrator.'),
            ]);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login timestamp
            $user = Auth::user();
            if ($user) {
                $user->update(['last_login_at' => now()]);
            }

            // Log successful login
            $this->logger->logSystem('User logged in', [
                'ip'        => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard.index'));
        }

        throw ValidationException::withMessages([
            'username' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Log logout before actually destroying the session
        $this->logger->logSystem('User logged out', [
            'ip'        => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}