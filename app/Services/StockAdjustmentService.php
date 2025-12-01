<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Repositories\StockAdjustmentRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function __construct(
        protected StockAdjustmentRepository $repository
    ) {}

    public function createAdjustment(array $data): StockAdjustment
    {
        DB::beginTransaction();
        try {
            $tenantId = Auth::user()->tenant_id;
            $adjustmentNumber = $this->repository->generateAdjustmentNumber($tenantId);

            $adjustment = $this->repository->create([
                'tenant_id' => $tenantId,
                'store_id' => $data['store_id'],
                'adjustment_number' => $adjustmentNumber,
                'adjustment_date' => $data['adjustment_date'],
                'product_id' => $data['product_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by_user_id' => Auth::id(),
            ]);

            DB::commit();
            return $adjustment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateAdjustment(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'draft') {
                throw new \Exception('Adjustment cannot be updated.');
            }

            $updated = $this->repository->update($id, [
                'adjustment_date' => $data['adjustment_date'],
                'product_id' => $data['product_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAdjustment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'draft') {
                throw new \Exception('Only draft adjustments can be deleted.');
            }

            $deleted = $this->repository->delete($id);

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submitAdjustment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'draft') {
                throw new \Exception('Only draft adjustments can be submitted.');
            }

            $updated = $this->repository->update($id, [
                'status' => 'submitted',
                'submitted_by_user_id' => Auth::id(),
                'submitted_at' => now(),
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveAdjustment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'submitted') {
                throw new \Exception('Only submitted adjustments can be approved.');
            }

            $updated = $this->repository->update($id, [
                'status' => 'approved',
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rejectAdjustment(int $id, string $reason): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'submitted') {
                throw new \Exception('Only submitted adjustments can be rejected.');
            }

            $updated = $this->repository->update($id, [
                'status' => 'rejected',
                'rejected_by_user_id' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function applyAdjustment(int $id): bool
    {
        DB::beginTransaction();
        try {
            $adjustment = $this->repository->find($id);

            if (!$adjustment || $adjustment->status !== 'approved') {
                throw new \Exception('Only approved adjustments can be applied.');
            }

            // Update stock
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $adjustment->product_id,
                    'store_id' => $adjustment->store_id,
                ],
                ['quantity' => 0]
            );

            $oldQuantity = $stock->quantity;

            if ($adjustment->type === 'add') {
                $stock->quantity += $adjustment->quantity;
            } else { // reduce
                if ($stock->quantity < $adjustment->quantity) {
                    throw new \Exception('Insufficient stock to reduce.');
                }
                $stock->quantity -= $adjustment->quantity;
            }

            $stock->save();

            // Create stock movement
            StockMovement::create([
                'product_id' => $adjustment->product_id,
                'store_id' => $adjustment->store_id,
                'type' => 'ADJUSTMENT',
                'quantity' => $adjustment->type === 'add' ? $adjustment->quantity : -$adjustment->quantity,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'notes' => "Stock Adjustment: {$adjustment->adjustment_number} - {$adjustment->reasonLabel} (Before: {$oldQuantity}, After: {$stock->quantity})",
                'created_by_user_id' => Auth::id(),
            ]);

            // Update adjustment status
            $this->repository->update($id, [
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
