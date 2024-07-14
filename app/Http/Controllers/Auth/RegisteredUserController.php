<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Store;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(string $plan)
    {
        $response_product = Http::withHeaders([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'Authorization' => 'Bearer ' . env('LEMON_SQUEEZY_API_KEY')
        ])->get('https://api.lemonsqueezy.com/v1/products/'.$plan);

        $response_variant = Http::withHeaders([
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'Authorization' => 'Bearer ' . env('LEMON_SQUEEZY_API_KEY')
        ])->get('https://api.lemonsqueezy.com/v1/variants', [
            'filter[product_id]' => $plan
        ]);

        $variants = $response_variant->json();
        $product = $response_product->json();

        return view('registration.register',[
            'title' => "Order Registration",
            'variants' => $variants,
            'product' => $product,
        ]);

        // return inertia('Auth/Register',[
        //     'title' => "Order Registration",
        //     'variants' => $variants,
        //     'product' => $product
        // ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        try {

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed'],
                'store' => ['required', 'string'],
                'country' => ['required', 'string'],
                'variant_id' => ['required', 'string'],
                'product_id' => ['required', 'string'],
            ]);

            $details = [
                'name' => $request->store,
                'founder' => $request->name,
                'email' => $request->store_email,
                'country' => $request->country,
                'timezone' => $request->timezone,
                'currency' => $request->currency,
            ];

            $store = Store::create($details);
    

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'store_id' => $store->id,
                'email_verified_at' => now(),
            ]);

            $user->addRole('owner');

            event(new Registered($user));

            Auth::login($user);


        } catch (\Throwable $th) {
            return redirect()->back();
        }

        return $request->user()->checkout($request->variant_id)
            ->withName($request->name)
            ->withEmail($request->email)
            ->withThankYouNote('Thanks for your subscription!')
            ->redirectTo(route('dashboard'));
        
    }
}
