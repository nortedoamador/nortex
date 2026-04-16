<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroDespesaLancamento extends Model
{
    protected $fillable = [
        'empresa_id',
        'user_id',
        'data_lancamento',
        'data_pagamento',
        'descricao',
        'valor',
        'fixa_grupo_id',
        'nota_path',
    ];

    protected function casts(): array
    {
        return [
            'data_lancamento' => 'date',
            'data_pagamento' => 'date',
            'valor' => 'decimal:2',
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
