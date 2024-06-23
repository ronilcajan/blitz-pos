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
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'avatar' => $user->profile_photo_url,
                        'store_id' => $user->store->id ?? '',
                        'store_name' => $user->store->name ?? '',
                        'currency' => $user->store->currency_symbol ?? '',
                        'isSuperAdmin' =>  $user_role === 'super-admin',
                        'isOwner' => $user_role === 'owner',
                        'isAdmin' => $user_role === 'admin',
                        'isStaff' => $user_role === 'staff',
                        'canDelete' => $user_role !== 'staff',
                        'impersonate' => session()->get('impersonate') ?? null
                    ] : null,
                ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'sale_id' => fn () => $request->session()->get('sale_id')
            ],
            'logo' => Vite::asset('resources/images/logo.png'),
        ];
    }
}
