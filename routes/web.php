<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
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
});

require __DIR__.'/auth.php';
