<?php

namespace App\Services;

use App\Models\PlatformActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class PlatformActivityLogService
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

        PlatformActivityLog::query()->create([
            'user_id' => (int) $user->id,
            'impersonator_id' => session('impersonator_id') ? (int) session('impersonator_id') : null,
            'empresa_id' => $empresaId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'summary' => $summary,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function logModel(Model $model, string $action, string $summary, ?array $properties = null, ?int $empresaId = null): void
    {
        $this->log(
            $action,
            $summary,
            $empresaId,
            $model::class,
            $model->getKey() !== null ? (int) $model->getKey() : null,
            $properties,
        );
    }
}

