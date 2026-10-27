<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (app()->bound('billing.bypass_company_scope') && app('billing.bypass_company_scope')) {
                return;
            }

            $companyId = session('id_company');

            if ($companyId !== null && $companyId !== '') {
                $builder->where($builder->getModel()->getTable().'.company_id', $companyId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $companyId = session('id_company');
                if ($companyId !== null && $companyId !== '') {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public static function withoutCompanyScope(): Builder
    {
        return static::withoutGlobalScope('company');
    }
}
