<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroLoteParceriaItem extends Model
{
    protected $fillable = [
        'lote_id',
        'data_lancamento',
        'data_pagamento',
        'cliente_nome',
        'servico_tipo',
        'receita',
        'taxa_marinha',
        'custo_envio',
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
            'taxa_marinha' => 'decimal:2',
            'custo_envio' => 'decimal:2',
            'custo_total' => 'decimal:2',
            'lucro' => 'decimal:2',
            'nota_emitida' => 'boolean',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(FinanceiroLoteParceria::class, 'lote_id');
    }
}
