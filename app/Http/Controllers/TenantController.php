<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenantRequest;
use App\Services\TenantService;
use App\Repositories\TenantRepository;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantController extends Controller
{
    protected TenantService $tenantService;
    protected TenantRepository $tenantRepository;

    public function __construct(TenantService $tenantService, TenantRepository $tenantRepository)
    {
        $this->tenantService = $tenantService;
        $this->tenantRepository = $tenantRepository;

        // Only Super Admin can access
        $this->middleware('role:Administrator SaaS');
    }

    /**
     * Display a listing of tenants
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $filters = [
            'subscription_status' => $request->input('subscription_status'),
            'is_active' => $request->input('is_active'),
        ];

        $tenants = $this->tenantRepository->getAllPaginated(15, $search, $filters);

        return view('tenants.index', compact('tenants', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create(): View
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(TenantRequest $request): RedirectResponse
    {
        try {
            $tenant = $this->tenantService->createTenant($request->validated());

            return redirect()
                ->route('admin.tenants.index')
                ->with('success', 'Tenant berhasil dibuat: ' . $tenant->name);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tenant
     */
    public function show(int $id): View
    {
        $tenant = $this->tenantRepository->getWithStatistics($id);

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        $statistics = $this->tenantService->getTenantStatistics($id);

        // Get users breakdown by role
        $usersBreakdown = $tenant->users()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->selectRaw('roles.name as role_name, COUNT(*) as count')
            ->groupBy('roles.name')
            ->get();

        return view('tenants.show', compact('tenant', 'statistics', 'usersBreakdown'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(int $id): View
    {
        $tenant = $this->tenantRepository->getWithStatistics($id);

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(TenantRequest $request, int $id): RedirectResponse
    {
        try {
            $this->tenantService->updateTenant($id, $request->validated());

            return redirect()
                ->route('admin.tenants.show', $id)
                ->with('success', 'Tenant berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->tenantService->deleteTenant($id);

            return redirect()
                ->route('admin.tenants.index')
                ->with('success', 'Tenant berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus tenant: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft deleted tenant
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $this->tenantService->restoreTenant($id);

            return redirect()
                ->route('admin.tenants.show', $id)
                ->with('success', 'Tenant berhasil dipulihkan.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal memulihkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Activate tenant
     */
    public function activate(Request $request, int $id): RedirectResponse
    {
        try {
            $activateStores = $request->input('activate_stores', false);
            $activateUsers = $request->input('activate_users', false);

            $this->tenantService->activateTenant($id, $activateStores, $activateUsers);

            $message = 'Tenant berhasil diaktifkan.';
            if ($activateStores) {
                $message .= ' Semua store juga diaktifkan.';
            }
            if ($activateUsers) {
                $message .= ' Semua user juga diaktifkan.';
            }

            return redirect()
                ->route('admin.tenants.show', $id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengaktifkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate tenant
     */
    public function deactivate(Request $request, int $id): RedirectResponse
    {
        try {
            $deactivateStores = $request->input('deactivate_stores', false);
            $deactivateUsers = $request->input('deactivate_users', false);

            $this->tenantService->deactivateTenant($id, $deactivateStores, $deactivateUsers);

            $message = 'Tenant berhasil dinonaktifkan.';
            if ($deactivateStores) {
                $message .= ' Semua store juga dinonaktifkan.';
            }
            if ($deactivateUsers) {
                $message .= ' Semua user juga dinonaktifkan.';
            }

            return redirect()
                ->route('admin.tenants.show', $id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menonaktifkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Check if slug is available (AJAX)
     */
    public function checkSlug(Request $request): \Illuminate\Http\JsonResponse
    {
        $slug = $request->input('slug');
        $excludeId = $request->input('exclude_id');

        $available = $this->tenantService->isSlugAvailable($slug, $excludeId);

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Slug tersedia' : 'Slug sudah digunakan'
        ]);
    }
}
