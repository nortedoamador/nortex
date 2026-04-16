<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroAdminDiretoLancamento extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'data_servico',
        'data_pagamento',
        'cliente_nome',
        'servico_tipo',
        'status_pagamento',
        'receita',
        'taxa_marinha',
        'custo_envio',
        'custo_total',
        'lucro',
        'comprovante_path',
        'nota_emitida',
    ];

    protected function casts(): array
    {
        return [
            'data_servico' => 'date',
            'data_pagamento' => 'date',
            'receita' => 'decimal:2',
            'taxa_marinha' => 'decimal:2',
            'custo_envio' => 'decimal:2',
            'custo_total' => 'decimal:2',
            'lucro' => 'decimal:2',
            'nota_emitida' => 'boolean',
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
