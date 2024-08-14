<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $user_role = $user->roles[0]->name ?? '';

        return [
            ...parent::share($request),
            'auth' => [
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'avatar' => $user->profile_photo_url,
                        'store_id' => $user->store->id ?? '',
                        'store_name' => $user->store->name ?? '',
                        'store_email' => $user->store->email ?? '',
                        'store_phone' => $user->store->phone ?? '',
                        'store_address' => $user->store->address ?? '',
                        'store_logo' => $user->store?->avatar ? asset('storage/'.$user->store->avatar) : '',
                        'currency' => $user->store->currency ?? '',
                        'isSuperAdmin' =>  $user->hasRole('superadministrator'),
                        'isOwner' =>  $user->hasRole('owner'),
                        'isAdmin' =>  $user->hasRole('admin'),
                        'isStaff' =>  $user->hasRole('staff'),
                        'canDelete' =>  !$user->hasRole('staff'),
                        'subscribed' => $user->subscribed() ?? false,
                        'impersonate' => session()->get('impersonate') ?? null
                    ] : null,
                ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            'logo' => Vite::asset('resources/images/logo.png'),
            'logo_white' => Vite::asset('resources/images/logo-white.png'),
            'product_image' => Vite::asset('resources/images/product.png'),
            'app_name' => config('app.name'),
            'app_lemon_squeezy_api' => config('services.lemonsqueezy.api_key'),
        ];
    }
}
