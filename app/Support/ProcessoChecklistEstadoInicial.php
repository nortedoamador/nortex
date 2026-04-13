<?php

namespace App\Support;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\DocumentoTipo;

/**
 * Espelha a lógica de {@see \App\Services\ProcessoChecklistService::gerarParaProcesso} para o estado inicial da linha (sem persistir).
 */
final class ProcessoChecklistEstadoInicial
{
    /**
     * @return array{
     *     status: ProcessoDocumentoStatus,
     *     preenchido_via_modelo: bool,
     *     declaracao_so_modelo: bool
     * }
     */
    public static function resolver(DocumentoTipo $docTipo): array
    {
        $codigo = (string) ($docTipo->codigo ?? '');
        $slugModelo = $docTipo->modeloSlugParaRender();

        $declaracaoSoModelo = $slugModelo === 'anexo-2g'
            || $slugModelo === 'anexo-5h'
            || $slugModelo === 'anexo-5d'
            || $slugModelo === 'anexo-3d-extravio-cha-mta-normam212'
            || $codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
            || Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigo)
            || Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)
            || Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo);

        $autoGerado = (bool) ($docTipo->auto_gerado ?? false);
        $isResidencia = $codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP;
        $statusInicial = ($autoGerado && ! $isResidencia)
            ? ProcessoDocumentoStatus::Enviado
            : ProcessoDocumentoStatus::Pendente;
        $preenchidoModelo = false;

        if ($slugModelo !== '' && ! $declaracaoSoModelo) {
            $statusInicial = ProcessoDocumentoStatus::Enviado;
            $preenchidoModelo = true;
        }

        return [
            'status' => $statusInicial,
            'preenchido_via_modelo' => $preenchidoModelo,
            'declaracao_so_modelo' => $declaracaoSoModelo,
        ];
    }
}
