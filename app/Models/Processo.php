<?php

namespace App\Models;

use App\Enums\ProcessoStatus;
use App\Services\ProcessoChecklistService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Processo extends TenantModel
{
    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'embarcacao_id',
        'habilitacao_id',
        'tipo_processo_id',
        'platform_tipo_processo_id',
        'status',
        'observacoes',
        'jurisdicao',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcessoStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Processo $processo) {
            app(ProcessoChecklistService::class)->gerarParaProcesso($processo);
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function habilitacao(): BelongsTo
    {
        return $this->belongsTo(Habilitacao::class);
    }

    public function tipoProcesso(): BelongsTo
    {
        return $this->belongsTo(PlatformTipoProcesso::class, 'platform_tipo_processo_id');
    }

    public function tipoProcessoTenant(): BelongsTo
    {
        return $this->belongsTo(TipoProcesso::class, 'tipo_processo_id');
    }

    public function documentosChecklist(): HasMany
    {
        return $this->hasMany(ProcessoDocumento::class);
    }

    public function postIts(): HasMany
    {
        return $this->hasMany(ProcessoPostIt::class)->orderBy('created_at');
    }

    /**
     * @return list<ProcessoStatus>
     */
    public function statusesPermitidosParaAlteracao(): array
    {
        $opts = ProcessoStatus::opcoesParaAlteracao($this->tipoProcesso ?? $this->tipoProcessoTenant);
        if (in_array($this->status, $opts, true)) {
            return $opts;
        }

        $opts[] = $this->status;
        $ordem = ProcessoStatus::kanbanOrder();
        usort($opts, function (ProcessoStatus $a, ProcessoStatus $b) use ($ordem): int {
            $ia = array_search($a, $ordem, true);
            $ib = array_search($b, $ordem, true);

            return ($ia === false ? 999 : $ia) <=> ($ib === false ? 999 : $ib);
        });

        return $opts;
    }

    public function aceitaDestinoStatus(ProcessoStatus $novo): bool
    {
        if ($novo !== ProcessoStatus::AguardandoProva) {
            return true;
        }

        return ($this->tipoProcesso ?? $this->tipoProcessoTenant)?->permiteStatusAguardandoProva() ?? false;
    }
}
