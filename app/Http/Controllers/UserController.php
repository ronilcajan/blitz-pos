<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
        ->with('store','roles')
        ->orderBy('id', 'DESC')
        ->filter(request(['search', 'store']))
        ->whereNot('id', auth()->id()) //prevent current user to display
        ->paginate($request->per_page ? ($request->per_page == 'All' ? User::count() : $request->per_page) : 10)
        ->withQueryString()
        ->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'store' => $user->store->name ?? '',
                'stores_id' => $user->store->id ?? '',
                'role' => $user->roles[0]->name,
                'avatar' => $user->profile_photo_url,
                'created_at' => $user->created_at->format('M d, Y h:i: A'),
            ];
        });

        return inertia('Users/Index', [
            'title' => 'User',
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
