<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroAulaLancamento extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'data_lancamento',
        'data_pagamento',
        'qtd_alunos',
        'receita',
        'custo_barco',
        'custo_combustivel',
        'custo_cafe',
        'custo_ingresso',
        'taxa_marinha',
        'custo_total',
        'lucro',
    ];

    protected function casts(): array
    {
        return [
            'data_lancamento' => 'date',
            'data_pagamento' => 'date',
            'qtd_alunos' => 'integer',
            'receita' => 'decimal:2',
            'custo_barco' => 'decimal:2',
            'custo_combustivel' => 'decimal:2',
            'custo_cafe' => 'decimal:2',
            'custo_ingresso' => 'decimal:2',
            'taxa_marinha' => 'decimal:2',
            'custo_total' => 'decimal:2',
            'lucro' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
