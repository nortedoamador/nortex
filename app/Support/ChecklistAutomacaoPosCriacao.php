<?php

namespace App\Support;

/**
 * Notas para auditoria: automações que alteram linhas depois da criação do processo (não confundir com estado inicial do checklist).
 */
final class ChecklistAutomacaoPosCriacao
{
    /**
     * @return list<string>
     */
    public static function notasParaLinha(string $platformTipoSlug, string $codigoDocumento): array
    {
        $slug = trim($platformTipoSlug);
        $codigo = trim($codigoDocumento);
        $notas = [];

        if (ChaChecklistDocumentoCodigos::isCnhComValidade($codigo)
            || ChaChecklistDocumentoCodigos::isAtestadoMedicoPsicofisico($codigo)) {
            $notas[] = 'Após anexar CNH com validade ou atualizar documentos: SyncChaAtestadoMedicoDispensaPorCnhService pode dispensar o atestado médico/psicofísico se aplicável.';
        }

        if ($slug === 'cha-extravio-roubo-furto-dano'
            && (Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)
                || Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo))) {
            $notas[] = 'Após criar o processo (com habilitação selecionada): SyncChaDeclaracaoExtravioPorCategoriaService dispensa 5-D ou 3-D conforme categoria da CHA (Motonauta isolada vs demais).';
        }

        return $notas;
    }
}
