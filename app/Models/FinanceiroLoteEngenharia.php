<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceiroLoteEngenharia extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'mes_referencia',
        'empresa_parceira',
        'status_pagamento',
        'comprovante_path',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FinanceiroLoteEngenhariaItem::class, 'lote_id');
    }
}
