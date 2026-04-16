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
        'tipo_custom',
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

    /**
     * Rótulo do tipo para listagens (inclui texto livre em «outro»).
     */
    public function getTipoLabelAttribute(): string
    {
        if ($this->tipo === 'outro' && filled($this->tipo_custom)) {
            return (string) $this->tipo_custom;
        }

        return match ($this->tipo) {
            'reuniao' => __('Reunião'),
            'marinha_atendimento' => __('Atendimento na Marinha'),
            'outro' => __('Outro'),
            default => (string) ($this->tipo ?? ''),
        };
    }
}
