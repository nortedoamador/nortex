<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EscolaInstrutor extends TenantModel
{
    protected $table = 'escola_instrutores';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'cha_numero',
        'cha_categoria',
        'cha_data_emissao',
        'cha_data_validade',
        'cha_jurisdicao',
    ];

    protected function casts(): array
    {
        return [
            'cha_data_emissao' => 'date',
            'cha_data_validade' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsToMany<AulaNautica> */
    public function aulas(): BelongsToMany
    {
        return $this->belongsToMany(AulaNautica::class, 'aula_nautica_escola_instrutores')
            ->withTimestamps();
    }
}
