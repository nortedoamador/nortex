<?php

namespace App\Models;

use App\Enums\TipoProcessoCategoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformTipoProcesso extends Model
{
    protected $table = 'platform_tipo_processos';

    /** Tipos de serviço CHA em que a etapa «Aguardando prova» faz parte do fluxo. */
    public const SLUGS_COM_ETAPA_AGUARDANDO_PROVA = [
        'cha-inscricao-arrais-amador',
        'cha-inscricao-arrais-amador-mestre-amador',
    ];

    /** CHA: exige seleção do registro de habilitação do cliente no processo. */
    public const SLUGS_EXIGEM_HABILITACAO_CHA_SELECIONADA = [
        'cha-renovacao',
        'cha-agregacao-motonauta',
        'cha-extravio-roubo-furto-dano',
    ];

    protected $fillable = [
        'nome',
        'slug',
        'categoria',
        'ativo',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'categoria' => TipoProcessoCategoria::class,
        ];
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class, 'platform_tipo_processo_id');
    }

    public function permiteStatusAguardandoProva(): bool
    {
        return in_array($this->slug, self::SLUGS_COM_ETAPA_AGUARDANDO_PROVA, true);
    }

    public function documentoRegras(): BelongsToMany
    {
        return $this->belongsToMany(DocumentoTipo::class, 'documento_processo', 'platform_tipo_processo_id', 'documento_tipo_id')
            ->withPivot(['empresa_id', 'obrigatorio', 'ordem'])
            ->withTimestamps()
            ->orderByPivot('ordem');
    }

    public function documentoRegrasDaEmpresa(int $empresaId): BelongsToMany
    {
        return $this->documentoRegras()->wherePivot('empresa_id', $empresaId);
    }
}

