<?php

namespace App\Models;

use App\Support\Normam211DocumentoCodigos;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DocumentoTipo extends TenantModel
{
    protected $fillable = [
        'empresa_id',
        'codigo',
        'nome',
        'auto_gerado',
        'modelo_slug',
    ];

    protected function casts(): array
    {
        return [
            'auto_gerado' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipoProcessos(): BelongsToMany
    {
        return $this->belongsToMany(TipoProcesso::class, 'documento_processo')
            ->withPivot(['obrigatorio', 'ordem'])
            ->withTimestamps();
    }

    /**
     * Slug usado em rotas de modelo PDF (coluna ou fallback por código; corrige slug legado sem prefixo «anexo-»).
     */
    public function modeloSlugParaRender(): string
    {
        $fromCol = trim((string) ($this->modelo_slug ?? ''));
        if ($fromCol === '2b-bdmoto-normam212') {
            return 'anexo-2b-bdmoto-normam212';
        }
        if ($fromCol !== '') {
            return $fromCol;
        }

        $codigo = (string) ($this->codigo ?? '');
        if (in_array($codigo, ['CIR_PROCURACAO', 'CHA_PROCURACAO', 'TIE_PROCURACAO'], true)) {
            return 'procuracao';
        }

        return Normam211DocumentoCodigos::slugModeloPorCodigoChecklist($codigo);
    }
}
