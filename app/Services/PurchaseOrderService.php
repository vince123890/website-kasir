<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Stock;
use App\Repositories\PurchaseOrderRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    protected PurchaseOrderRepository $repository;

    public function __construct(PurchaseOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create new purchase order.
     */
    public function createPO(array $data, array $items): PurchaseOrder
    {
        try {
            DB::beginTransaction();

            // Generate PO number
            $data['po_number'] = $this->repository->generatePONumber($data['tenant_id']);
            $data['created_by_user_id'] = auth()->id();
            $data['status'] = 'draft';

            // Create PO
            $purchaseOrder = $this->repository->create($data);

            // Create items
            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            // Calculate totals
            $purchaseOrder->refresh();
            $purchaseOrder->calculateTotal();

            DB::commit();

            Log::info('Purchase Order created', [
                'po_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'user_id' => auth()->id(),
            ]);

            return $purchaseOrder;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update purchase order (only if draft).
     */
    public function updatePO(int $id, array $data, array $items): bool
    {
        try {
            DB::beginTransaction();

            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'draft') {
                throw new Exception('Cannot edit Purchase Order. Status: ' . $purchaseOrder->status);
            }

            // Update PO
            $purchaseOrder->update($data);

            // Delete old items
            $purchaseOrder->items()->delete();

            // Create new items
            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            // Recalculate totals
            $purchaseOrder->refresh();
            $purchaseOrder->calculateTotal();

            DB::commit();

            Log::info('Purchase Order updated', [
                'po_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete purchase order (only if draft).
     */
    public function deletePO(int $id): bool
    {
        try {
            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'draft') {
                throw new Exception('Cannot delete Purchase Order. Status: ' . $purchaseOrder->status);
            }

            $this->repository->delete($id);

            Log::info('Purchase Order deleted', [
                'po_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Submit purchase order for approval.
     */
    public function submitPO(int $id): bool
    {
        try {
            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'draft') {
                throw new Exception('Cannot submit Purchase Order. Status: ' . $purchaseOrder->status);
            }

            $purchaseOrder->update([
                'status' => 'submitted',
                'submitted_by_user_id' => auth()->id(),
                'submitted_at' => now(),
            ]);

            Log::info('Purchase Order submitted', [
                'po_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
            ]);

            // TODO: Send notification to Tenant Owner

            return true;
        } catch (Exception $e) {
            Log::error('Failed to submit Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Approve purchase order.
     */
    public function approvePO(int $id): bool
    {
        try {
            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'submitted') {
                throw new Exception('Cannot approve Purchase Order. Status: ' . $purchaseOrder->status);
            }

            $purchaseOrder->update([
                'status' => 'approved',
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now(),
            ]);

            Log::info('Purchase Order approved', [
                'po_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
            ]);

            // TODO: Send notification to requester

            return true;
        } catch (Exception $e) {
            Log::error('Failed to approve Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reject purchase order.
     */
    public function rejectPO(int $id, string $reason): bool
    {
        try {
            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'submitted') {
                throw new Exception('Cannot reject Purchase Order. Status: ' . $purchaseOrder->status);
            }

            $purchaseOrder->update([
                'status' => 'draft',
                'rejected_by_user_id' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            Log::info('Purchase Order rejected', [
                'po_id' => $purchaseOrder->id,
                'reason' => $reason,
                'user_id' => auth()->id(),
            ]);

            // TODO: Send notification to requester

            return true;
        } catch (Exception $e) {
            Log::error('Failed to reject Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Receive purchase order and update stock.
     */
    public function receivePO(int $id): bool
    {
        try {
            DB::beginTransaction();

            $purchaseOrder = $this->repository->find($id);

            if (!$purchaseOrder) {
                throw new Exception('Purchase Order not found');
            }

            if ($purchaseOrder->status !== 'approved') {
                throw new Exception('Cannot receive Purchase Order. Status: ' . $purchaseOrder->status);
            }

            // Update each item's stock
            foreach ($purchaseOrder->items as $item) {
                // Get or create stock record
                $stock = Stock::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'store_id' => $purchaseOrder->store_id,
                    ],
                    [
                        'quantity' => 0,
                        'min_stock' => null,
                        'max_stock' => null,
                    ]
                );

                // Increase stock quantity
                $stock->increment('quantity', $item->quantity);

                // Create stock movement record
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'store_id' => $purchaseOrder->store_id,
                    'type' => 'IN',
                    'quantity' => $item->quantity,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'notes' => "Purchase Order: {$purchaseOrder->po_number}",
                    'created_by_user_id' => auth()->id(),
                ]);
            }

            // Update PO status
            $purchaseOrder->update([
                'status' => 'received',
                'received_by_user_id' => auth()->id(),
                'received_at' => now(),
            ]);

            DB::commit();

            Log::info('Purchase Order received and stock updated', [
                'po_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to receive Purchase Order: ' . $e->getMessage());
            throw $e;
        }
    }
}
