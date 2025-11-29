<?php

namespace App\Services;

use App\Repositories\SupplierRepository;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierService
{
    protected SupplierRepository $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Create a new supplier
     */
    public function createSupplier(array $data): Supplier
    {
        DB::beginTransaction();
        try {
            // Generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = $this->generateCode($data['tenant_id']);
            }

            // Validate NPWP format if provided
            if (!empty($data['tax_id'])) {
                $data['tax_id'] = $this->formatNPWP($data['tax_id']);
            }

            // Create supplier
            $supplier = $this->supplierRepository->create($data);

            Log::info('Supplier created', [
                'supplier_id' => $supplier->id,
                'code' => $supplier->code,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $supplier;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create supplier', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update a supplier
     */
    public function updateSupplier(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $supplier = $this->supplierRepository->find($id);
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            // Validate NPWP format if provided
            if (!empty($data['tax_id'])) {
                $data['tax_id'] = $this->formatNPWP($data['tax_id']);
            }

            // Update supplier
            $updated = $this->supplierRepository->update($id, $data);

            Log::info('Supplier updated', [
                'supplier_id' => $id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update supplier', [
                'supplier_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a supplier
     */
    public function deleteSupplier(int $id): bool
    {
        DB::beginTransaction();
        try {
            $supplier = $this->supplierRepository->find($id);
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            // Check if has active or pending purchase orders
            if ($this->supplierRepository->hasActivePurchaseOrders($id)) {
                throw new \Exception('Cannot delete supplier with active or pending purchase orders');
            }

            // Soft delete
            $deleted = $this->supplierRepository->delete($id);

            Log::info('Supplier deleted', [
                'supplier_id' => $id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete supplier', [
                'supplier_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique supplier code
     */
    public function generateCode(int $tenantId): string
    {
        return $this->supplierRepository->generateCode($tenantId);
    }

    /**
     * Format NPWP (Indonesian Tax ID)
     * Format: XX.XXX.XXX.X-XXX.XXX
     */
    protected function formatNPWP(string $npwp): string
    {
        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $npwp);

        // Check if valid length (15 digits)
        if (strlen($clean) !== 15) {
            return $npwp; // Return as is if invalid
        }

        // Format: XX.XXX.XXX.X-XXX.XXX
        return substr($clean, 0, 2) . '.' .
               substr($clean, 2, 3) . '.' .
               substr($clean, 5, 3) . '.' .
               substr($clean, 8, 1) . '-' .
               substr($clean, 9, 3) . '.' .
               substr($clean, 12, 3);
    }

    /**
     * Validate NPWP format
     */
    public function validateNPWP(string $npwp): bool
    {
        // Remove all non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $npwp);

        // NPWP must be exactly 15 digits
        return strlen($clean) === 15;
    }

    /**
     * Export suppliers to array for CSV/Excel
     */
    public function exportSuppliers(int $tenantId, array $filters = []): array
    {
        $suppliers = $this->supplierRepository->getAllForExport($tenantId, $filters);

        $data = [];
        foreach ($suppliers as $supplier) {
            $data[] = [
                'Code' => $supplier->code,
                'Name' => $supplier->name,
                'Contact Person' => $supplier->contact_person,
                'Phone' => $supplier->phone,
                'Email' => $supplier->email ?? '',
                'Address' => $supplier->address,
                'City' => $supplier->city ?? '',
                'Province' => $supplier->province ?? '',
                'Postal Code' => $supplier->postal_code ?? '',
                'Payment Terms' => $supplier->payment_terms ?? '',
                'Tax ID (NPWP)' => $supplier->tax_id ?? '',
                'Total POs' => $supplier->purchase_orders_count ?? 0,
                'Total Purchases' => $supplier->total_purchases ?? 0,
                'Status' => $supplier->is_active ? 'Active' : 'Inactive',
                'Created At' => $supplier->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    /**
     * Get supplier statistics
     */
    public function getSupplierStatistics(int $id): array
    {
        return $this->supplierRepository->getStatistics($id);
    }
}
