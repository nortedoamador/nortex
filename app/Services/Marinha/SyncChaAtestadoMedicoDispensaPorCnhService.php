<?php

namespace App\Services\Marinha;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Support\ChaChecklistDocumentoCodigos;
use Carbon\Carbon;

/**
 * Quando a CNH está anexada, com data de validade informada e válida na data de referência,
 * marca o atestado médico/psicofísico como dispensado (sem anexos). Reverte para pendente
 * quando a condição deixa de ser satisfeita e o atestado ainda não tem arquivos.
 */
final class SyncChaAtestadoMedicoDispensaPorCnhService
{
    public function __construct(
        private CnhAtestadoOrientacaoService $orientacao,
    ) {}

    /**
     * @return list<int> IDs de linhas de checklist alteradas (ex.: item do atestado).
     */
    public function sync(Processo $processo): array
    {
        $processo->unsetRelation('documentosChecklist');
        $processo->load([
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
        ]);

        $cnh = $processo->documentosChecklist->first(
            fn (ProcessoDocumento $pd) => ChaChecklistDocumentoCodigos::isCnhComValidade(
                (string) ($pd->documentoTipo?->codigo ?? '')
            )
        );
        $atestado = $processo->documentosChecklist->first(
            fn (ProcessoDocumento $pd) => ChaChecklistDocumentoCodigos::isAtestadoMedicoPsicofisico(
                (string) ($pd->documentoTipo?->codigo ?? '')
            )
        );

        if (! $cnh || ! $atestado) {
            return [];
        }

        $validade = $cnh->data_validade_documento;
        $validadeCarbon = $validade ? Carbon::parse((string) $validade) : null;

        $cnhDispensa = $cnh->anexos->isNotEmpty()
            && $this->orientacao->cnhDispensaAtestadoMedico($validadeCarbon);

        $changed = [];

        if ($cnhDispensa) {
            if ($atestado->anexos->isEmpty() && $atestado->status !== ProcessoDocumentoStatus::Dispensado) {
                $atestado->update(['status' => ProcessoDocumentoStatus::Dispensado]);
                $changed[] = $atestado->id;
            }
        } elseif ($atestado->status === ProcessoDocumentoStatus::Dispensado
            && $atestado->anexos->isEmpty()) {
            $atestado->update(['status' => ProcessoDocumentoStatus::Pendente]);
            $changed[] = $atestado->id;
        }

        return $changed;
    }
}
