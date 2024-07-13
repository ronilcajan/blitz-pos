<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function signInwithGoogle()
    {

        return Socialite::driver('google')
            ->redirect();
    }
    public function handleGoogleLoginCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->onlyInput('email');
        }

        $user = User::where('gauth_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if (!$user) {
            // Handle user not found case for login
            return redirect('/login')
                ->withErrors(['email' => 'No google account found with this email.']);
        }

        // dd(request()->segment(3));

        Auth::login($user, true);

        return redirect()->intended('/dashboard');
    }
}
