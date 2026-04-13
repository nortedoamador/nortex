<?php

namespace App\Models;

use App\Enums\AnexoValidacaoStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HabilitacaoAnexo extends Model
{
    protected $fillable = [
        'habilitacao_id',
        'tipo_codigo',
        'platform_anexo_tipo_id',
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

            $builder->whereHas('habilitacao', function (Builder $q) use ($user) {
                $q->where('empresa_id', $user->empresa_id);
            });
        });
    }

    public function habilitacao(): BelongsTo
    {
        return $this->belongsTo(Habilitacao::class);
    }

    public function urlPublica(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
