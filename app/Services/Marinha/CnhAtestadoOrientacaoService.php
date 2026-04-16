<?php

namespace App\Services\Marinha;

use App\Support\ChaChecklistDocumentoCodigos;
use Carbon\CarbonInterface;

/**
 * Regras de orientação (não substitui o operador; não usa data de prova).
 *
 * O checklist guarda em `ProcessoDocumento::data_validade_documento` o fim da validade do documento
 * (ex.: data impressa na CNH). A “data de referência” para comparar com essa validade é, por padrão,
 * hoje no fuso do app — não confundir com data de exame/prova.
 *
 * CNH:
 * - Pode ser aceita como documento mesmo vencida (o operador decide o fluxo).
 * - NÃO dispensa atestado médico se a CNH estiver vencida na data de referência.
 * - Dispensa atestado médico apenas se a CNH estiver válida na data de referência (ex.: hoje).
 */
final class CnhAtestadoOrientacaoService
{
    /**
     * @param  CarbonInterface|null  $dataValidadeDocumento  Fim da validade do documento (ex.: validade da CNH).
     * @param  CarbonInterface|null  $dataReferencia  Dia com o qual se compara a validade; padrão: hoje. Nunca usar data de prova.
     */
    public function cnhValidaNaReferencia(?CarbonInterface $dataValidadeDocumento, ?CarbonInterface $dataReferencia = null): bool
    {
        if ($dataValidadeDocumento === null) {
            return false;
        }

        $ref = ($dataReferencia ?? now())->copy()->startOfDay();
        $fim = $dataValidadeDocumento->copy()->startOfDay();

        return ! $fim->lt($ref);
    }

    /**
     * Quando true, a regra de negócio indica que a CNH válida dispensa a exigência de atestado
     * (orientação para checklist — decisão final continua com o operador).
     */
    public function cnhDispensaAtestadoMedico(?CarbonInterface $dataValidadeDocumento, ?CarbonInterface $dataReferencia = null): bool
    {
        return $this->cnhValidaNaReferencia($dataValidadeDocumento, $dataReferencia);
    }

    /**
     * Atestado ainda é necessário do ponto de vista da regra CNH (orientação).
     * Sem data de validade da CNH informada, não dispensa — operador deve conferir.
     */
    public function atestadoAindaNecessarioPorRegraCnh(?CarbonInterface $dataValidadeDocumento, ?CarbonInterface $dataReferencia = null): bool
    {
        return ! $this->cnhDispensaAtestadoMedico($dataValidadeDocumento, $dataReferencia);
    }

    /**
     * Mensagens para exibir ao operador (tom informativo / alerta).
     *
     * @param  CarbonInterface|null  $dataValidadeDocumento  Valor de `data_validade_documento` no item do checklist (ex.: fim da CNH).
     * @param  CarbonInterface|null  $dataReferencia  Opcional; padrão hoje. Não usar data de prova.
     * @return list<array{nivel: string, texto: string}>
     */
    public function orientacoesParaChecklist(?string $codigoDocumentoTipo, ?CarbonInterface $dataValidadeDocumento, ?CarbonInterface $dataReferencia = null): array
    {
        $codigo = $codigoDocumentoTipo ? strtoupper($codigoDocumentoTipo) : '';
        $linhas = [];

        if ($codigo === 'CNH'
            || $codigo === 'CHA_CNH_OU_ATESTADO'
            || $codigo === ChaChecklistDocumentoCodigos::CNH_COM_VALIDADE
            || $codigo === 'CHA_CNH_OU_RG') {
            $linhas[] = [
                'nivel' => 'info',
                'texto' => 'Há dispensa automática de atestado quando existe CNH no processo ou na ficha do cliente (cópia anexada).',
            ];

            return $linhas;
        }

        if ($codigo === 'ATESTADO_MEDICO' || $codigo === ChaChecklistDocumentoCodigos::ATESTADO_MEDICO_PSICOFISICO) {
            $linhas[] = [
                'nivel' => 'info',
                'texto' => 'Atestado médico/psicofísico: verifique validade (menos de 1 ano) e o tipo de processo. A dispensa automática ocorre quando há CNH anexada no processo ou na ficha do cliente.',
            ];
        }

        return $linhas;
    }
}
