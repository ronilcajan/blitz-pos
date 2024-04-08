<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return [
            ...parent::share($request),
            'auth' => [
                    'user' => $request->user() ? [
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'address' => $request->user()->address,
                    'avatar' => $request->user()->profile_photo_url,
                    'store_id' => $request->user()->store->id ?? '',
                    'isSuperAdmin' =>  $request->user()->roles[0]->name === 'super-admin',
                    'isOwner' => $request->user()->roles[0]->name === 'owner',
                    'isAdmin' => $request->user()->roles[0]->name === 'admin',
                    'isStaff' => $request->user()->roles[0]->name === 'staff',
                    'canDelete' => $request->user()->roles[0]->name === 'staff' ? false : true,
                    'impersonate' => session()->get('impersonate') ?? null
                    ] : null,
            ]
        ];
    }
}
