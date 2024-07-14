<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Store;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Http;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return inertia('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $response = Http::get('https://api.ipgeolocation.io/ipgeo',[
            'apiKey' => '8aa952f04a194d639a762c8e66425c46',
        ])->json();

        $details = [
            'name' => $request->name,
            'founder' => $request->name,
            'email' => $request->email,
            'country' => $response['country_name'],
            'country_code' =>  $response['country_code3'],
            'timezone' => $response['time_zone']['name'],
            'currency' => $response['currency']['code'],
            'currency_symbol' => $response['currency']['symbol'],
            'flag' => $response['country_flag'],
        ];

        $store = Store::create($details);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'store_id' => $store->id,
        ]);

        event(new Registered($user));

        $user->addRole('owner');

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
