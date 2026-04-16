<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoModeloGlobal extends Model
{
    protected $table = 'documento_modelo_globais';

    protected $fillable = [
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

    public function documentoModelos(): HasMany
    {
        return $this->hasMany(DocumentoModelo::class, 'documento_modelo_global_id');
    }

    protected static function booted(): void
    {
        static::saving(function (DocumentoModeloGlobal $m): void {
            if ($m->isDirty('slug') && is_string($m->slug)) {
                $m->slug = trim(mb_strtolower(preg_replace('/\s+/', '-', $m->slug)));
            }
        });
    }
}
