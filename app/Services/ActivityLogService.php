<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\FinanceiroLoteEngenhariaItem;
use App\Models\FinanceiroLoteParceriaItem;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoPostIt;
use App\Support\TenantEmpresaContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class ActivityLogService
{
    public function log(
        string $action,
        string $summary,
        ?int $empresaId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $properties = null,
    ): void {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();
        $eid = $empresaId ?? (int) ($user->empresa_id ?? 0);
        if ($eid <= 0) {
            return;
        }

        // Em rotas "admin desta empresa" dentro da plataforma, não expor a identidade
        // do administrador da plataforma nos logs visíveis ao tenant.
        $userId = (int) $user->id;
        if (
            TenantEmpresaContext::isPlatformEmpresaAdminRoute()
            && ($user->is_platform_admin ?? false)
            && (int) ($user->empresa_id ?? 0) !== $eid
        ) {
            $userId = 0;
        }

        ActivityLog::query()->create([
            'empresa_id' => $eid,
            'user_id' => $userId > 0 ? $userId : null,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'summary' => $summary,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function logModel(Model $model, string $action, string $summary, ?array $properties = null): void
    {
        $empresaId = $this->resolveEmpresaId($model);
        if ($empresaId === null) {
            return;
        }

        $this->log(
            $action,
            $summary,
            $empresaId,
            $model::class,
            $model->getKey() !== null ? (int) $model->getKey() : null,
            $properties,
        );
    }

    private function resolveEmpresaId(Model $model): ?int
    {
        if ($model->getAttribute('empresa_id')) {
            return (int) $model->getAttribute('empresa_id');
        }

        if ($model instanceof ProcessoDocumento) {
            return $model->processo()->value('empresa_id');
        }

        if ($model instanceof ProcessoPostIt) {
            return $model->processo()->value('empresa_id');
        }

        if ($model instanceof FinanceiroLoteEngenhariaItem) {
            return $model->lote()->value('empresa_id');
        }

        if ($model instanceof FinanceiroLoteParceriaItem) {
            return $model->lote()->value('empresa_id');
        }

        return null;
    }
}
