<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product
    {
        DB::beginTransaction();
        try {
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU($data['tenant_id'], $data['category_id']);
            }

            // Upload image if provided
            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image_path'] = $this->uploadProductImage($data['image']);
                unset($data['image']);
            }

            // Create product
            $product = $this->productRepository->create($data);

            // Create initial stocks for all stores (qty = 0)
            $this->productRepository->createInitialStocks($product->id, $data['tenant_id']);

            // Log initial price history
            $this->productRepository->logPriceHistory(
                $product->id,
                0,
                $data['selling_price'],
                auth()->id()
            );

            Log::info('Product created', [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create product', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update a product
     */
    public function updateProduct(int $id, array $data): bool
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->find($id);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            $oldPrice = $product->selling_price;

            // Upload new image if provided
            if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
                // Delete old image
                if ($product->image_path) {
                    $this->deleteProductImage($product->image_path);
                }

                $data['image_path'] = $this->uploadProductImage($data['image']);
                unset($data['image']);
            }

            // Update product
            $updated = $this->productRepository->update($id, $data);

            // Log price change if selling price changed
            if (isset($data['selling_price']) && $data['selling_price'] != $oldPrice) {
                $this->productRepository->logPriceHistory(
                    $id,
                    $oldPrice,
                    $data['selling_price'],
                    auth()->id()
                );
            }

            Log::info('Product updated', [
                'product_id' => $id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a product
     */
    public function deleteProduct(int $id): bool
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->find($id);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            // Check if has stock movements or transactions
            if ($this->productRepository->hasStockMovementsOrTransactions($id)) {
                throw new \Exception('Cannot delete product with stock movements or transactions');
            }

            // Soft delete
            $deleted = $this->productRepository->delete($id);

            Log::info('Product deleted', [
                'product_id' => $id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete product', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique SKU
     */
    public function generateSKU(int $tenantId, int $categoryId): string
    {
        return $this->productRepository->generateSKU($tenantId, $categoryId);
    }

    /**
     * Bulk import products from Excel
     */
    public function bulkImportFromExcel(UploadedFile $file, int $tenantId): array
    {
        try {
            // Read Excel file (using PhpSpreadsheet or similar)
            // For now, we'll assume CSV format
            $productsData = [];

            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle); // Skip header row

                while (($row = fgetcsv($handle)) !== false) {
                    // Parse row data
                    $productsData[] = [
                        'tenant_id' => $tenantId,
                        'name' => $row[0] ?? '',
                        'sku' => $row[1] ?? '',
                        'barcode' => $row[2] ?? null,
                        'category_id' => $row[3] ?? null,
                        'unit' => $row[4] ?? 'pcs',
                        'purchase_price' => floatval($row[5] ?? 0),
                        'selling_price' => floatval($row[6] ?? 0),
                        'min_stock' => intval($row[7] ?? 0),
                        'max_stock' => intval($row[8] ?? 0),
                        'is_active' => true,
                    ];
                }
                fclose($handle);
            }

            // Process bulk import
            return $this->productRepository->bulkImport($productsData);
        } catch (\Exception $e) {
            Log::error('Bulk import failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
        DB::beginTransaction();
        try {
            $result = $this->productRepository->bulkPriceUpdate(
                $tenantId,
                $filters,
                $changeType,
                $value
            );

            Log::info('Bulk price update completed', [
                'tenant_id' => $tenantId,
                'change_type' => $changeType,
                'value' => $value,
                'updated' => $result['updated'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk price update failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Upload product image
     */
    public function uploadProductImage(UploadedFile $file): string
    {
        try {
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Create directory if not exists
            $directory = storage_path('app/public/products');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $thumbnailDirectory = storage_path('app/public/products/thumbnails');
            if (!file_exists($thumbnailDirectory)) {
                mkdir($thumbnailDirectory, 0755, true);
            }

            // Resize main image to 800x800
            $mainImage = Image::make($file)
                ->resize(800, 800, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 85);

            $mainImage->save($directory . '/' . $filename);

            // Create thumbnail 200x200
            $thumbnail = Image::make($file)
                ->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 85);

            $thumbnail->save($thumbnailDirectory . '/' . $filename);

            return 'products/' . $filename;
        } catch (\Exception $e) {
            Log::error('Failed to upload product image', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete product image
     */
    protected function deleteProductImage(string $path): void
    {
        try {
            // Delete main image
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Delete thumbnail
            $thumbnailPath = str_replace('products/', 'products/thumbnails/', $path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete product image', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Override store-specific price
     */
    public function overrideStorePrice(int $productId, int $storeId, float $price): bool
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            // Get old store price if exists
            $oldStorePrice = $this->productRepository->getStorePrice($productId, $storeId);
            $oldPrice = $oldStorePrice ? $oldStorePrice->selling_price : $product->selling_price;

            // Create/Update store-specific price
            $this->productRepository->overrideStorePrice($productId, $storeId, $price);

            // Log price history with store ID
            $this->productRepository->logPriceHistory(
                $productId,
                $oldPrice,
                $price,
                auth()->id(),
                $storeId
            );

            Log::info('Store price overridden', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'price' => $price,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to override store price', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Export products to array for CSV/Excel
     */
    public function exportProducts(int $tenantId, array $filters = []): array
    {
        $products = $this->productRepository->getAllForExport($tenantId, $filters);

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'SKU' => $product->sku,
                'Barcode' => $product->barcode ?? '',
                'Name' => $product->name,
                'Category' => $product->category->name ?? '',
                'Unit' => ucfirst($product->unit),
                'Purchase Price' => $product->purchase_price,
                'Selling Price' => $product->selling_price,
                'Profit Margin (%)' => $product->purchase_price > 0
                    ? round((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 2)
                    : 0,
                'Min Stock' => $product->min_stock ?? 0,
                'Max Stock' => $product->max_stock ?? 0,
                'Total Stock' => $product->total_stock ?? 0,
                'Status' => $product->is_active ? 'Active' : 'Inactive',
                'Created At' => $product->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    /**
     * Generate import template data
     */
    public function generateImportTemplate(): array
    {
        return [
            [
                'Name' => 'Example Product',
                'SKU' => 'PRD-20251130-001',
                'Barcode' => '1234567890',
                'Category ID' => '1',
                'Unit' => 'pcs',
                'Purchase Price' => '10000',
                'Selling Price' => '15000',
                'Min Stock' => '10',
                'Max Stock' => '100',
            ]
        ];
    }

    /**
     * Get product statistics
     */
    public function getProductStatistics(int $id): array
    {
        $product = $this->productRepository->getWithStocks($id);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $totalStock = $product->stocks->sum('quantity');
        $profitMargin = $product->purchase_price > 0
            ? (($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100
            : 0;

        return [
            'total_stock' => $totalStock,
            'profit_margin' => round($profitMargin, 2),
            'stores_count' => $product->stocks->count(),
            'low_stock_stores' => $product->stocks->filter(function ($stock) use ($product) {
                return $stock->quantity < ($product->min_stock ?? 0);
            })->count(),
        ];
    }
}
