<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpensesCategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
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
    Route::get('/profile/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-avatar', [ProfileController::class, 'update_avatar'])->name('profile.update_avatar');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');

    Route::resource('/orders', OrderController::class);
    Route::post('/orders/update', [OrderController::class, 'update']);

    Route::resource('/inventory', InventoryController::class);
    Route::post('/inventory/update', [InventoryController::class, 'update']);

    Route::resource('/products', ProductController::class);
    Route::get('/products/api/fetch/{barcode}', [ProductController::class, 'barcode_api'])->name('products.barcode.api');
    Route::post('/products/update', [ProductController::class, 'update']);
    Route::post('/products/bulk/delete', [ProductController::class, 'bulkDelete'])->name('products.bulkDelete');

    Route::resource('/product_categories', ProductCategoryController::class);
    Route::post('/product_categories/update', [ProductCategoryController::class, 'update']);
    Route::post('/product_categories/bulk/delete', [ProductCategoryController::class, 'bulkDelete'])->name('product_categories.bulkDelete');

    Route::resource('/product_units', ProductUnitController::class);
    Route::post('/product_units/update', [ProductUnitController::class, 'update']);
    Route::post('/product_units/bulk/delete', [ProductUnitController::class, 'bulkDelete'])->name('product_unit.bulkDelete');

    Route::resource('/expenses', ExpensesController::class);
    Route::post('/expenses/update', [ExpensesController::class, 'update']);
    Route::post('/expenses/change-status', [ExpensesController::class, 'change_status']);
    Route::post('/expenses/bulk/delete', [ExpensesController::class, 'bulkDelete'])->name('expenses.bulkDelete');

    Route::resource('/expenses_categories', ExpensesCategoryController::class);
    Route::post('/expenses_categories/update', [ExpensesCategoryController::class, 'update']);
    Route::post('/expenses_categories/bulk/delete', [ExpensesCategoryController::class, 'bulkDelete'])->name('expenses_categories.bulkDelete');

    Route::resource('/customers', CustomerController::class);
    Route::post('/customers/update', [CustomerController::class, 'update']);
    Route::post('/customers/bulk/delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulkDelete');

    Route::resource('/suppliers', SupplierController::class);
    Route::post('/suppliers/update', [SupplierController::class, 'update']);
    Route::post('/suppliers/bulk/delete', [SupplierController::class, 'bulkDelete'])->name('suppliers.bulkDelete');

    Route::resource('/stores', StoreController::class);
    Route::post('/stores/update', [StoreController::class, 'update'])->name('store.update');
    Route::post('/stores/bulk/delete', [StoreController::class, 'bulkDelete'])->name('store.bulkDelete');

    Route::resource('/users', UserController::class);
    Route::post('/users/update', [UserController::class, 'update']);
    Route::post('/users/update/status', [UserController::class, 'updateStatus'])->name('user.update.status');
    Route::post('/users/reset', [UserController::class, 'reset']);
    Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete'])->name('user.bulkDelete');
    Route::get('/users/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('user.impersonate');
    Route::get('/users/leave/impersonation', [ImpersonateController::class, 'leave'])->name('user.leave');

});

require __DIR__.'/auth.php';
