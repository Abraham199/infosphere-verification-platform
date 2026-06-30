<?php

namespace App\Domain\Tenancy\Scopes;

use App\Domain\Tenancy\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId !== null && in_array('tenant_id', $model->getFillable(), true)) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }
}
