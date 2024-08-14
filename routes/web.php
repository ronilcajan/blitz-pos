<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpensesCategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ExportProductController;
use App\Http\Controllers\ExportSaleController;
use App\Http\Controllers\ExportSupplierController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\ImportProductController;
use App\Http\Controllers\InhouseStockTransactionController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register/{plan}', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

Route::get('/auth/google', [GoogleAuthController::class, 'signInwithGoogle'])->name('google-auth');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleLoginCallback']);

Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');



Route::group(['prefix' => 'admin', 'middleware' => ['auth','role:super-admin']], function() {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/stores', StoreController::class);
    // Route::resource('/users', AdminUserController::class);
});

Route::middleware(['auth', 'verified', 'timeZone'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    Route::resource('/logs', LogsController::class);
    Route::post('/logs/bulk/delete', [LogsController::class, 'bulkDelete'])->name('logs.bulkDelete');

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscription');

    Route::resource('/sales', SaleController::class);
    Route::post('/sales/bulk/delete', [SaleController::class, 'bulkDelete'])->name('sales.bulkDelete');
    Route::get('/sales/pdf/{sale}', [SaleController::class, 'downloadSalesInvoice'])->name('sales.downloadSalesInvoice');
    Route::post('/sales/update/status', [SaleController::class, 'updateStatus'])->name('sales.updateStatus');
    Route::get('/export/sales/', [ExportSaleController::class, 'export_excel'])->name('sales.export_excel');
    Route::get('/export/sales/pdf', [ExportSaleController::class, 'export_pdf'])->name('sales.export_pdf');

    Route::get('/pos', [POSController::class, 'index'])->name('pos');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    Route::post('/pos/get_product', [POSController::class, 'get_product'])->name('pos.get_product');

    Route::get('/profile/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/update-avatar', [ProfileController::class, 'update_avatar'])->name('profile.update_avatar');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');

    Route::resource('/purchase', PurchaseController::class);
    Route::post('/purchase/update', [PurchaseController::class, 'update']);
    Route::post('/purchase/bulk/delete', [PurchaseController::class, 'bulkDelete'])->name('purchase.bulkDelete');
    Route::get('/purchase/pdfview/{purchase}', [PurchaseController::class, 'downloadPDF'])->name('purchase.downloadPDF');

    Route::resource('/deliveries', DeliveryController::class);
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('delivery');
    Route::get('/deliveries/pdfview/{delivery}', [DeliveryController::class, 'downloadPDF'])->name('delivery.downloadPDF');
    Route::post('/deliveries/bulk/delete', [DeliveryController::class, 'bulkDelete'])->name('delivery.bulkDelete');

    Route::resource('/inventory', InventoryController::class);
    Route::post('/inventory/update', [InventoryController::class, 'update']);
    Route::patch('/inventory/stocks/update/{product}', [InventoryController::class, 'update_stocks']);
    Route::post('/inventory/stocks/bulk_update', [InventoryController::class, 'bulk_update']);

    Route::resource('/in_house', InhouseStockTransactionController::class);
    Route::post('/in_house/bulk_update', [InhouseStockTransactionController::class, 'bulkDelete'])->name('in_house.bulkDelete');

    Route::resource('/products', ProductController::class);
    Route::get('/products/api/fetch/{barcode}', [ProductController::class, 'barcode_api'])->name('products.barcode.api');
    // Route::post('/products/product/update', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/status', [ProductController::class, 'change_status'])->name('products.change_status');
    Route::post('/products/bulk/delete', [ProductController::class, 'bulkDelete'])->name('products.bulkDelete');

    Route::post('/check-barcode', [BarcodeController::class, 'checkBarcode'])
            ->name('product.check-barcode');
    Route::post('/get-barcode', [BarcodeController::class, 'getProducts'])
            ->name('product.get-products');

    Route::get('/export/products/', [ExportProductController::class, 'export_excel'])->name('products.export_excel');
    Route::get('/export/products/pdf', [ExportProductController::class, 'export_pdf'])->name('products.export_pdf');
    Route::get('/export/products/template', [ExportProductController::class, 'export_template'])->name('products.donwloadTemplate');
    Route::post('/import/products', ImportProductController::class)->name('products.import');

    Route::resource('/product_categories', ProductCategoryController::class);
    Route::post('/product_categories/update', [ProductCategoryController::class, 'update']);
    Route::post('/product_categories/bulk/delete', [ProductCategoryController::class, 'bulkDelete'])->name('product_categories.bulkDelete');

    Route::resource('/product_units', ProductUnitController::class);
    Route::post('/product_units/update', [ProductUnitController::class, 'update']);
    Route::post('/product_units/bulk/delete', [ProductUnitController::class, 'bulkDelete'])->name('product_unit.bulkDelete');

    Route::resource('/expenses', ExpensesController::class);
    Route::post('/expenses/update', [ExpensesController::class, 'update']);
    Route::post('/expenses/change-status/{expense}', [ExpensesController::class, 'change_status']);
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
    Route::get('/export/suppliers/excel', [ExportSupplierController::class, 'export_excel'])->name('suppliers.export_excel');
    Route::get('/export/suppliers/pdf', [ExportSupplierController::class, 'export_pdf'])->name('suppliers.export_pdf');
    Route::get('/export/suppliers/template', [ExportSupplierController::class, 'export_template'])->name('suppliers.donwloadTemplate');
    Route::post('/import/suppliers', [SupplierController::class, 'import'])->name('suppliers.import');

    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('store.show');
    Route::post('/stores/update', [StoreController::class, 'update'])->name('store.update');
    Route::post('/stores/bulk/delete', [StoreController::class, 'bulkDelete'])->name('store.bulkDelete');

    Route::resource('/users', UserController::class);
    Route::post('/users/update/profile', [UserController::class, 'update'])->name('user.update.profile');
    Route::post('/users/update/status', [UserController::class, 'updateStatus'])->name('user.update.status');
    Route::post('/users/reset', [UserController::class, 'reset']);
    Route::post('/users/bulk/delete', [UserController::class, 'bulkDelete'])->name('user.bulkDelete');
    Route::get('/users/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->name('user.impersonate');
    Route::get('/users/leave/impersonation', [ImpersonateController::class, 'leave'])->name('user.leave');
    Route::get('/users/export/data', [UserController::class, 'export'])->name('user.export');

    Route::get('/api/unread-activity', [NotificationController::class, 'unread']);
    Route::post('/api/mark-as-read', [NotificationController::class, 'mark_as_read']);

});

require __DIR__.'/auth.php';
