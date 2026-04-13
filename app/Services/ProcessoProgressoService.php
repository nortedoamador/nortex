<?php

namespace App\Services;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\Processo;
use App\Support\ChecklistDocumentoModelo;

/**
 * Progresso: concluídos / itens ativos do checklist (dispensados saem do denominador).
 */
class ProcessoProgressoService
{
    /**
     * @return array{
     *   enviados: int,
     *   obrigatorios_ativos: int,
     *   percentual: float,
     *   total_itens_ativos: int
     * }
     */
    public function calcular(Processo $processo): array
    {
        $processo->loadMissing([
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
        ]);

        $enviados = 0;
        $totalAtivos = 0;

        foreach ($processo->documentosChecklist as $linha) {
            $st = $linha->status;
            if ($st === ProcessoDocumentoStatus::Dispensado) {
                continue;
            }

            $totalAtivos++;

            if ($st === ProcessoDocumentoStatus::Fisico) {
                $enviados++;
            } elseif ($st === ProcessoDocumentoStatus::Enviado) {
                if ($linha->anexos->isNotEmpty()
                    || ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($linha)) {
                    $enviados++;
                }
            }
        }

        $percentual = $totalAtivos > 0
            ? round(100 * $enviados / $totalAtivos, 1)
            : 100.0;

        return [
            'enviados' => $enviados,
            // Compat: chave antiga ainda usada na UI; agora significa total de itens ativos.
            'obrigatorios_ativos' => $totalAtivos,
            'total_itens_ativos' => $totalAtivos,
            'percentual' => $percentual,
        ];
    }
}
