<?php

namespace App\Models;

use App\Support\DocumentoModeloPadraoFicheiro;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoModelo extends TenantModel
{
    protected $fillable = [
        'empresa_id',
        'slug',
        'titulo',
        'referencia',
        'conteudo',
        'conteudo_upload_bruto',
        'upload_mapeamento_pendente',
        'mapeamento_upload',
    ];

    protected $casts = [
        'mapeamento_upload' => 'array',
        'upload_mapeamento_pendente' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (DocumentoModelo $modelo): void {
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
