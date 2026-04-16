<?php

namespace App\Models;

use App\Support\AulaCurriculoNormam;

class EmpresaAtestadoNormamDuracao extends TenantModel
{
    protected $table = 'empresa_atestado_normam_duracoes';

    protected $fillable = [
        'empresa_id',
        'programa',
        'item_key',
        'duracao_minutos',
    ];

    /**
     * Verdadeiro quando todos os itens NORMAM do programa têm duração guardada (não nula).
     */
    public static function programaDuracoesNormamCompleto(string $programa): bool
    {
        $keys = AulaCurriculoNormam::allKeys($programa);
        $rows = static::query()
            ->where('programa', $programa)
            ->whereIn('item_key', $keys)
            ->get()
            ->keyBy('item_key');

        foreach ($keys as $key) {
            $row = $rows->get($key);
            if ($row === null || $row->duracao_minutos === null) {
                return false;
            }
        }

        return true;
    }

    public static function planoTreinamentoNormamCompleto(): bool
    {
        return static::programaDuracoesNormamCompleto(AulaCurriculoNormam::PROGRAMA_ARA)
            && static::programaDuracoesNormamCompleto(AulaCurriculoNormam::PROGRAMA_MTA);
    }
}
