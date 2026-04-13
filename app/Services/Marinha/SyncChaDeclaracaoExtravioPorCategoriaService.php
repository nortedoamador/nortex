<?php

namespace App\Services\Marinha;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Support\Normam211DocumentoCodigos;

/**
 * No processo de extravio CHA, mantém apenas a declaração aplicável: Anexo 3-D (NORMAM-212)
 * se a categoria da CHA for somente Motonauta; caso contrário Anexo 5-D (NORMAM-211).
 */
final class SyncChaDeclaracaoExtravioPorCategoriaService
{
    /**
     * @return list<int> IDs de linhas de checklist alteradas
     */
    public function sync(Processo $processo): array
    {
        $processo->loadMissing(['tipoProcesso', 'habilitacao']);
        if ((string) ($processo->tipoProcesso?->slug ?? '') !== 'cha-extravio-roubo-furto-dano') {
            return [];
        }
        if (! $processo->habilitacao_id) {
            return [];
        }

        $processo->unsetRelation('documentosChecklist');
        $processo->load([
            'documentosChecklist.documentoTipo',
        ]);

        $linha5d = $processo->documentosChecklist->first(
            fn (ProcessoDocumento $pd) => Normam211DocumentoCodigos::isDeclaracaoAnexo5d((string) ($pd->documentoTipo?->codigo ?? ''))
        );
        $linha3d = $processo->documentosChecklist->first(
            fn (ProcessoDocumento $pd) => Normam211DocumentoCodigos::isDeclaracaoAnexo3d((string) ($pd->documentoTipo?->codigo ?? ''))
        );

        if (! $linha5d || ! $linha3d) {
            return [];
        }

        $categoria = trim((string) ($processo->habilitacao?->categoria ?? ''));
        $soMotonauta = ($categoria === 'Motonauta');

        $changed = [];

        if ($soMotonauta) {
            if ($linha5d->status !== ProcessoDocumentoStatus::Dispensado) {
                $linha5d->update([
                    'status' => ProcessoDocumentoStatus::Dispensado,
                    'declaracao_anexo_5d' => false,
                    'preenchido_via_modelo' => false,
                ]);
                $changed[] = $linha5d->id;
            }
            if ($linha3d->status === ProcessoDocumentoStatus::Dispensado) {
                $linha3d->update(['status' => ProcessoDocumentoStatus::Pendente]);
                $changed[] = $linha3d->id;
            }
        } else {
            if ($linha3d->status !== ProcessoDocumentoStatus::Dispensado) {
                $linha3d->update([
                    'status' => ProcessoDocumentoStatus::Dispensado,
                    'declaracao_anexo_3d' => false,
                    'preenchido_via_modelo' => false,
                ]);
                $changed[] = $linha3d->id;
            }
            if ($linha5d->status === ProcessoDocumentoStatus::Dispensado) {
                $linha5d->update(['status' => ProcessoDocumentoStatus::Pendente]);
                $changed[] = $linha5d->id;
            }
        }

        return $changed;
    }
}
