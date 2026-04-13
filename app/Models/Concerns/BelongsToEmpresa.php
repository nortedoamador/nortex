<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToEmpresa
{
    protected static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || ! $user->empresa_id) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.empresa_id', $user->empresa_id);
        });

        static::creating(function (Model $model) {
            if (! $model->getAttribute('empresa_id') && Auth::check()) {
                $model->setAttribute('empresa_id', Auth::user()->empresa_id);
            }
        });
    }
}

