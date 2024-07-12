<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Type\Exception;
use Laravel\Socialite\Facades\Socialite;

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
                ->with('message', 'Welcome to your dashboard!');

            }else{

                DB::beginTransaction();

                try {
                    $store = Store::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'contact' => '',
                        'address' => '',
                    ]);

                    $newUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'gauth_id'=> $user->id,
                        'password'=> $user->id,
                        'profile_photo_url'=> $user->avatar,
                        'store_id' =>  $store->id
                    ]);

                    $newUser->addRole('owner');

                    DB::commit();

                    Auth::login($newUser);

                    return redirect('stores/'.$store->id)
                        ->with('message', 'Please update your store details!');
                } catch (\Throwable $th) {
                    DB::rollBack();

                    return redirect()->back()->withErrors([
                        'email' => 'The provided credentials do not match our records.',
                    ])->onlyInput('email');
                }

            }

        } catch (Exception $e) {

            return redirect()->back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
    }
}
