<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        if (Auth::user()->status->getLabelText() === 'active') {

            if(Auth::user()->hasRole('super-admin')) {
                return redirect()
                ->intended(route('admin.dashboard', absolute: true))
                ->with('message', "Welcome back, ".
                    auth()->user()->name.". Everything is up and running smoothly.");

            }

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('message', "Hello, ".
                    auth()->user()->name."! We're glad to see you. Your dashboard is ready.");

        } else {

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect to a different route or show an error message
            return redirect('login')->withErrors([
                'email' => 'You are not authorized to login.',
            ])->onlyInput('email');
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

        return redirect('/login');
    }
}
