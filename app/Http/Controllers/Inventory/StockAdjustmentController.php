<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Repositories\StockAdjustmentRepository;
use App\Services\StockAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockAdjustmentRepository $repository,
        protected StockAdjustmentService $service
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = $this->repository->getByTenant($tenantId, 15);

        // Apply filters
        if ($filters['search']) {
            $query = $this->repository->search($tenantId, $filters['search'], 15);
        }

        if ($filters['status']) {
            $query = $this->repository->filterByStatus($tenantId, $filters['status'], 15);
        }

        if ($filters['type']) {
            $query = $this->repository->filterByType($tenantId, $filters['type'], 15);
        }

        if ($filters['date_from'] || $filters['date_to']) {
            $query = $this->repository->filterByDateRange($tenantId, $filters['date_from'], $filters['date_to'], 15);
        }

        // If user is Admin Toko, only show their store's adjustments
        if ($user->hasRole('Admin Toko')) {
            $storeId = $user->stores()->first()?->id;
            if ($storeId) {
                $query = $this->repository->getByStore($storeId, 15);
            }
        }

        $adjustments = $query;

        return view('inventory.adjustments.index', compact('adjustments', 'filters'));
    }

    public function create()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get stores based on user role
        if ($user->hasRole('Tenant Owner')) {
            $stores = Store::where('tenant_id', $tenantId)->get();
        } else {
            $stores = $user->stores;
        }

        // Get all products for the tenant
        $products = Product::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('inventory.adjustments.create', compact('stores', 'products'));
    }

    public function store(StockAdjustmentRequest $request)
    {
        try {
            $adjustment = $this->service->createAdjustment($request->validated());

            return redirect()
                ->route('inventory.adjustments.show', $adjustment->id)
                ->with('success', 'Stock adjustment created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create stock adjustment: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $adjustment = $this->repository->find($id);

        if (!$adjustment) {
            return redirect()
                ->route('inventory.adjustments.index')
                ->with('error', 'Stock adjustment not found.');
        }

        // Check if current stock exists
        $currentStock = Stock::where('product_id', $adjustment->product_id)
            ->where('store_id', $adjustment->store_id)
            ->first();

        return view('inventory.adjustments.show', compact('adjustment', 'currentStock'));
    }

    public function edit(int $id)
    {
        $adjustment = $this->repository->find($id);

        if (!$adjustment || !$adjustment->canEdit) {
            return redirect()
                ->route('inventory.adjustments.index')
                ->with('error', 'Adjustment cannot be edited.');
        }

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if ($user->hasRole('Tenant Owner')) {
            $stores = Store::where('tenant_id', $tenantId)->get();
        } else {
            $stores = $user->stores;
        }

        $products = Product::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('inventory.adjustments.edit', compact('adjustment', 'stores', 'products'));
    }

    public function update(StockAdjustmentRequest $request, int $id)
    {
        try {
            $this->service->updateAdjustment($id, $request->validated());

            return redirect()
                ->route('inventory.adjustments.show', $id)
                ->with('success', 'Stock adjustment updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update stock adjustment: ' . $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteAdjustment($id);

            return redirect()
                ->route('inventory.adjustments.index')
                ->with('success', 'Stock adjustment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete stock adjustment: ' . $e->getMessage());
        }
    }

    public function submit(int $id)
    {
        try {
            $this->service->submitAdjustment($id);

            return redirect()
                ->route('inventory.adjustments.show', $id)
                ->with('success', 'Stock adjustment submitted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to submit stock adjustment: ' . $e->getMessage());
        }
    }

    public function approve(int $id)
    {
        try {
            $this->service->approveAdjustment($id);

            return redirect()
                ->route('inventory.adjustments.show', $id)
                ->with('success', 'Stock adjustment approved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve stock adjustment: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->service->rejectAdjustment($id, $request->rejection_reason);

            return redirect()
                ->route('inventory.adjustments.show', $id)
                ->with('success', 'Stock adjustment rejected successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reject stock adjustment: ' . $e->getMessage());
        }
    }

    public function apply(int $id)
    {
        try {
            $this->service->applyAdjustment($id);

            return redirect()
                ->route('inventory.adjustments.show', $id)
                ->with('success', 'Stock adjustment applied successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to apply stock adjustment: ' . $e->getMessage());
        }
    }
}
