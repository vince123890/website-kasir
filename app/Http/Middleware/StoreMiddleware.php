<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user has a store_id for store-level routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(401, 'Unauthenticated');
        }

        $user = auth()->user();

        // Administrator SaaS and Tenant Owner can access all stores in their scope
        if ($user->hasRole(['Administrator SaaS', 'Tenant Owner'])) {
            return $next($request);
        }

        // Check if user has store_id (required for Admin Toko and Kasir)
        if (!$user->store_id) {
            abort(403, 'User tidak memiliki toko yang terdaftar');
        }

        // If accessing a specific store resource, verify ownership
        $storeId = $request->route('store_id') ?? $request->input('store_id');

        if ($storeId && (int) $storeId !== (int) $user->store_id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses toko ini');
        }

        return $next($request);
    }
}
