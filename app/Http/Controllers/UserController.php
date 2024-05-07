<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $perPage = $request->per_page
        ? ($request->per_page == 'All' ? Store::count() : $request->per_page)
        : 10;

        $users = User::query()
            ->with('store','roles')
            ->orderBy('id', 'DESC')
            ->filter(request(['search', 'store','status']))
            ->whereNot('id', auth()->id()) //prevent current user to display
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'store' => $user->store?->name,
                    'role' => $user->roles[0]?->name,
                    'status' => $user->status->getLabelText(),
                    'statusColor' => $user->status->getLabelColor(),
                    'avatar' => $user->profile_photo_url,
                    'created_at' => $user->created_at->format('M d, Y h:i: A'),
                ];
            });

        $userSummary = User::query()
            ->with('store','roles')->get()->map(function($user){
                return [
                    'id' => $user->id,
                    'status' => $user->status->getLabelText(),
                ];
            });

        return inertia('Users/Index', [
            'title' => 'Users',
            'users' => $users,
            'userSummary' => $userSummary,
            'stores' => Store::select('id', 'name')->orderBy('name','ASC')->get(),
            'roles' => Role::select('id','name')->orderBy('name','ASC')->get(),
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

        $request->validated();

        $validate = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'store_id' => $request->store_id,
        ];

        if($request->hasFile('profile_photo_url')){
            $avatar = $request->file('profile_photo_url')->store('users','public');
            $validate['profile_photo_url'] = asset('storage/'. $avatar);
        }

        $validate['password'] = bcrypt($request->email); // default password

        $user = User::create($validate);

        UserDetail::create(['user_id' =>  $user->id]);

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

        auth()->user()->can('update', $user);

        $validate = $request->validated();

        if($request->hasFile('profile_photo_url')){
            $avatar = $request->file('profile_photo_url')->store('users','public');
            $validate['profile_photo_url'] = asset('storage/'. $avatar);
        }

        $user->update($validate);
        $user->syncRoles([$request->role_id]); // parameter can be a Role object, BackedEnum, array, id or the role string name

        return redirect()->back();
    }

    public function updateStatus(Request $request)
    {
        $user = User::find($request->id);
        Gate::authorize('update', $user);
        $user->update(['status' => $request->status]);
        return redirect()->back();
    }



    public function reset(Request $request)
    {
        $user = User::find($request->id);
        Gate::authorize('reset', $user);

        $user->update(['password' => bcrypt($user->email)]);
        return redirect()->back();
    }

    public function export()
    {
        $filename = date('Y-m-d-H-i-s').'_users.xlsx';
        return Excel::download(new UsersExport, $filename);
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
