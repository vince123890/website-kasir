<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\Inventory\StockOpnameController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\UnpackingController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\POSController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Users Management Routes
    Route::resource('users', UserController::class);
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::post('/users/{id}/logout-all', [UserController::class, 'logoutAllSessions'])->name('users.logout-all');
    Route::post('/users/{id}/send-activation', [UserController::class, 'sendActivationEmail'])->name('users.send-activation');
    Route::post('/users/{id}/force-password-change', [UserController::class, 'forcePasswordChange'])->name('users.force-password-change');
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');

    // Admin Routes (Super Admin only)
    Route::prefix('admin')->name('admin.')->middleware('role:Administrator SaaS')->group(function () {
        // Users Management
        Route::resource('users', UserController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);

        // Tenants Management
        Route::resource('tenants', TenantController::class);
        Route::post('/tenants/{id}/restore', [TenantController::class, 'restore'])->name('tenants.restore');
        Route::post('/tenants/{id}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('/tenants/{id}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
    });

    // Staff Routes (Admin Toko)
    Route::prefix('staff')->name('staff.')->middleware('role:Admin Toko')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Stores Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->group(function () {
        Route::resource('stores', StoreController::class);
        Route::get('/stores/{id}/settings', [StoreController::class, 'settings'])->name('stores.settings');
        Route::put('/stores/{id}/settings', [StoreController::class, 'updateSettings'])->name('stores.updateSettings');
    });

    // Categories Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulkDelete');
        Route::get('/categories/export', [CategoryController::class, 'export'])->name('categories.export');
    });

    // Products Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->group(function () {
        Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.downloadTemplate');
        Route::resource('products', ProductController::class);
        Route::post('/products/bulk-import', [ProductController::class, 'bulkImport'])->name('products.bulkImport');
        Route::post('/products/bulk-price-update', [ProductController::class, 'bulkPriceUpdate'])->name('products.bulkPriceUpdate');
        Route::get('/products/{id}/price-history', [ProductController::class, 'priceHistory'])->name('products.priceHistory');
        Route::post('/products/{id}/override-price', [ProductController::class, 'overrideStorePrice'])->name('products.overrideStorePrice');
    });

    // Suppliers Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->group(function () {
        Route::get('/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::resource('suppliers', SupplierController::class);
        Route::get('/suppliers/{id}/history', [SupplierController::class, 'purchaseHistory'])->name('suppliers.history');
    });

    // Purchase Orders Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->group(function () {
        Route::get('/purchases', [PurchaseOrderController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseOrderController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{id}', [PurchaseOrderController::class, 'show'])->name('purchases.show');
        Route::get('/purchases/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchases/{id}', [PurchaseOrderController::class, 'update'])->name('purchases.update');
        Route::delete('/purchases/{id}', [PurchaseOrderController::class, 'destroy'])->name('purchases.destroy');
        Route::post('/purchases/{id}/submit', [PurchaseOrderController::class, 'submit'])->name('purchases.submit');
        Route::post('/purchases/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('purchases.approve');
        Route::post('/purchases/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('purchases.reject');
        Route::post('/purchases/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
    });

    // Stock Opname Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->prefix('inventory/opname')->name('inventory.opname.')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StockOpnameController::class, 'create'])->name('create');
        Route::post('/', [StockOpnameController::class, 'store'])->name('store');
        Route::get('/{id}', [StockOpnameController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StockOpnameController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StockOpnameController::class, 'update'])->name('update');
        Route::delete('/{id}', [StockOpnameController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit', [StockOpnameController::class, 'submit'])->name('submit');
        Route::post('/{id}/approve', [StockOpnameController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [StockOpnameController::class, 'reject'])->name('reject');
        Route::post('/{id}/finalize', [StockOpnameController::class, 'finalize'])->name('finalize');
    });

    // Stock Adjustment Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->prefix('inventory/adjustments')->name('inventory.adjustments.')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create');
        Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
        Route::get('/{id}', [StockAdjustmentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StockAdjustmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StockAdjustmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [StockAdjustmentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit', [StockAdjustmentController::class, 'submit'])->name('submit');
        Route::post('/{id}/approve', [StockAdjustmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [StockAdjustmentController::class, 'reject'])->name('reject');
        Route::post('/{id}/apply', [StockAdjustmentController::class, 'apply'])->name('apply');
    });

    // Unpacking Management Routes (Tenant Owner & Admin Toko)
    Route::middleware('role:Tenant Owner|Admin Toko')->prefix('inventory/unpacking')->name('inventory.unpacking.')->group(function () {
        Route::get('/', [UnpackingController::class, 'index'])->name('index');
        Route::get('/create', [UnpackingController::class, 'create'])->name('create');
        Route::post('/', [UnpackingController::class, 'store'])->name('store');
        Route::get('/{id}', [UnpackingController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UnpackingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UnpackingController::class, 'update'])->name('update');
        Route::delete('/{id}', [UnpackingController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit', [UnpackingController::class, 'submit'])->name('submit');
        Route::post('/{id}/approve', [UnpackingController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [UnpackingController::class, 'reject'])->name('reject');
        Route::post('/{id}/process', [UnpackingController::class, 'process'])->name('process');
    });

    // Store Sessions Management Routes (Kasir, Admin Toko, Tenant Owner)
    Route::middleware('role:Kasir|Admin Toko|Tenant Owner')->prefix('sessions')->name('sessions.')->group(function () {
        Route::get('/', [SessionController::class, 'index'])->name('index');
        Route::get('/create', [SessionController::class, 'create'])->name('create');
        Route::post('/', [SessionController::class, 'store'])->name('store');
        Route::get('/{id}', [SessionController::class, 'show'])->name('show');
        Route::get('/{id}/close', [SessionController::class, 'closeForm'])->name('closeForm');
        Route::post('/{id}/close', [SessionController::class, 'close'])->name('close');
        Route::post('/{id}/approve', [SessionController::class, 'approve'])->name('approve')->middleware('role:Admin Toko|Tenant Owner');
        Route::get('/pending-approvals', [SessionController::class, 'pendingApprovals'])->name('pendingApprovals')->middleware('role:Admin Toko|Tenant Owner');
    });

    // POS Transactions Routes (Kasir, Admin Toko, Tenant Owner)
    Route::middleware('role:Kasir|Admin Toko|Tenant Owner')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/transactions', [POSController::class, 'store'])->name('store');
        Route::post('/hold', [POSController::class, 'hold'])->name('hold');
        Route::get('/resume/{id}', [POSController::class, 'resume'])->name('resume');
        Route::delete('/pending/{id}', [POSController::class, 'deletePending'])->name('deletePending');
        Route::post('/void/{id}', [POSController::class, 'void'])->name('void')->middleware('role:Admin Toko|Tenant Owner');
        Route::get('/receipt/{id}', [POSController::class, 'receipt'])->name('receipt');
        Route::get('/search-product', [POSController::class, 'searchProduct'])->name('searchProduct');
    });
});

require __DIR__.'/auth.php';
