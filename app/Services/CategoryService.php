<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoryService
{
    protected CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data): Category
    {
        DB::beginTransaction();

        try {
            // Generate slug from name if not provided
            if (empty($data['slug'])) {
                $data['slug'] = \Str::slug($data['name']);
            }

            // Set default values
            $data['is_active'] = $data['is_active'] ?? true;

            // Create the category
            $category = $this->categoryRepository->create($data);

            Log::info('Category created', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'tenant_id' => $category->tenant_id,
            ]);

            DB::commit();

            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Update a category
     */
    public function updateCategory(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return false;
            }

            // Regenerate slug if name changed
            if (isset($data['name']) && $data['name'] !== $category->name) {
                $data['slug'] = \Str::slug($data['name']);
            }

            $result = $this->categoryRepository->update($id, $data);

            if ($result) {
                Log::info('Category updated', [
                    'category_id' => $id,
                    'updated_fields' => array_keys($data),
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update category', [
                'category_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(int $id, ?int $reassignToCategoryId = null): bool
    {
        DB::beginTransaction();

        try {
            $category = $this->categoryRepository->find($id);

            if (!$category) {
                return false;
            }

            // Check if category has products
            $hasProducts = $this->categoryRepository->checkHasProducts($id);

            if ($hasProducts) {
                if ($reassignToCategoryId) {
                    // Reassign products to another category
                    $this->categoryRepository->reassignProducts($id, $reassignToCategoryId);
                } else {
                    throw new Exception('Cannot delete category with existing products. Please reassign products first.');
                }
            }

            // Soft delete the category
            $result = $this->categoryRepository->delete($id);

            if ($result) {
                Log::info('Category deleted', [
                    'category_id' => $id,
                    'category_name' => $category->name,
                    'reassign_to' => $reassignToCategoryId,
                ]);
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete category', [
                'category_id' => $id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Bulk delete categories
     */
    public function bulkDelete(array $ids, ?int $reassignToCategoryId = null): array
    {
        DB::beginTransaction();

        try {
            $results = [
                'success' => [],
                'failed' => [],
            ];

            foreach ($ids as $id) {
                try {
                    // Check if category has products
                    $hasProducts = $this->categoryRepository->checkHasProducts($id);

                    if ($hasProducts) {
                        if ($reassignToCategoryId) {
                            // Reassign products to another category
                            $this->categoryRepository->reassignProducts($id, $reassignToCategoryId);
                        } else {
                            $results['failed'][] = $id;
                            continue;
                        }
                    }

                    // Delete the category
                    if ($this->categoryRepository->delete($id)) {
                        $results['success'][] = $id;
                    } else {
                        $results['failed'][] = $id;
                    }
                } catch (Exception $e) {
                    $results['failed'][] = $id;
                    Log::warning('Failed to delete category in bulk operation', [
                        'category_id' => $id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Bulk delete categories', [
                'success_count' => count($results['success']),
                'failed_count' => count($results['failed']),
            ]);

            DB::commit();

            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk delete categories', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if slug is available
     */
    public function isSlugAvailable(string $slug, int $tenantId, ?int $excludeId = null): bool
    {
        return $this->categoryRepository->isSlugAvailable($slug, $tenantId, $excludeId);
    }

    /**
     * Get category statistics
     */
    public function getCategoryStatistics(int $id): array
    {
        $category = $this->categoryRepository->getWithProductCount($id);

        if (!$category) {
            return [];
        }

        return [
            'total_products' => $category->products_count ?? 0,
            'subcategories_count' => $category->children->count() ?? 0,
        ];
    }

    /**
     * Export categories to array for Excel/CSV
     */
    public function exportCategories(int $tenantId): array
    {
        $categories = $this->categoryRepository->getAllForExport($tenantId);

        return $categories->map(function ($category) {
            return [
                'ID' => $category->id,
                'Name' => $category->name,
                'Slug' => $category->slug,
                'Parent Category' => $category->parent ? $category->parent->name : '-',
                'Products Count' => $category->products_count,
                'Status' => $category->is_active ? 'Active' : 'Inactive',
                'Created At' => $category->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }
}
