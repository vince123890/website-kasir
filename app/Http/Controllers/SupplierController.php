<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Repositories\SupplierRepository;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Exception;

class SupplierController extends Controller
{
    protected SupplierRepository $supplierRepository;
    protected SupplierService $supplierService;

    public function __construct(
        SupplierRepository $supplierRepository,
        SupplierService $supplierService
    ) {
        $this->supplierRepository = $supplierRepository;
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of suppliers
     */
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $search = $request->get('search');
        $filters = [
            'is_active' => $request->get('is_active'),
            'city' => $request->get('city'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];

        $suppliers = $this->supplierRepository->getByTenant($tenantId, 15, $search, $filters);

        return view('suppliers.index', compact('suppliers', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage
     */
    public function store(SupplierRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = auth()->user()->tenant_id;

            $supplier = $this->supplierService->createSupplier($data);

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified supplier
     */
    public function show(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $supplier = $this->supplierRepository->getWithStatistics($id);

        if (!$supplier || $supplier->tenant_id !== $tenantId) {
            abort(404);
        }

        // Get statistics
        $statistics = $this->supplierService->getSupplierStatistics($id);

        // Get purchase history
        $purchaseOrders = $supplier->purchaseOrders ?? collect([]);

        return view('suppliers.show', compact('supplier', 'statistics', 'purchaseOrders'));
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $supplier = $this->supplierRepository->find($id);

        if (!$supplier || $supplier->tenant_id !== $tenantId) {
            abort(404);
        }

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage
     */
    public function update(SupplierRequest $request, int $id): RedirectResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier || $supplier->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $data = $request->validated();

            $this->supplierService->updateSupplier($id, $data);

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier from storage
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $supplier = $this->supplierRepository->find($id);

            if (!$supplier || $supplier->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $this->supplierService->deleteSupplier($id);

            return redirect()
                ->route('suppliers.index')
                ->with('success', 'Supplier berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    /**
     * Export suppliers to Excel/CSV
     */
    public function export(Request $request): Response
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $filters = [
                'search' => $request->get('search'),
                'is_active' => $request->get('is_active'),
                'city' => $request->get('city'),
            ];

            $data = $this->supplierService->exportSuppliers($tenantId, $filters);

            $filename = 'suppliers-' . date('Y-m-d-His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                // Add BOM for UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Add header row
                if (count($data) > 0) {
                    fputcsv($file, array_keys($data[0]));
                }

                // Add data rows
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengekspor supplier: ' . $e->getMessage());
        }
    }

    /**
     * Get purchase history for a supplier
     */
    public function purchaseHistory(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $supplier = $this->supplierRepository->find($id);

        if (!$supplier || $supplier->tenant_id !== $tenantId) {
            abort(404);
        }

        $purchaseOrders = $this->supplierRepository->getPurchaseHistory($id);

        return view('suppliers.purchase-history', compact('supplier', 'purchaseOrders'));
    }
}
