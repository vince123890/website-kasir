<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CategoryRepository
{
    /**
     * Get all categories with optional pagination and search
     */
    public function getByTenant(int $tenantId, int $perPage = 15, ?string $search = null, array $filters = []): LengthAwarePaginator
    {
        $query = Category::where('tenant_id', $tenantId)
            ->with(['parent'])
            ->withCount(['products']);

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply filters
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['parent_id'])) {
            if ($filters['parent_id'] === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get all active categories for dropdown
     */
    public function getAllActive(int $tenantId): Collection
    {
        return Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);
    }

    /**
     * Get category with product count
     */
    public function getWithProductCount(int $id): ?Category
    {
        return Category::withCount(['products'])
            ->with(['parent', 'children'])
            ->find($id);
    }

    /**
     * Get a single category by ID
     */
    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        // Generate unique slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $data['tenant_id']);
        } else {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $data['tenant_id']);
        }

        return Category::create($data);
    }

    /**
     * Update a category
     */
    public function update(int $id, array $data): bool
    {
        $category = $this->find($id);

        if (!$category) {
            return false;
        }

        // If slug is being updated, ensure uniqueness
        if (isset($data['slug']) && $data['slug'] !== $category->slug) {
            $data['slug'] = $this->ensureUniqueSlug($data['slug'], $category->tenant_id, $id);
        }

        return $category->update($data);
    }

    /**
     * Delete a category (soft delete)
     */
    public function delete(int $id): bool
    {
        $category = $this->find($id);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }

    /**
     * Bulk delete categories
     */
    public function bulkDelete(array $ids): int
    {
        return Category::whereIn('id', $ids)->delete();
    }

    /**
     * Check if category has products
     */
    public function checkHasProducts(int $id): bool
    {
        $category = Category::withCount('products')->find($id);

        if (!$category) {
            return false;
        }

        return $category->products_count > 0;
    }

    /**
     * Reassign products to another category
     */
    public function reassignProducts(int $fromCategoryId, int $toCategoryId): int
    {
        $category = $this->find($fromCategoryId);

        if (!$category) {
            return 0;
        }

        return $category->products()->update(['category_id' => $toCategoryId]);
    }

    /**
     * Generate a unique slug
     */
    protected function generateUniqueSlug(string $name, int $tenantId): string
    {
        $slug = \Str::slug($name);
        return $this->ensureUniqueSlug($slug, $tenantId);
    }

    /**
     * Ensure slug is unique by appending a number if needed
     */
    protected function ensureUniqueSlug(string $slug, int $tenantId, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (!$this->isSlugAvailable($slug, $tenantId, $excludeId)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug is available
     */
    public function isSlugAvailable(string $slug, int $tenantId, ?int $excludeId = null): bool
    {
        $query = Category::where('slug', $slug)
            ->where('tenant_id', $tenantId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get category by slug
     */
    public function getBySlug(string $slug, int $tenantId): ?Category
    {
        return Category::where('slug', $slug)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Get parent categories (no parent)
     */
    public function getParentCategories(int $tenantId): Collection
    {
        return Category::where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get subcategories of a parent
     */
    public function getSubcategories(int $parentId): Collection
    {
        return Category::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active categories count
     */
    public function getActiveCount(int $tenantId): int
    {
        return Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get all categories for export
     */
    public function getAllForExport(int $tenantId): Collection
    {
        return Category::where('tenant_id', $tenantId)
            ->with(['parent'])
            ->withCount(['products'])
            ->orderBy('name')
            ->get();
    }
}
