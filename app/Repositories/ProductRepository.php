<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PriceHistory;
use App\Models\ProductStorePrice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Get products by tenant with search, filters, and pagination
     */
    public function getByTenant(
        int $tenantId,
        int $perPage = 15,
        ?string $search = null,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Product::where('tenant_id', $tenantId)
            ->with(['category', 'stocks.store'])
            ->withSum('stocks as total_stock', 'quantity');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Filter by stock level
        if (!empty($filters['stock_level'])) {
            $query->whereHas('stocks', function ($q) use ($filters) {
                $stockLevel = $filters['stock_level'];

                if ($stockLevel === 'low') {
                    // Low stock: quantity < min_stock
                    $q->whereRaw('quantity < min_stock');
                } elseif ($stockLevel === 'out') {
                    // Out of stock: quantity = 0
                    $q->where('quantity', 0);
                } elseif ($stockLevel === 'over') {
                    // Overstock: quantity > max_stock
                    $q->whereRaw('quantity > max_stock');
                } elseif ($stockLevel === 'normal') {
                    // Normal stock: min_stock <= quantity <= max_stock
                    $q->whereRaw('quantity >= min_stock')
                      ->whereRaw('quantity <= max_stock');
                }
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get product with stocks and relationships
     */
    public function getWithStocks(int $id): ?Product
    {
        return Product::with([
            'category',
            'stocks.store',
            'storePrices.store',
            'priceHistories' => function ($query) {
                $query->latest()->limit(10);
            }
        ])
        ->withSum('stocks as total_stock', 'quantity')
        ->find($id);
    }

    /**
     * Get all active products for dropdown
     */
    public function getAllActive(int $tenantId): Collection
    {
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'selling_price']);
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->find($id);
        if (!$product) {
            return false;
        }

        return $product->update($data);
    }

    /**
     * Delete a product (soft delete)
     */
    public function delete(int $id): bool
    {
        $product = $this->find($id);
        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    /**
     * Find a product by ID
     */
    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Check if SKU is duplicate
     */
    public function checkDuplicateSKU(string $sku, int $tenantId, ?int $excludeId = null): bool
    {
        $query = Product::where('sku', $sku)
            ->where('tenant_id', $tenantId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Generate unique SKU
     */
    public function generateSKU(int $tenantId, int $categoryId): string
    {
        $category = \App\Models\Category::find($categoryId);

        // Generate category code (first 3 letters uppercase)
        $categoryCode = strtoupper(substr($category->slug ?? 'PRD', 0, 3));

        // Get current date
        $date = date('Ymd');

        // Get sequence number
        $lastProduct = Product::where('tenant_id', $tenantId)
            ->where('sku', 'like', "{$categoryCode}-{$date}-%")
            ->orderBy('sku', 'desc')
            ->first();

        if ($lastProduct) {
            // Extract sequence from last SKU
            $lastSKU = $lastProduct->sku;
            $parts = explode('-', $lastSKU);
            $lastSequence = intval(end($parts));
            $sequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        return "{$categoryCode}-{$date}-{$sequence}";
    }

    /**
     * Get price history for a product
     */
    public function getPriceHistory(int $productId, int $limit = 10): Collection
    {
        return PriceHistory::where('product_id', $productId)
            ->with(['user', 'store'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all price history for export
     */
    public function getAllPriceHistory(int $productId): Collection
    {
        return PriceHistory::where('product_id', $productId)
            ->with(['user', 'store'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Override store-specific price
     */
    public function overrideStorePrice(int $productId, int $storeId, float $price): ProductStorePrice
    {
        return ProductStorePrice::updateOrCreate(
            [
                'product_id' => $productId,
                'store_id' => $storeId,
            ],
            [
                'selling_price' => $price,
            ]
        );
    }

    /**
     * Get store-specific price
     */
    public function getStorePrice(int $productId, int $storeId): ?ProductStorePrice
    {
        return ProductStorePrice::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();
    }

    /**
     * Create initial stocks for all stores
     */
    public function createInitialStocks(int $productId, int $tenantId): void
    {
        $stores = \App\Models\Store::where('tenant_id', $tenantId)->get();

        foreach ($stores as $store) {
            ProductStock::create([
                'product_id' => $productId,
                'store_id' => $store->id,
                'quantity' => 0,
            ]);
        }
    }

    /**
     * Log price change to history
     */
    public function logPriceHistory(
        int $productId,
        float $oldPrice,
        float $newPrice,
        int $userId,
        ?int $storeId = null
    ): PriceHistory {
        return PriceHistory::create([
            'product_id' => $productId,
            'store_id' => $storeId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'changed_by' => $userId,
        ]);
    }

    /**
     * Bulk import products
     */
    public function bulkImport(array $productsData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($productsData as $index => $data) {
            try {
                DB::beginTransaction();

                // Check if SKU exists
                $existing = Product::where('sku', $data['sku'])
                    ->where('tenant_id', $data['tenant_id'])
                    ->first();

                if ($existing) {
                    // Update existing product
                    $existing->update($data);
                    $results['success'][] = [
                        'row' => $index + 2, // +2 because Excel row 1 is header
                        'sku' => $data['sku'],
                        'action' => 'updated',
                    ];
                } else {
                    // Create new product
                    $product = $this->create($data);

                    // Create initial stocks
                    $this->createInitialStocks($product->id, $data['tenant_id']);

                    // Log initial price
                    $this->logPriceHistory(
                        $product->id,
                        0,
                        $data['selling_price'],
                        auth()->id()
                    );

                    $results['success'][] = [
                        'row' => $index + 2,
                        'sku' => $data['sku'],
                        'action' => 'created',
                    ];
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $results['failed'][] = [
                    'row' => $index + 2,
                    'sku' => $data['sku'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Bulk price update
     */
    public function bulkPriceUpdate(
        int $tenantId,
        array $filters,
        string $changeType,
        float $value
    ): array {
        $query = Product::where('tenant_id', $tenantId);

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $products = $query->get();
        $updated = 0;

        foreach ($products as $product) {
            $oldPrice = $product->selling_price;
            $newPrice = $oldPrice;

            // Calculate new price based on change type
            if ($changeType === 'increase_percent') {
                $newPrice = $oldPrice * (1 + ($value / 100));
            } elseif ($changeType === 'decrease_percent') {
                $newPrice = $oldPrice * (1 - ($value / 100));
            } elseif ($changeType === 'increase_fixed') {
                $newPrice = $oldPrice + $value;
            } elseif ($changeType === 'decrease_fixed') {
                $newPrice = $oldPrice - $value;
            }

            // Ensure price doesn't go below 0
            $newPrice = max(0, $newPrice);

            if ($newPrice !== $oldPrice) {
                $product->update(['selling_price' => $newPrice]);

                // Log price history
                $this->logPriceHistory(
                    $product->id,
                    $oldPrice,
                    $newPrice,
                    auth()->id()
                );

                $updated++;
            }
        }

        return [
            'total' => $products->count(),
            'updated' => $updated,
        ];
    }

    /**
     * Get all products for export
     */
    public function getAllForExport(int $tenantId, array $filters = []): Collection
    {
        $query = Product::where('tenant_id', $tenantId)
            ->with(['category', 'stocks.store'])
            ->withSum('stocks as total_stock', 'quantity');

        // Apply search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Check if product has stock movements or transactions
     */
    public function hasStockMovementsOrTransactions(int $productId): bool
    {
        // Check stock movements
        $hasStockMovements = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->exists();

        // Check transaction details
        $hasTransactions = DB::table('transaction_details')
            ->where('product_id', $productId)
            ->exists();

        return $hasStockMovements || $hasTransactions;
    }

    /**
     * Get product stock by store
     */
    public function getStockByStore(int $productId, int $storeId): ?ProductStock
    {
        return ProductStock::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();
    }

    /**
     * Get available products for store with stock information
     */
    public function getAvailableForStore(int $storeId): Collection
    {
        return Product::whereHas('stocks', function ($query) use ($storeId) {
            $query->where('store_id', $storeId)
                ->where('quantity', '>', 0);
        })
        ->where('is_active', true)
        ->with(['category', 'stocks' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->orderBy('name')
        ->get();
    }

    /**
     * Search products for store
     */
    public function searchForStore(string $search, int $storeId, int $limit = 10): Collection
    {
        return Product::where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%");
        })
        ->whereHas('stocks', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })
        ->where('is_active', true)
        ->with(['category', 'stocks' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->limit($limit)
        ->get();
    }

    /**
     * Get available stock for product in store
     */
    public function getAvailableStock(int $productId, int $storeId): float
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    /**
     * Reduce stock for a product in a store
     */
    public function reduceStock(int $productId, int $storeId, float $quantity): void
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if ($stock) {
            $stock->decrement('quantity', $quantity);
        }
    }

    /**
     * Increase stock for a product in a store
     */
    public function increaseStock(int $productId, int $storeId, float $quantity): void
    {
        $stock = ProductStock::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if ($stock) {
            $stock->increment('quantity', $quantity);
        }
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }
}
