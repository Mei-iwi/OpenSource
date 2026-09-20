<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function goodbye(Request $request): View|RedirectResponse
    {
        // Keep one deadline per logout, including refreshes of the goodbye page.
        $deadline = $request->session()->get('goodbye_deadline');
        if ($deadline === null) {
            $deadline = now()->getTimestampMs() + 5000;
            $request->session()->put('goodbye_deadline', $deadline);
        }

        $remainingMs = max(0, $deadline - now()->getTimestampMs());
        if ($remainingMs === 0) {
            return redirect()->route('login');
        }

        return view('auth.goodbye', compact('remainingMs'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Always resolve the dashboard through the authenticated user's role.
        // This prevents an Employee from being redirected back to a forbidden
        // Admin/HR URL stored in the session as the intended destination.
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('goodbye');
    }
}
