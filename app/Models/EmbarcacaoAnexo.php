<?php

namespace App\Models;

use App\Enums\AnexoValidacaoStatus;
use App\Support\EmbarcacaoTiposAnexo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmbarcacaoAnexo extends Model
{
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
        return Storage::disk($this->disk)->url($this->path);
    }

    public function tipoLabel(): string
    {
        return EmbarcacaoTiposAnexo::label($this->tipo_codigo);
    }
}
