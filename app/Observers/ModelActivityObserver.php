<?php

namespace App\Observers;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\DocumentoTipo;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\TipoProcesso;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class ModelActivityObserver
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function created(Model $model): void
    {
        $this->activityLog->logModel(
            $model,
            'created',
            $this->summary('criou', $model),
            $this->safeSnapshot($model),
        );
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getChanges();
        unset($dirty['updated_at'], $dirty['password'], $dirty['remember_token']);
        if (array_key_exists('conteudo', $dirty)) {
            $dirty['conteudo'] = '(conteúdo alterado, '.strlen((string) $dirty['conteudo']).' bytes)';
        }
        if ($dirty === []) {
            return;
        }

        $this->activityLog->logModel(
            $model,
            'updated',
            $this->summary('atualizou', $model),
            ['alterado' => $this->maskSensitive($dirty)],
        );
    }

    public function deleted(Model $model): void
    {
        $this->activityLog->logModel(
            $model,
            'deleted',
            $this->summary('removeu', $model),
            null,
        );
    }

    private function summary(string $verb, Model $model): string
    {
        $label = $this->modelLabel($model);
        $user = auth()->user()?->name ?? 'Sistema';

        return "{$user} {$verb} {$label}.";
    }

    private function modelLabel(Model $model): string
    {
        return match ($model::class) {
            Cliente::class => 'cliente «'.Str::limit((string) ($model->nome ?? $model->getKey()), 80).'»',
            Embarcacao::class => 'embarcação #'.$model->getKey(),
            Habilitacao::class => 'habilitação #'.$model->getKey(),
            Processo::class => 'processo #'.$model->getKey(),
            ProcessoDocumento::class => 'item de checklist #'.$model->getKey(),
            TipoProcesso::class => 'tipo de processo «'.Str::limit((string) ($model->nome ?? ''), 80).'»',
            DocumentoTipo::class => 'tipo de documento «'.Str::limit((string) ($model->nome ?? ''), 80).'»',
            DocumentoModelo::class => 'modelo «'.Str::limit((string) ($model->titulo ?? $model->slug ?? ''), 80).'»',
            default => class_basename($model).' #'.$model->getKey(),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function safeSnapshot(Model $model): ?array
    {
        $attrs = $model->getAttributes();
        unset($attrs['password'], $attrs['remember_token'], $attrs['conteudo']);

        return $this->maskSensitive($attrs);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function maskSensitive(array $data): array
    {
        foreach (array_keys($data) as $k) {
            if (is_string($k) && str_contains(strtolower($k), 'password')) {
                $data[$k] = '***';
            }
        }

        return $data;
    }
}
