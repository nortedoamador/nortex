<?php

namespace App\Services\Marinha;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Support\ChaChecklistDocumentoCodigos;
use App\Support\ClienteTiposAnexo;

/**
 * Quando há cópia da CNH no processo ou na ficha do cliente, marca o atestado médico/psicofísico como dispensado
 * (sem anexos no item do atestado). Reverte para pendente quando deixa de haver CNH anexada em ambos os sítios
 * e o atestado ainda não tem arquivos.
 */
final class SyncChaAtestadoMedicoDispensaPorCnhService
{
    /**
     * @return list<int> IDs de linhas de checklist alteradas (ex.: item do atestado).
     */
    public function sync(Processo $processo): array
    {
        $processo->unsetRelation('documentosChecklist');
        $processo->load([
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
            'cliente.anexos',
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

        $cnhNoProcesso = $cnh->anexos->isNotEmpty();
        $cnhNaFichaCliente = $processo->cliente !== null
            && $processo->cliente->anexos->contains(
                fn ($a) => (string) ($a->tipo_codigo ?? '') === ClienteTiposAnexo::CNH
            );

        $cnhDispensa = $cnhNoProcesso || $cnhNaFichaCliente;

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
