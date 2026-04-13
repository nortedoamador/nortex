<?php

namespace App\Models;

use App\Enums\ProcessoDocumentoStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ProcessoDocumento extends Model
{
    protected $table = 'processo_documentos';

    /**
     * data_validade_documento: fim da validade do documento no checklist (ex.: validade da CNH).
     * Usada só para orientação no app; a data de comparação (“referência”) é hoje (ou parâmetro explícito no serviço), nunca data de prova.
     */
    protected $fillable = [
        'processo_id',
        'documento_tipo_id',
        'status',
        'data_validade_documento',
        'declaracao_residencia_2g',
        'declaracao_anexo_5h',
        'declaracao_anexo_5d',
        'declaracao_anexo_3d',
        'preenchido_via_modelo',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcessoDocumentoStatus::class,
            'data_validade_documento' => 'date',
            'declaracao_residencia_2g' => 'boolean',
            'declaracao_anexo_5h' => 'boolean',
            'declaracao_anexo_5d' => 'boolean',
            'declaracao_anexo_3d' => 'boolean',
            'preenchido_via_modelo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder) {
            $user = Auth::user();
            if (! $user?->empresa_id) {
                return;
            }

            $builder->whereHas('processo', function (Builder $q) use ($user) {
                $q->where('empresa_id', $user->empresa_id);
            });
        });
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function documentoTipo(): BelongsTo
    {
        return $this->belongsTo(DocumentoTipo::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(ProcessoDocumentoAnexo::class);
    }
}
