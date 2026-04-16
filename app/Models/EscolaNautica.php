<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EscolaNautica extends TenantModel
{
    protected $table = 'escola_nauticas';

    protected $fillable = [
        'empresa_id',
        'nome',
        'cnpj',
        'diretor_cliente_id',
    ];

    public function diretor(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'diretor_cliente_id');
    }

    public function capitanias(): HasMany
    {
        return $this->hasMany(EscolaCapitania::class, 'escola_nautica_id');
    }
}
