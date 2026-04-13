<?php

namespace App\Models;

use App\Enums\TipoProcessoCategoria;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoProcesso extends TenantModel
{
    /** Tipos de serviço CHA em que a etapa «Aguardando prova» faz parte do fluxo. */
    public const SLUGS_COM_ETAPA_AGUARDANDO_PROVA = [
        'cha-inscricao-arrais-amador',
        'cha-inscricao-arrais-amador-mestre-amador',
    ];

    protected $fillable = [
        'empresa_id',
        'nome',
        'slug',
        'categoria',
    ];

    protected function casts(): array
    {
        return [
            'categoria' => TipoProcessoCategoria::class,
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documentoRegras(): BelongsToMany
    {
        return $this->belongsToMany(DocumentoTipo::class, 'documento_processo')
            ->withPivot(['obrigatorio', 'ordem'])
            ->withTimestamps()
            ->orderByPivot('ordem');
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    public function permiteStatusAguardandoProva(): bool
    {
        return in_array($this->slug, self::SLUGS_COM_ETAPA_AGUARDANDO_PROVA, true);
    }
}
