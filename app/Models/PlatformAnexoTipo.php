<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformAnexoTipo extends Model
{
    protected $table = 'platform_anexo_tipos';

    protected $fillable = [
        'nome',
        'slug',
        'ativo',
        'ordem',
        'max_size_mb',
        'allowed_mime_types',
        'allowed_extensions',
        'is_multiple',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'allowed_mime_types' => 'array',
            'allowed_extensions' => 'array',
            'is_multiple' => 'boolean',
        ];
    }
}

