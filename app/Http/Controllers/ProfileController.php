<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'title' => 'Profile Settings',
            'user_details' => auth()->user()->details,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $details = [
            'birthdate' => $request->birthdate,
            'bio' => $request->bio,
            'education' => $request->education,
            'position' => $request->position,
            'join_date' => $request->join_date,
            'hobbies' => $request->hobbies,
            'skills' => $request->skills,
        ];

        if($user->details){
            $user->details->update($details);
        }else{
            $details['user_id'] = $user->id;
            UserDetail::create($details);
        }

        return redirect()->back();
    }

    public function update_avatar(Request $request)
    {
        $user = User::find(auth()->id());

        $avatar = $request->file('profile_photo_url')->store('avatar','public');

        $user->profile_photo_url = asset('storage/'.$avatar);

        $user->save();

        return redirect()->back();
    }

    public function show(): Response
    {
        return Inertia::render('Profile/Show', [
            'title' => 'Profile',
            'profile' => auth()->user(),
            'user_details' => auth()->user()->details,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
