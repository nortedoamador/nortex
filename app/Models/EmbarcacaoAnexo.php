<?php

namespace App\Models;

use App\Enums\AnexoValidacaoStatus;
use App\Models\Concerns\HasOpaqueAnexoRoutes;
use App\Support\EmbarcacaoTiposAnexo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class EmbarcacaoAnexo extends Model
{
    use HasOpaqueAnexoRoutes;

    protected $fillable = [
        'embarcacao_id',
        'tipo_codigo',
        'platform_anexo_tipo_id',
        'disk',
        'path',
        'nome_original',
        'rotulo',
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

            $builder->whereHas('embarcacao', function (Builder $q) use ($user) {
                $q->where('empresa_id', $user->empresa_id);
            });
        });
    }

    public function embarcacao(): BelongsTo
    {
        return $this->belongsTo(Embarcacao::class);
    }

    public function urlPublica(): string
    {
        return $this->signedInlineUrl();
    }

    public function tipoLabel(): string
    {
        return EmbarcacaoTiposAnexo::label($this->tipo_codigo);
    }

    protected function anexoInlineRouteName(): string
    {
        return 'embarcacoes.anexos.inline';
    }

    protected function anexoDownloadRouteName(): ?string
    {
        return 'embarcacoes.anexos.download';
    }

    protected function anexoPrintRouteName(): ?string
    {
        return 'embarcacoes.anexos.print';
    }

    protected function anexoDestroyRouteName(): ?string
    {
        return 'embarcacoes.anexos.destroy';
    }
}
