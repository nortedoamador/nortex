<?php

namespace App\Models;

use App\Support\DocumentoModeloPadraoFicheiro;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoModelo extends TenantModel
{
    protected $fillable = [
        'empresa_id',
        'documento_modelo_global_id',
        'slug',
        'titulo',
        'referencia',
        'conteudo',
        'conteudo_upload_bruto',
        'upload_mapeamento_pendente',
        'mapeamento_upload',
        'personalizado',
        'global_synced_at',
    ];

    protected $casts = [
        'mapeamento_upload' => 'array',
        'upload_mapeamento_pendente' => 'boolean',
        'personalizado' => 'boolean',
        'global_synced_at' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documentoModeloGlobal(): BelongsTo
    {
        return $this->belongsTo(DocumentoModeloGlobal::class, 'documento_modelo_global_id');
    }

    public function escreveFicheiroPadraoNoRepositorio(): bool
    {
        return $this->documento_modelo_global_id === null;
    }

    protected static function booted(): void
    {
        static::deleting(function (DocumentoModelo $modelo): void {
            if ($modelo->documento_modelo_global_id !== null) {
                return;
            }
            $slug = (string) $modelo->getAttribute('slug');
            DocumentoModeloPadraoFicheiro::apagarFicheiroBladeSeExistir($slug);
        });
    }

    /**
     * Normaliza slug (ex.: "Anexo 2G" -> "anexo-2g").
     *
     * @return Attribute<string, string>
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($v) => trim(mb_strtolower(preg_replace('/\s+/', '-', (string) $v))),
        );
    }
}
