<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnpackingRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Repositories\UnpackingRepository;
use App\Services\UnpackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnpackingController extends Controller
{
    public function __construct(
        protected UnpackingRepository $repository,
        protected UnpackingService $service
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
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

        if ($filters['date_from'] || $filters['date_to']) {
            $query = $this->repository->filterByDateRange($tenantId, $filters['date_from'], $filters['date_to'], 15);
        }

        // If user is Admin Toko, only show their store's unpacking
        if ($user->hasRole('Admin Toko')) {
            $storeId = $user->stores()->first()?->id;
            if ($storeId) {
                $query = $this->repository->getByStore($storeId, 15);
            }
        }

        $unpackings = $query;

        return view('inventory.unpacking.index', compact('unpackings', 'filters'));
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

        return view('inventory.unpacking.create', compact('stores', 'products'));
    }

    public function store(UnpackingRequest $request)
    {
        try {
            $unpacking = $this->service->createUnpacking($request->validated());

            return redirect()
                ->route('inventory.unpacking.show', $unpacking->id)
                ->with('success', 'Unpacking transaction created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create unpacking transaction: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        $unpacking = $this->repository->find($id);

        if (!$unpacking) {
            return redirect()
                ->route('inventory.unpacking.index')
                ->with('error', 'Unpacking transaction not found.');
        }

        // Check current stocks
        $sourceStock = Stock::where('product_id', $unpacking->source_product_id)
            ->where('store_id', $unpacking->store_id)
            ->first();

        $resultStock = Stock::where('product_id', $unpacking->result_product_id)
            ->where('store_id', $unpacking->store_id)
            ->first();

        return view('inventory.unpacking.show', compact('unpacking', 'sourceStock', 'resultStock'));
    }

    public function edit(int $id)
    {
        $unpacking = $this->repository->find($id);

        if (!$unpacking || !$unpacking->canEdit) {
            return redirect()
                ->route('inventory.unpacking.index')
                ->with('error', 'Unpacking transaction cannot be edited.');
        }

        $user = Auth::user();
        $tenantId = $user->tenant_id;

        if ($user->hasRole('Tenant Owner')) {
            $stores = Store::where('tenant_id', $tenantId)->get();
        } else {
            $stores = $user->stores;
        }

        $products = Product::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('inventory.unpacking.edit', compact('unpacking', 'stores', 'products'));
    }

    public function update(UnpackingRequest $request, int $id)
    {
        try {
            $this->service->updateUnpacking($id, $request->validated());

            return redirect()
                ->route('inventory.unpacking.show', $id)
                ->with('success', 'Unpacking transaction updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update unpacking transaction: ' . $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->deleteUnpacking($id);

            return redirect()
                ->route('inventory.unpacking.index')
                ->with('success', 'Unpacking transaction deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete unpacking transaction: ' . $e->getMessage());
        }
    }

    public function submit(int $id)
    {
        try {
            $this->service->submitUnpacking($id);

            return redirect()
                ->route('inventory.unpacking.show', $id)
                ->with('success', 'Unpacking transaction submitted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to submit unpacking transaction: ' . $e->getMessage());
        }
    }

    public function approve(int $id)
    {
        try {
            $this->service->approveUnpacking($id);

            return redirect()
                ->route('inventory.unpacking.show', $id)
                ->with('success', 'Unpacking transaction approved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve unpacking transaction: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->service->rejectUnpacking($id, $request->rejection_reason);

            return redirect()
                ->route('inventory.unpacking.show', $id)
                ->with('success', 'Unpacking transaction rejected successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reject unpacking transaction: ' . $e->getMessage());
        }
    }

    public function process(int $id)
    {
        try {
            $this->service->processUnpacking($id);

            return redirect()
                ->route('inventory.unpacking.show', $id)
                ->with('success', 'Unpacking transaction processed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to process unpacking transaction: ' . $e->getMessage());
        }
    }
}
