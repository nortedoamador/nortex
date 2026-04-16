<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaCompromisso extends Model
{
    use BelongsToEmpresa;

    protected $table = 'empresa_compromissos';

    protected $fillable = [
        'empresa_id',
        'tipo',
        'titulo',
        'data',
        'hora_inicio',
        'hora_fim',
        'local',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
