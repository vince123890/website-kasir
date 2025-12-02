<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Exception;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display dashboard based on user role
     */
    public function index()
    {
        $user = auth()->user();

        // Route to appropriate dashboard based on role
        if ($user->hasRole('Administrator SaaS')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('Tenant Owner')) {
            return $this->tenantDashboard();
        } elseif ($user->hasRole('Admin Toko')) {
            return $this->storeDashboard();
        } elseif ($user->hasRole('Kasir')) {
            return $this->cashierDashboard();
        }

        abort(403, 'No dashboard available for your role');
    }

    /**
     * Administrator SaaS Dashboard
     */
    protected function adminDashboard()
    {
        try {
            $result = $this->reportService->getAdminDashboardStats();

            return view('dashboards.admin', [
                'stats' => $result['data'],
            ]);
        } catch (Exception $e) {
            return view('dashboards.admin')->with('error', $e->getMessage());
        }
    }

    /**
     * Tenant Owner Dashboard
     */
    protected function tenantDashboard()
    {
        try {
            $tenantId = auth()->user()->tenant_id;

            if (!$tenantId) {
                abort(403, 'Tenant ID not found');
            }

            $result = $this->reportService->getTenantDashboardStats($tenantId);

            return view('dashboards.tenant', [
                'stats' => $result['data'],
            ]);
        } catch (Exception $e) {
            return view('dashboards.tenant')->with('error', $e->getMessage());
        }
    }

    /**
     * Admin Toko Dashboard
     */
    protected function storeDashboard()
    {
        try {
            $storeId = auth()->user()->store_id;

            if (!$storeId) {
                return view('dashboards.store')->with('error', 'You are not assigned to any store');
            }

            $result = $this->reportService->getStoreDashboardStats($storeId);

            return view('dashboards.store', [
                'stats' => $result['data'],
            ]);
        } catch (Exception $e) {
            return view('dashboards.store')->with('error', $e->getMessage());
        }
    }

    /**
     * Kasir Dashboard
     */
    protected function cashierDashboard()
    {
        try {
            $cashierId = auth()->id();

            $result = $this->reportService->getCashierDashboardStats($cashierId);

            return view('dashboards.cashier', [
                'stats' => $result['data'],
            ]);
        } catch (Exception $e) {
            return view('dashboards.cashier')->with('error', $e->getMessage());
        }
    }
}
