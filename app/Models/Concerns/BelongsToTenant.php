<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (tenant('id') && !$model->tenant_id) {
                $model->tenant_id = tenant('id');
            }
        });
    }

    /**
     * Remove the tenant scope from the query (for admin/global access).
     */
    public static function withoutTenancy(): Builder
    {
        return (new static)->newQueryWithoutScope(TenantScope::class);
    }

    /**
     * Shortcut to manually disable tenancy within an existing query.
     */
    public function scopeWithoutTenancy($query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
