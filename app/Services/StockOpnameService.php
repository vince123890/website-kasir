<?php

namespace App\Services;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Repositories\StockOpnameRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockOpnameService
{
    protected StockOpnameRepository $repository;

    public function __construct(StockOpnameRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createOpname(array $data, array $items): StockOpname
    {
        DB::beginTransaction();
        try {
            // Generate opname number
            $opnameNumber = $this->repository->generateOpnameNumber($data['tenant_id']);

            // Create stock opname
            $stockOpname = $this->repository->create([
                'tenant_id' => $data['tenant_id'],
                'store_id' => $data['store_id'],
                'opname_number' => $opnameNumber,
                'opname_date' => $data['opname_date'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by_user_id' => Auth::id(),
            ]);

            // Create items
            foreach ($items as $itemData) {
                StockOpnameItem::create([
                    'stock_opname_id' => $stockOpname->id,
                    'product_id' => $itemData['product_id'],
                    'system_quantity' => $itemData['system_quantity'] ?? 0,
                    'physical_quantity' => $itemData['physical_quantity'] ?? 0,
                    'variance_reason' => $itemData['variance_reason'] ?? null,
                ]);
            }

            // Calculate total variance
            $stockOpname->load('items.product');
            $stockOpname->calculateTotalVariance();

            DB::commit();
            return $stockOpname->fresh('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOpname(int $id, array $data, array $items): bool
    {
        DB::beginTransaction();
        try {
            $stockOpname = $this->repository->find($id);

            if (!$stockOpname) {
                throw new \Exception('Stock opname not found');
            }

            // Only allow update if draft
            if ($stockOpname->status !== 'draft') {
                throw new \Exception('Cannot update stock opname. Status is not draft.');
            }

            // Update stock opname
            $stockOpname->update([
                'opname_date' => $data['opname_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete old items
            $stockOpname->items()->delete();

            // Create new items
            foreach ($items as $itemData) {
                StockOpnameItem::create([
                    'stock_opname_id' => $stockOpname->id,
                    'product_id' => $itemData['product_id'],
                    'system_quantity' => $itemData['system_quantity'] ?? 0,
                    'physical_quantity' => $itemData['physical_quantity'] ?? 0,
                    'variance_reason' => $itemData['variance_reason'] ?? null,
                ]);
            }

            // Recalculate total variance
            $stockOpname->load('items.product');
            $stockOpname->calculateTotalVariance();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteOpname(int $id): bool
    {
        $stockOpname = $this->repository->find($id);

        if (!$stockOpname) {
            throw new \Exception('Stock opname not found');
        }

        // Only allow delete if draft
        if ($stockOpname->status !== 'draft') {
            throw new \Exception('Cannot delete stock opname. Status is not draft.');
        }

        return $this->repository->delete($id);
    }

    public function submitOpname(int $id): bool
    {
        DB::beginTransaction();
        try {
            $stockOpname = $this->repository->find($id);

            if (!$stockOpname) {
                throw new \Exception('Stock opname not found');
            }

            // Validate status
            if ($stockOpname->status !== 'draft') {
                throw new \Exception('Stock opname cannot be submitted. Current status: ' . $stockOpname->status);
            }

            // Validate: all items must have physical quantity
            foreach ($stockOpname->items as $item) {
                if ($item->needsReason && empty($item->variance_reason)) {
                    throw new \Exception('Variance reason is required for product: ' . $item->product->name);
                }
            }

            // Update status
            $stockOpname->update([
                'status' => 'submitted',
                'submitted_by_user_id' => Auth::id(),
                'submitted_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveOpname(int $id): bool
    {
        DB::beginTransaction();
        try {
            $stockOpname = $this->repository->find($id);

            if (!$stockOpname) {
                throw new \Exception('Stock opname not found');
            }

            // Validate status
            if ($stockOpname->status !== 'submitted') {
                throw new \Exception('Stock opname cannot be approved. Current status: ' . $stockOpname->status);
            }

            // Update status
            $stockOpname->update([
                'status' => 'approved',
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectOpname(int $id, string $reason): bool
    {
        DB::beginTransaction();
        try {
            $stockOpname = $this->repository->find($id);

            if (!$stockOpname) {
                throw new \Exception('Stock opname not found');
            }

            // Validate status
            if ($stockOpname->status !== 'submitted') {
                throw new \Exception('Stock opname cannot be rejected. Current status: ' . $stockOpname->status);
            }

            // Update status
            $stockOpname->update([
                'status' => 'rejected',
                'rejected_by_user_id' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function finalizeOpname(int $id): bool
    {
        DB::beginTransaction();
        try {
            $stockOpname = $this->repository->find($id);

            if (!$stockOpname) {
                throw new \Exception('Stock opname not found');
            }

            // Validate status
            if ($stockOpname->status !== 'approved') {
                throw new \Exception('Stock opname cannot be finalized. Current status: ' . $stockOpname->status);
            }

            // Update stocks based on physical quantities
            foreach ($stockOpname->items as $item) {
                $stock = Stock::firstOrCreate([
                    'product_id' => $item->product_id,
                    'store_id' => $stockOpname->store_id,
                ], ['quantity' => 0]);

                // Update stock quantity to physical quantity
                $oldQuantity = $stock->quantity;
                $stock->quantity = $item->physical_quantity;
                $stock->last_stock_opname_date = $stockOpname->opname_date;
                $stock->save();

                // Create stock movement for audit trail
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'store_id' => $stockOpname->store_id,
                    'type' => 'OPNAME',
                    'quantity' => $item->variance, // Can be positive or negative
                    'reference_type' => StockOpname::class,
                    'reference_id' => $stockOpname->id,
                    'notes' => "Stock Opname: {$stockOpname->opname_number}. Old: {$oldQuantity}, New: {$item->physical_quantity}, Variance: {$item->variance}" .
                               ($item->variance_reason ? " - Reason: {$item->variance_reason}" : ''),
                    'created_by_user_id' => Auth::id(),
                ]);
            }

            // Update opname status
            $stockOpname->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
