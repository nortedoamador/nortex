<?php

namespace App\Models;

use App\Enums\AnexoValidacaoStatus;
use App\Models\Concerns\HasOpaqueAnexoRoutes;
use App\Support\EncryptedS3AnexoStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ProcessoDocumentoAnexo extends Model
{
    use HasOpaqueAnexoRoutes;

    protected $table = 'processo_documento_anexos';

    protected $fillable = [
        'processo_documento_id',
        'disk',
        'path',
        'nome_original',
        'mime',
        'tamanho',
        'extra_validation_status',
        'extra_validation_notes',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'extra_validation_status' => AnexoValidacaoStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder) {
            $user = Auth::user();
            if (! $user?->empresa_id) {
                return;
            }

            $builder->whereHas('processoDocumento.processo', function (Builder $q) use ($user) {
                $q->where('empresa_id', $user->empresa_id);
            });
        });
    }

    public function processoDocumento(): BelongsTo
    {
        return $this->belongsTo(ProcessoDocumento::class);
    }

    public function urlPublica(): string
    {
        return $this->signedInlineUrl();
    }

    protected function anexoInlineRouteName(): string
    {
        return 'processos.documentos.anexos.inline';
    }

    protected function anexoDestroyRouteName(): ?string
    {
        return 'processos.documentos.anexos.destroy';
    }
}
