<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscolaCapitania extends Model
{
    protected $table = 'escola_capitanias';

    protected $fillable = [
        'escola_nautica_id',
        'capitania_jurisdicao',
        'capitania_endereco',
        'representante_funcao',
        'representante_posto',
        'representante_nome',
    ];

    public function escolaNautica(): BelongsTo
    {
        return $this->belongsTo(EscolaNautica::class, 'escola_nautica_id');
    }
}
