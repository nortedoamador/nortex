<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroLoteEngenhariaItem extends Model
{
    protected $fillable = [
        'lote_id',
        'data_lancamento',
        'data_pagamento',
        'cliente_nome',
        'servico_tipo',
        'receita',
        'custos_extras',
        'custo_total',
        'lucro',
        'nota_emitida',
    ];

    protected function casts(): array
    {
        return [
            'data_lancamento' => 'date',
            'data_pagamento' => 'date',
            'receita' => 'decimal:2',
            'custos_extras' => 'decimal:2',
            'custo_total' => 'decimal:2',
            'lucro' => 'decimal:2',
            'nota_emitida' => 'boolean',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(FinanceiroLoteEngenharia::class, 'lote_id');
    }
}
