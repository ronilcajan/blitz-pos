<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function leave()
    {
        if(!session()->has('impersonate')){
            return abort(403);
        }

        auth()->login(User::withoutGlobalScopes()->find(session()->get('impersonate')));
        
        session()->forget('impersonate');

        return redirect()->back();

    }

    public function impersonate(User $user)
    {
        // $this->authorize('impersonate', User::class);
        
        if(!is_null(auth()->user()->store_id)){
            return abort(403);
        }

        $originalId = auth()->id();

        session()->put('impersonate', $originalId);

        Auth::loginUsingId($user->id);
        
        return redirect()->route('dashboard');

    }
}
