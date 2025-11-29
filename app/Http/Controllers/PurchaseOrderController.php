<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Product;
use App\Models\Supplier;
use App\Repositories\PurchaseOrderRepository;
use App\Services\PurchaseOrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    protected PurchaseOrderRepository $repository;
    protected PurchaseOrderService $service;

    public function __construct(
        PurchaseOrderRepository $repository,
        PurchaseOrderService $service
    ) {
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        // Check role
        if ($user->hasRole('Tenant Owner')) {
            $filters['store_id'] = $request->get('store_id');
            $purchaseOrders = $this->repository->getByTenant($user->tenant_id, 15, $filters);
        } else {
            $purchaseOrders = $this->repository->getByStore($user->store_id, 15, $filters);
        }

        return view('purchases.index', compact('purchaseOrders', 'filters'));
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created purchase order.
     */
    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = auth()->user()->tenant_id;
            $data['store_id'] = auth()->user()->store_id;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;

            $items = $data['items'];
            unset($data['items']);

            $purchaseOrder = $this->service->createPO($data, $items);

            // Check if submit for approval
            if ($request->has('submit_for_approval')) {
                $this->service->submitPO($purchaseOrder->id);
                return redirect()
                    ->route('purchases.index')
                    ->with('success', 'Purchase Order berhasil dibuat dan diajukan untuk approval.');
            }

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Order berhasil dibuat sebagai draft.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(int $id): View
    {
        $purchaseOrder = $this->repository->find($id);

        if (!$purchaseOrder) {
            abort(404);
        }

        // Check permission
        $user = auth()->user();
        if ($purchaseOrder->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        return view('purchases.show', compact('purchaseOrder'));
    }

    /**
     * Show the form for editing the purchase order.
     */
    public function edit(int $id): View
    {
        $purchaseOrder = $this->repository->find($id);

        if (!$purchaseOrder || $purchaseOrder->status !== 'draft') {
            abort(403, 'Cannot edit this Purchase Order');
        }

        $tenantId = auth()->user()->tenant_id;
        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('purchases.edit', compact('purchaseOrder', 'suppliers', 'products'));
    }

    /**
     * Update the purchase order.
     */
    public function update(PurchaseOrderRequest $request, int $id): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tax_amount'] = $data['tax_amount'] ?? 0;

            $items = $data['items'];
            unset($data['items']);

            $this->service->updatePO($id, $data, $items);

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Order berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the purchase order.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->service->deletePO($id);

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Order berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Submit purchase order for approval.
     */
    public function submit(int $id): RedirectResponse
    {
        try {
            $this->service->submitPO($id);

            return redirect()
                ->route('purchases.show', $id)
                ->with('success', 'Purchase Order berhasil diajukan untuk approval.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengajukan Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Approve purchase order.
     */
    public function approve(int $id): RedirectResponse
    {
        try {
            $this->service->approvePO($id);

            return redirect()
                ->route('purchases.show', $id)
                ->with('success', 'Purchase Order berhasil di-approve.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal approve Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Reject purchase order.
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->service->rejectPO($id, $request->rejection_reason);

            return redirect()
                ->route('purchases.show', $id)
                ->with('success', 'Purchase Order berhasil di-reject.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal reject Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Receive purchase order and update stock.
     */
    public function receive(int $id): RedirectResponse
    {
        try {
            $this->service->receivePO($id);

            return redirect()
                ->route('purchases.show', $id)
                ->with('success', 'Purchase Order berhasil diterima. Stok telah diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menerima Purchase Order: ' . $e->getMessage());
        }
    }
}
