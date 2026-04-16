<?php

namespace App\Services\Marinha;

use App\Enums\EmbarcacaoTipoNavegacao;
use App\Models\Embarcacao;

/**
 * Motor de regras: decide quais anexos (modelos PDF) devem ser usados no checklist,
 * com base na ficha da embarcação.
 *
 * Regras fornecidas pelo negócio (Apr/2026):
 * - comprimento (m)
 * - tipo_navegacao (Interior | Mar Aberto)
 * - atividade (Esporte e Recreio | outros)
 * - tipo (Moto Aquática | outros)
 */
final class EmbarcacaoChecklistAnexosRulesService
{
    /**
     * @return array{requerimento_slug: string, bade_bsade_slug: string, declaracao_residencia_slug: string}
     */
    public function resolver(Embarcacao $embarcacao): array
    {
        $len = $this->comprimentoMetros($embarcacao);
        $tipoNav = $embarcacao->tipo_navegacao;
        $atividade = trim((string) ($embarcacao->atividade ?? ''));
        $isEsporteRecreio = mb_strtolower($atividade) === mb_strtolower('Esporte e Recreio');

        $tipo = trim((string) ($embarcacao->tipo ?? ''));
        $isMoto = $this->isMotoAquatica($tipo);

        // Normaliza tipo_navegacao para decisão (fallback para interior se estiver vazio).
        $isInterior = $tipoNav instanceof EmbarcacaoTipoNavegacao
            ? $tipoNav === EmbarcacaoTipoNavegacao::Interior
            : true;
        $isMarAberto = $tipoNav instanceof EmbarcacaoTipoNavegacao
            ? $tipoNav === EmbarcacaoTipoNavegacao::MarAberto
            : false;

        // 2) Moto aquática (NORMAM-212) — regra 2.
        if ($len <= 6.0 && $isInterior && $isEsporteRecreio && $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2a-normam212',
                'bade_bsade_slug' => 'anexo-2b-bdmoto-normam212',
                'declaracao_residencia_slug' => 'anexo-1c-normam212',
            ];
        }

        // 1) Até 6m, interior, esporte/recreio, não-moto (NORMAM-211).
        if ($len <= 6.0 && $isInterior && $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2c-normam211',
                'bade_bsade_slug' => 'anexo-2b-bsade',
                'declaracao_residencia_slug' => 'anexo-2g',
            ];
        }

        // 3) Até 6m, interior, atividade != esporte/recreio, não-moto (NORMAM-202).
        if ($len <= 6.0 && $isInterior && ! $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2f-normam202',
                'bade_bsade_slug' => 'anexo-2e-normam202',
                'declaracao_residencia_slug' => 'anexo-2p-normam202',
            ];
        }

        // 4) > 6m, interior, esporte/recreio, não-moto (NORMAM-211).
        if ($len > 6.0 && $isInterior && $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2c-normam211',
                'bade_bsade_slug' => 'anexo-2a-normam211',
                'declaracao_residencia_slug' => 'anexo-2g',
            ];
        }

        // 5) > 6m, interior, atividade != esporte/recreio, não-moto (NORMAM-202).
        if ($len > 6.0 && $isInterior && ! $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2f-normam202',
                'bade_bsade_slug' => 'anexo-2b-bade-normam202',
                'declaracao_residencia_slug' => 'anexo-2p-normam202',
            ];
        }

        // 6) > 6m, mar aberto, esporte/recreio, não-moto (NORMAM-211).
        if ($len > 6.0 && $isMarAberto && $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2c-normam211',
                'bade_bsade_slug' => 'anexo-2a-normam211',
                'declaracao_residencia_slug' => 'anexo-2g',
            ];
        }

        // 7) > 6m, mar aberto, atividade != esporte/recreio, não-moto (NORMAM-201).
        if ($len > 6.0 && $isMarAberto && ! $isEsporteRecreio && ! $isMoto) {
            return [
                'requerimento_slug' => 'anexo-2e-normam201',
                'bade_bsade_slug' => 'anexo-2b-bade-normam201',
                'declaracao_residencia_slug' => 'anexo-2p-normam201',
            ];
        }

        // Fallback conservador (mantém comportamento atual: NORMAM-211, caso não tenha dados completos).
        return [
            'requerimento_slug' => 'anexo-2c-normam211',
            'bade_bsade_slug' => 'anexo-2b-bsade',
            'declaracao_residencia_slug' => 'anexo-2g',
        ];
    }

    private function isMotoAquatica(string $tipoEmbarcacao): bool
    {
        $t = mb_strtolower(trim($tipoEmbarcacao));
        if ($t === '') {
            return false;
        }

        // Opção padrão do formulário: "Moto-Aquática/similar"
        return str_contains($t, 'moto') && (str_contains($t, 'aquatica') || str_contains($t, 'aquática'));
    }

    private function comprimentoMetros(Embarcacao $embarcacao): float
    {
        $m = $embarcacao->comprimento_m;
        if (is_numeric($m)) {
            return max(0.0, (float) $m);
        }

        // Fallback legado: alguns registros guardam "comprimento" como string.
        $raw = trim((string) ($embarcacao->comprimento ?? ''));
        if ($raw === '') {
            return 0.0;
        }
        $raw = str_replace(',', '.', $raw);
        if (preg_match('/(\d+(?:\.\d+)?)/', $raw, $mch) === 1) {
            return max(0.0, (float) $mch[1]);
        }

        return 0.0;
    }
}

