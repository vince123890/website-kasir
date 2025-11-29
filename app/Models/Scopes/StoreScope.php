<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StoreScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply scope if user is authenticated
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Administrator SaaS and Tenant Owner can see all stores in their scope
        if ($user->hasRole(['Administrator SaaS', 'Tenant Owner'])) {
            return;
        }

        // Apply store filter if user has store_id (for Admin Toko and Kasir)
        if ($user->store_id) {
            $builder->where($model->getTable() . '.store_id', $user->store_id);
        }
    }
}
