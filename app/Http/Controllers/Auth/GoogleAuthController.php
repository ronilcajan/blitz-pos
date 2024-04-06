<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use SebastianBergmann\Type\Exception;

class GoogleAuthController extends Controller
{
    public function signInwithGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackToGoogle()
    {
        try {
            $user = Socialite::driver('google')->user();
            $findUser = User::where('gauth_id', $user->id)->first();
        
            if($findUser){
    
                Auth::login($findUser);
                return redirect()->route('dashboard')
                ->with('message', 'Welcome back! All system are running smoothly.');

        
            }else{
                return redirect('login')->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])->onlyInput('email');
            }
            
        } catch (Exception $e) {
            
            return redirect('login')->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
    }
}
