<?php

namespace App\Services;

use App\Enums\ProcessoDocumentoStatus;
use App\Support\ChecklistDocumentoModelo;
use App\Enums\ProcessoStatus;
use App\Models\Processo;

/**
 * Bloqueio de alteração de status enquanto houver documento obrigatório pendente.
 */
class ProcessoStatusService
{
    public function temDocumentoObrigatorioPendente(Processo $processo): bool
    {
        $processo->loadMissing([
            'documentosChecklist.anexos',
            'documentosChecklist.documentoTipo',
            'tipoProcesso',
            'tipoProcessoTenant',
        ]);

        $tipo = $processo->tipoProcesso;
        if ($tipo) {
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $processo->empresa_id),
            ]);
        } else {
            $tipo = $processo->tipoProcessoTenant;
            $tipo?->loadMissing([
                'documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
            ]);
        }

        $regrasObrigatorias = $tipo?->documentoRegras
            ->filter(fn ($dt) => (bool) $dt->pivot->obrigatorio) ?? collect();

        $porTipoId = $processo->documentosChecklist->keyBy('documento_tipo_id');

        foreach ($regrasObrigatorias as $docTipo) {
            $linha = $porTipoId->get($docTipo->id);
            if (! $linha) {
                return true;
            }

            if ($linha->status === ProcessoDocumentoStatus::Pendente) {
                return true;
            }

            // Enviado sem anexo: pendência, exceto declaração 2-G registrada no comprovante de residência.
            if ($linha->status === ProcessoDocumentoStatus::Enviado && $linha->anexos->isEmpty()) {
                if (! ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($linha)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Quantidade de itens obrigatórios em situação de pendência (para exibir no diálogo). */
    public function quantidadeDocumentosObrigatoriosPendentes(Processo $processo): int
    {
        $processo->loadMissing([
            'documentosChecklist.anexos',
            'documentosChecklist.documentoTipo',
            'tipoProcesso',
            'tipoProcessoTenant',
        ]);

        $tipo = $processo->tipoProcesso;
        if ($tipo) {
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $processo->empresa_id),
            ]);
        } else {
            $tipo = $processo->tipoProcessoTenant;
            $tipo?->loadMissing([
                'documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
            ]);
        }

        $regrasObrigatorias = $tipo?->documentoRegras
            ->filter(fn ($dt) => (bool) $dt->pivot->obrigatorio) ?? collect();

        $porTipoId = $processo->documentosChecklist->keyBy('documento_tipo_id');

        $n = 0;
        foreach ($regrasObrigatorias as $docTipo) {
            $linha = $porTipoId->get($docTipo->id);
            if (! $linha) {
                $n++;
                continue;
            }
            if ($linha->status === ProcessoDocumentoStatus::Pendente) {
                $n++;
                continue;
            }
            if ($linha->status === ProcessoDocumentoStatus::Enviado && $linha->anexos->isEmpty()) {
                if (! ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($linha)) {
                    $n++;
                }
            }
        }

        return $n;
    }

    /**
     * Ciência explícita só ao sair de «Em montagem» para outra etapa com obrigatórios pendentes.
     */
    public function requerConfirmacaoCienciaPendenciasDocumentais(Processo $processo, ProcessoStatus $novo): bool
    {
        if (! $this->temDocumentoObrigatorioPendente($processo)) {
            return false;
        }

        return $processo->status === ProcessoStatus::EmMontagem
            && $novo !== ProcessoStatus::EmMontagem;
    }

    /**
     * Com pendências documentais obrigatórias, a alteração de status só exige confirmação ao sair de «Em montagem».
     */
    public function podeAlterarStatus(Processo $processo, ProcessoStatus $novo, bool $confirmarCienciaPendenciasDocumentais = false): bool
    {
        if ($processo->status === $novo) {
            return true;
        }

        if (! $processo->aceitaDestinoStatus($novo)) {
            return false;
        }

        if ($this->requerConfirmacaoCienciaPendenciasDocumentais($processo, $novo)) {
            return $confirmarCienciaPendenciasDocumentais;
        }

        return true;
    }

    /** Mensagem para feedback ao operador (ex.: envio sem flag de ciência). */
    public function motivoBloqueio(Processo $processo): ?string
    {
        if ($processo->status !== ProcessoStatus::EmMontagem) {
            return null;
        }

        if (! $this->temDocumentoObrigatorioPendente($processo)) {
            return null;
        }

        return __('Há documentos obrigatórios pendentes. Para alterar o status para outra etapa, confirme no diálogo (processo com pendências).');
    }
}
