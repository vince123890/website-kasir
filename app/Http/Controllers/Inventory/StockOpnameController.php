<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockOpnameRequest;
use App\Services\StockOpnameService;
use App\Repositories\StockOpnameRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StockOpnameController extends Controller
{
    protected StockOpnameService $service;
    protected StockOpnameRepository $repository;

    public function __construct(
        StockOpnameService $service,
        StockOpnameRepository $repository
    ) {
        $this->service = $service;
        $this->repository = $repository;
    }

    /**
     * Display a listing of stock opnames.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filters = $request->only(['search', 'status', 'date_from', 'date_to', 'store_id']);

        if ($user->hasRole('Tenant Owner')) {
            $stockOpnames = $this->repository->getByTenant($user->tenant_id, 15, $filters);
        } else {
            $stockOpnames = $this->repository->getByStore($user->store_id, 15, $filters);
        }

        return view('inventory.opname.index', compact('stockOpnames', 'filters'));
    }

    /**
     * Show the form for creating a new stock opname.
     */
    public function create(): View
    {
        $user = auth()->user();

        // Get current stock for store
        $stockItems = $this->repository->getCurrentStockForStore($user->store_id);

        return view('inventory.opname.create', compact('stockItems'));
    }

    /**
     * Store a newly created stock opname.
     */
    public function store(StockOpnameRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();

            $data = [
                'tenant_id' => $user->tenant_id,
                'store_id' => $user->store_id,
                'opname_date' => $request->opname_date,
                'notes' => $request->notes,
            ];

            $items = $request->items;

            $stockOpname = $this->service->createOpname($data, $items);

            return redirect()
                ->route('inventory.opname.show', $stockOpname->id)
                ->with('success', 'Stock opname berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock opname.
     */
    public function show(int $id): View
    {
        $stockOpname = $this->repository->find($id);

        if (!$stockOpname) {
            abort(404, 'Stock opname not found');
        }

        return view('inventory.opname.show', compact('stockOpname'));
    }

    /**
     * Show the form for editing the specified stock opname.
     */
    public function edit(int $id): View
    {
        $stockOpname = $this->repository->find($id);

        if (!$stockOpname) {
            abort(404, 'Stock opname not found');
        }

        // Only allow edit if draft
        if ($stockOpname->status !== 'draft') {
            abort(403, 'Cannot edit stock opname. Status is ' . $stockOpname->status);
        }

        return view('inventory.opname.edit', compact('stockOpname'));
    }

    /**
     * Update the specified stock opname.
     */
    public function update(int $id, StockOpnameRequest $request): RedirectResponse
    {
        try {
            $data = [
                'opname_date' => $request->opname_date,
                'notes' => $request->notes,
            ];

            $items = $request->items;

            $this->service->updateOpname($id, $data, $items);

            return redirect()
                ->route('inventory.opname.show', $id)
                ->with('success', 'Stock opname berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified stock opname.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->service->deleteOpname($id);

            return redirect()
                ->route('inventory.opname.index')
                ->with('success', 'Stock opname berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Submit stock opname for approval.
     */
    public function submit(int $id): RedirectResponse
    {
        try {
            $this->service->submitOpname($id);

            return redirect()
                ->route('inventory.opname.show', $id)
                ->with('success', 'Stock opname berhasil disubmit untuk approval.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal submit stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Approve stock opname.
     */
    public function approve(int $id): RedirectResponse
    {
        try {
            $this->service->approveOpname($id);

            return redirect()
                ->route('inventory.opname.show', $id)
                ->with('success', 'Stock opname berhasil diapprove.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal approve stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Reject stock opname.
     */
    public function reject(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $this->service->rejectOpname($id, $request->rejection_reason);

            return redirect()
                ->route('inventory.opname.show', $id)
                ->with('success', 'Stock opname berhasil direject.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal reject stock opname: ' . $e->getMessage());
        }
    }

    /**
     * Finalize stock opname and update stocks.
     */
    public function finalize(int $id): RedirectResponse
    {
        try {
            $this->service->finalizeOpname($id);

            return redirect()
                ->route('inventory.opname.show', $id)
                ->with('success', 'Stock opname berhasil difinalisasi. Stock telah diupdate.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal finalisasi stock opname: ' . $e->getMessage());
        }
    }
}
