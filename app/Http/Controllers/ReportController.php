<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Exception;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected StoreRepository $storeRepository,
        protected UserRepository $userRepository,
        protected CategoryRepository $categoryRepository
    ) {
        $this->middleware('auth');
    }

    /**
     * Display reports index page
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display sales report
     */
    public function salesReport(Request $request)
    {
        try {
            $filters = [
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'store_id' => $request->get('store_id'),
                'cashier_id' => $request->get('cashier_id'),
                'payment_method' => $request->get('payment_method'),
            ];

            $result = $this->reportService->generateSalesReport($filters);

            // Get data for filters
            $tenantId = auth()->user()->tenant_id;
            $stores = $this->storeRepository->getByTenant($tenantId, 1000);
            $cashiers = $this->userRepository->getByTenant($tenantId, 1000)
                ->filter(function ($user) {
                    return $user->hasRole('Kasir');
                });

            return view('reports.sales', [
                'report' => $result['data'],
                'filters' => $filters,
                'stores' => $stores,
                'cashiers' => $cashiers,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display inventory report
     */
    public function inventoryReport(Request $request)
    {
        try {
            $filters = [
                'store_id' => $request->get('store_id'),
                'category_id' => $request->get('category_id'),
                'stock_level' => $request->get('stock_level'),
            ];

            $result = $this->reportService->generateInventoryReport($filters);

            // Get data for filters
            $tenantId = auth()->user()->tenant_id;
            $stores = $this->storeRepository->getByTenant($tenantId, 1000);
            $categories = $this->categoryRepository->getByTenant($tenantId, 1000);

            return view('reports.inventory', [
                'report' => $result['data'],
                'filters' => $filters,
                'stores' => $stores,
                'categories' => $categories,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display financial report
     */
    public function financialReport(Request $request)
    {
        try {
            // Check permission - only Tenant Owner
            if (!auth()->user()->hasRole('Tenant Owner')) {
                abort(403, 'Unauthorized access to financial reports');
            }

            $filters = [
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ];

            $result = $this->reportService->generateFinancialReport($filters);

            return view('reports.financial', [
                'report' => $result['data'],
                'filters' => $filters,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display cashier performance report
     */
    public function cashierReport(Request $request)
    {
        try {
            $filters = [
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'store_id' => $request->get('store_id'),
                'cashier_id' => $request->get('cashier_id'),
            ];

            $result = $this->reportService->generateCashierReport($filters);

            // Get data for filters
            $tenantId = auth()->user()->tenant_id;
            $stores = $this->storeRepository->getByTenant($tenantId, 1000);
            $cashiers = $this->userRepository->getByTenant($tenantId, 1000)
                ->filter(function ($user) {
                    return $user->hasRole('Kasir');
                });

            return view('reports.cashier', [
                'report' => $result['data'],
                'filters' => $filters,
                'stores' => $stores,
                'cashiers' => $cashiers,
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'report_type' => 'required|in:sales,inventory,financial,cashier',
                'format' => 'required|in:excel,pdf,csv',
            ]);

            $reportType = $request->get('report_type');
            $format = $request->get('format');
            $filters = $request->except(['report_type', 'format', '_token']);

            $filename = $this->reportService->exportReport($reportType, $format, $filters);

            $filepath = storage_path('app/exports/' . $filename);

            if (file_exists($filepath)) {
                return response()->download($filepath)->deleteFileAfterSend(true);
            }

            return back()->with('error', 'Export file not found');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
