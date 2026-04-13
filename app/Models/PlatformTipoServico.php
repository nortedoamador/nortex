<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformTipoServico extends Model
{
    protected $table = 'platform_tipo_servicos';

    protected $fillable = [
        'nome',
        'slug',
        'ativo',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }
}

