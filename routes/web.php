<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
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
});

require __DIR__.'/auth.php';
