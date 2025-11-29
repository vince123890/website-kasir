<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Restore a soft deleted record
     */
    public function restore(int $id): bool;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get records by tenant
     */
    public function byTenant(int $tenantId): Collection;

    /**
     * Get records by store
     */
    public function byStore(int $storeId): Collection;

    /**
     * Get only active records
     */
    public function active(): Collection;
}
