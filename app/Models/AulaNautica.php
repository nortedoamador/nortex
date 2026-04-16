<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use App\Models\Concerns\UsesHashidsRouteKey;
use App\Support\TenantHashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AulaNautica extends Model
{
    use BelongsToEmpresa;
    use UsesHashidsRouteKey;

    protected $table = 'aulas_nauticas';

    protected $fillable = [
        'empresa_id',
        'numero_oficio',
        'data_aula',
        'local',
        'tipo_aula',
        'hora_inicio',
        'hora_fim',
        'status',
        'comunicado_enviado_em',
    ];

    protected $casts = [
        'data_aula' => 'date',
        // Campos TIME são guardados como string "HH:MM:SS" (ou "HH:MM") no DB.
        'hora_inicio' => 'string',
        'hora_fim' => 'string',
        'comunicado_enviado_em' => 'datetime',
    ];

    /** @return BelongsToMany<Cliente> */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'aula_nautica_alunos')
            ->withTimestamps();
    }

    /** @return BelongsToMany<User> */
    public function instrutores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'aula_nautica_instrutores')
            ->withTimestamps();
    }

    /** @return BelongsToMany<EscolaInstrutor> */
    public function escolaInstrutores(): BelongsToMany
    {
        return $this->belongsToMany(EscolaInstrutor::class, 'aula_nautica_escola_instrutores')
            ->withTimestamps();
    }

    public static function routeHashidType(): int
    {
        return TenantHashids::TYPE_AULA_NAUTICA;
    }
}

