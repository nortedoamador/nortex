<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;

final class PlanLimitService
{
    public function maxUsers(Empresa $empresa): int
    {
        $override = is_array($empresa->plan_overrides) ? ($empresa->plan_overrides['max_users'] ?? null) : null;
        if (is_numeric($override)) {
            return max(0, (int) $override);
        }

        return $empresa->plan?->max_users ?? 0;
    }

    public function empresaPodeCriarMaisUsuarios(Empresa $empresa): bool
    {
        $max = $this->maxUsers($empresa);
        if ($max <= 0) {
            return true;
        }

        $count = User::query()->where('empresa_id', $empresa->id)->count();

        return $count < $max;
    }
}

