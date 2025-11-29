<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensure the authenticated user has a tenant_id and prevent access to other tenants' data.
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

        // Administrator SaaS can access all tenants
        if ($user->hasRole('Administrator SaaS')) {
            return $next($request);
        }

        // Check if user has tenant_id
        if (!$user->tenant_id) {
            abort(403, 'User tidak memiliki tenant yang terdaftar');
        }

        // If accessing a specific tenant resource, verify ownership
        $tenantId = $request->route('tenant_id') ?? $request->input('tenant_id');

        if ($tenantId && (int) $tenantId !== (int) $user->tenant_id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses tenant ini');
        }

        return $next($request);
    }
}
