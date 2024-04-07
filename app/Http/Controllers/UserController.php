<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with('store','roles')
            ->orderBy('id', 'DESC')
            ->filter(request(['search', 'store']))
            ->whereNot('id', auth()->id()) //prevent current user to display
            ->paginate($request->per_page ? ($request->per_page == 'All' ? User::count() : $request->per_page) : 10)
            ->withQueryString()
            ->through(function ($user) {
                // dd($user);
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'store' => $user->store->name ?? '',
                    'role' => $user->roles[0]->name ?? '',
                    'avatar' => $user->profile_photo_url,
                    'created_at' => $user->created_at->format('M d, Y h:i: A'),
                ];
            });

        return inertia('Users/Index', [
            'title' => 'Users',
            'users' => $users,
            'stores' => Store::select('id', 'name')->get(),
            'roles' => Role::select('id','name')->get(),
            'filter' => $request->only(['search','store','per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create',  User::class);

        return inertia('Users/Create', [
            'title' => "Add New User",
            'stores' => Store::select('id', 'name')->get(),
            'roles' => Role::select('id','name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserFormRequest $request)
    {
        Gate::authorize('create',  User::class);

        $validate = $request->validated();

        if($request->hasFile('profile_photo_url')){
            $avatar = $request->file('profile_photo_url')->store('users','public');
            $validate['profile_photo_url'] = asset('storage/'. $avatar);
        }

        $validate['password'] = bcrypt($request->email); // default password
        
        $user = User::create($validate);

        $user->addRole($request->role_id); // parameter can be a Role object, BackedEnum, array, id or the role string name

        return redirect()->back();
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
    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        $employee = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'store_id' => $user->store->id ?? '',
            'role' => $user->roles()->first(['id', 'name'])->toArray(),
            'profile_photo_url' => $user->profile_photo_url,
        ];

        return inertia('Users/Edit', [
            'title' => "Edit User",
            'employee' => $employee,
            'stores' => Store::select('id', 'name')->get(),
            'roles' => Role::select('id','name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserFormRequest $request)
    {
        $user = User::find($request->id);
        Gate::authorize('update', $user);

        $validate = $request->validated();

        if($request->hasFile('profile_photo_url')){
            $avatar = $request->file('profile_photo_url')->store('users','public');
            $validate['profile_photo_url'] = asset('storage/'. $avatar);
        }
        
        $user->update($validate);
        $user->syncRoles([$request->role_id]); // parameter can be a Role object, BackedEnum, array, id or the role string name

        return redirect()->back();
    }

    public function reset(Request $request)
    {   
        $user = User::find($request->id);
        Gate::authorize('reset', $user);

        $user->update(['password' => bcrypt($user->email)]);
        return redirect()->back();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function bulkDelete(Request $request)
    {
        Gate::authorize('bulk_delete', User::class);

        User::whereIn('id',$request->users_id)->delete();
        return redirect()->back();
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();
        return redirect()->back();
    }
}
