<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\UnpackingTransaction;
use App\Repositories\UnpackingRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnpackingService
{
    public function __construct(
        protected UnpackingRepository $repository
    ) {}

    public function createUnpacking(array $data): UnpackingTransaction
    {
        DB::beginTransaction();
        try {
            $tenantId = Auth::user()->tenant_id;
            $unpackingNumber = $this->repository->generateUnpackingNumber($tenantId);

            $unpacking = $this->repository->create([
                'tenant_id' => $tenantId,
                'store_id' => $data['store_id'],
                'unpacking_number' => $unpackingNumber,
                'unpacking_date' => $data['unpacking_date'],
                'source_product_id' => $data['source_product_id'],
                'source_quantity' => $data['source_quantity'],
                'result_product_id' => $data['result_product_id'],
                'result_quantity' => $data['result_quantity'],
                'conversion_ratio' => $data['conversion_ratio'],
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by_user_id' => Auth::id(),
            ]);

            DB::commit();
            return $unpacking;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUnpacking(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'draft') {
                throw new \Exception('Unpacking cannot be updated.');
            }

            $updated = $this->repository->update($id, [
                'unpacking_date' => $data['unpacking_date'],
                'source_product_id' => $data['source_product_id'],
                'source_quantity' => $data['source_quantity'],
                'result_product_id' => $data['result_product_id'],
                'result_quantity' => $data['result_quantity'],
                'conversion_ratio' => $data['conversion_ratio'],
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUnpacking(int $id): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'draft') {
                throw new \Exception('Only draft unpacking transactions can be deleted.');
            }

            $deleted = $this->repository->delete($id);

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function submitUnpacking(int $id): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'draft') {
                throw new \Exception('Only draft unpacking transactions can be submitted.');
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

    public function approveUnpacking(int $id): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'submitted') {
                throw new \Exception('Only submitted unpacking transactions can be approved.');
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

    public function rejectUnpacking(int $id, string $reason): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'submitted') {
                throw new \Exception('Only submitted unpacking transactions can be rejected.');
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

    public function processUnpacking(int $id): bool
    {
        DB::beginTransaction();
        try {
            $unpacking = $this->repository->find($id);

            if (!$unpacking || $unpacking->status !== 'approved') {
                throw new \Exception('Only approved unpacking transactions can be processed.');
            }

            // Check source stock availability
            $sourceStock = Stock::firstOrCreate(
                [
                    'product_id' => $unpacking->source_product_id,
                    'store_id' => $unpacking->store_id,
                ],
                ['quantity' => 0]
            );

            if ($sourceStock->quantity < $unpacking->source_quantity) {
                throw new \Exception('Insufficient source stock to process unpacking.');
            }

            // Reduce source stock
            $sourceOldQuantity = $sourceStock->quantity;
            $sourceStock->quantity -= $unpacking->source_quantity;
            $sourceStock->save();

            // Create stock movement for source (reduction)
            StockMovement::create([
                'product_id' => $unpacking->source_product_id,
                'store_id' => $unpacking->store_id,
                'type' => 'UNPACKING_OUT',
                'quantity' => -$unpacking->source_quantity,
                'reference_type' => UnpackingTransaction::class,
                'reference_id' => $unpacking->id,
                'notes' => "Unpacking: {$unpacking->unpacking_number} - Source (Before: {$sourceOldQuantity}, After: {$sourceStock->quantity})",
                'created_by_user_id' => Auth::id(),
            ]);

            // Add result stock
            $resultStock = Stock::firstOrCreate(
                [
                    'product_id' => $unpacking->result_product_id,
                    'store_id' => $unpacking->store_id,
                ],
                ['quantity' => 0]
            );

            $resultOldQuantity = $resultStock->quantity;
            $resultStock->quantity += $unpacking->result_quantity;
            $resultStock->save();

            // Create stock movement for result (addition)
            StockMovement::create([
                'product_id' => $unpacking->result_product_id,
                'store_id' => $unpacking->store_id,
                'type' => 'UNPACKING_IN',
                'quantity' => $unpacking->result_quantity,
                'reference_type' => UnpackingTransaction::class,
                'reference_id' => $unpacking->id,
                'notes' => "Unpacking: {$unpacking->unpacking_number} - Result (Before: {$resultOldQuantity}, After: {$resultStock->quantity})",
                'created_by_user_id' => Auth::id(),
            ]);

            // Update unpacking status
            $this->repository->update($id, [
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
