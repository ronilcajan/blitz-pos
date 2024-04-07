<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/auth/google', [GoogleAuthController::class, 'signInwithGoogle'])->name('google-auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callbackToGoogle']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('stores', StoreController::class);
    Route::post('/stores/update', [StoreController::class, 'update'])->name('store.update');
    Route::post('/stores/bulk/delete', [StoreController::class, 'bulkDelete'])->name('store.bulkDelete');

    Route::resource('users', UserController::class);
    Route::post('users/update', [UserController::class, 'update']);
    Route::post('users/reset', [UserController::class, 'reset']);
    Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete'])->name('user.bulkDelete');
    Route::get('/users/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('user.impersonate');
    Route::get('/users/leave/impersonation', [ImpersonateController::class, 'leave'])->name('user.leave');

});

require __DIR__.'/auth.php';
