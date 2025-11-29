<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
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

        // Administrator SaaS can see all data
        if ($user->hasRole('Administrator SaaS')) {
            return;
        }

        // Apply tenant filter if user has tenant_id
        if ($user->tenant_id) {
            $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
        }
    }
}
