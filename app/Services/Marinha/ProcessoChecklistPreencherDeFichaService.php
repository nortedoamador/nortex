<?php

namespace App\Services\Marinha;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\ClienteAnexo;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\HabilitacaoAnexo;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Services\ProcessoDocumentoAnexoService;
use App\Support\ChaChecklistDocumentoCodigos;
use App\Support\ChecklistDocumentoModelo;
use App\Support\ChecklistDocumentoMultiplosAnexos;
use App\Support\ClienteAnexoStorage;
use App\Support\ClienteTiposAnexo;
use App\Support\EmbarcacaoTiposAnexo;
use App\Support\EncryptedS3AnexoStorage;
use App\Support\HabilitacaoAnexoTiposCha;
use App\Support\Normam211DocumentoCodigos;

/**
 * Preenche itens do checklist a partir de anexos já guardados na ficha do cliente, na habilitação CHA ou na embarcação.
 */
final class ProcessoChecklistPreencherDeFichaService
{
    public function __construct(
        private ProcessoDocumentoAnexoService $anexoProcesso,
    ) {}

    public function sync(Processo $processo): void
    {
        $processo->loadMissing([
            'cliente.anexos',
            'cliente.habilitacoes.anexos',
            'embarcacao.anexos',
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
        ]);

        foreach ($processo->documentosChecklist as $doc) {
            $this->sincronizarLinha($processo, $doc);
        }
    }

    private function sincronizarLinha(Processo $processo, ProcessoDocumento $doc): void
    {
        $codigo = (string) ($doc->documentoTipo?->codigo ?? '');

        if (ChecklistDocumentoMultiplosAnexos::permite($codigo)) {
            $this->sincronizarFotosEmbarcacao($processo, $doc);

            return;
        }

        if ($doc->status === ProcessoDocumentoStatus::Dispensado) {
            return;
        }

        $this->marcarProcuracaoComModeloSeAplicavel($doc);

        if (ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($doc) && $doc->anexos->isEmpty()) {
            return;
        }

        if ($doc->status !== ProcessoDocumentoStatus::Pendente || $doc->anexos->isNotEmpty()) {
            return;
        }

        if ($this->codigoUsaCnhFicha($codigo)) {
            $this->copiarClienteAnexoTipo($processo, $doc, ClienteTiposAnexo::CNH);

            return;
        }

        if ($this->codigoUsaComprovanteEnderecoFicha($codigo)) {
            $this->copiarClienteAnexoTipo($processo, $doc, ClienteTiposAnexo::COMPROVANTE_ENDERECO);

            return;
        }

        if ($this->codigoUsaChaHabilitacao($codigo)) {
            $this->copiarChaHabilitacao($processo, $doc);
        }
    }

    /**
     * Itens de procuração passam a contar com o PDF do modelo (slug «procuracao») sem upload manual.
     */
    private function marcarProcuracaoComModeloSeAplicavel(ProcessoDocumento $doc): void
    {
        $codigo = (string) ($doc->documentoTipo?->codigo ?? '');
        if (! in_array($codigo, ['CIR_PROCURACAO', 'CHA_PROCURACAO', 'TIE_PROCURACAO'], true)) {
            return;
        }
        if (! ChecklistDocumentoModelo::tipoTemModelo($doc->documentoTipo)) {
            return;
        }
        if ((bool) ($doc->preenchido_via_modelo ?? false)) {
            return;
        }
        if ($doc->anexos->isNotEmpty()) {
            return;
        }
        if ($doc->status === ProcessoDocumentoStatus::Dispensado) {
            return;
        }

        $doc->forceFill([
            'preenchido_via_modelo' => true,
            'status' => ProcessoDocumentoStatus::Enviado,
        ])->save();
    }

    private function sincronizarFotosEmbarcacao(Processo $processo, ProcessoDocumento $doc): void
    {
        if ($doc->status === ProcessoDocumentoStatus::Fisico
            || $doc->status === ProcessoDocumentoStatus::Dispensado) {
            return;
        }

        $emb = $processo->embarcacao;
        if (! $emb instanceof Embarcacao) {
            if ((bool) ($doc->satisfeito_via_ficha_embarcacao ?? false)) {
                $doc->update([
                    'satisfeito_via_ficha_embarcacao' => false,
                    'status' => ProcessoDocumentoStatus::Pendente,
                ]);
            }

            return;
        }

        $emb->loadMissing('anexos');
        $temFotos = $this->embarcacaoTemFotoTravesEPopa($emb);

        if (! $temFotos && (bool) ($doc->satisfeito_via_ficha_embarcacao ?? false) && $doc->anexos->isEmpty()) {
            $doc->update([
                'satisfeito_via_ficha_embarcacao' => false,
                'status' => ProcessoDocumentoStatus::Pendente,
            ]);

            return;
        }

        if ($temFotos && $doc->anexos->isEmpty()
            && ($doc->status === ProcessoDocumentoStatus::Pendente
                || (bool) ($doc->satisfeito_via_ficha_embarcacao ?? false))) {
            $doc->update([
                'status' => ProcessoDocumentoStatus::Enviado,
                'satisfeito_via_ficha_embarcacao' => true,
            ]);
        }
    }

    private function embarcacaoTemFotoTravesEPopa(Embarcacao $embarcacao): bool
    {
        $tipos = $embarcacao->anexos->pluck('tipo_codigo')->map(fn ($t) => (string) $t)->all();

        return in_array(EmbarcacaoTiposAnexo::FOTO_TRAVES, $tipos, true)
            && in_array(EmbarcacaoTiposAnexo::FOTO_POPA, $tipos, true);
    }

    private function codigoUsaCnhFicha(string $codigo): bool
    {
        return ChaChecklistDocumentoCodigos::isCnhComValidade($codigo)
            || $codigo === 'CHA_CNH_OU_RG';
    }

    private function codigoUsaComprovanteEnderecoFicha(string $codigo): bool
    {
        return $codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
            || $codigo === Normam211DocumentoCodigos::CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY;
    }

    private function codigoUsaChaHabilitacao(string $codigo): bool
    {
        return $codigo === 'CHA_CARTEIRA_EXISTENTE'
            || $codigo === 'CHA_OU_DECL_EXTRAVIO_5D';
    }

    private function resolverHabilitacaoCha(Processo $processo): ?Habilitacao
    {
        if ($processo->habilitacao_id && $processo->cliente_id) {
            $h = Habilitacao::query()
                ->where('cliente_id', $processo->cliente_id)
                ->where('id', $processo->habilitacao_id)
                ->first();
            if ($h instanceof Habilitacao) {
                return $h;
            }
        }

        return $processo->cliente?->habilitacoes->sortByDesc('id')->first();
    }

    private function copiarClienteAnexoTipo(Processo $processo, ProcessoDocumento $doc, string $tipoCodigo): void
    {
        $cliente = $processo->cliente;
        if ($cliente === null) {
            return;
        }

        $fonte = $cliente->anexos
            ->filter(fn (ClienteAnexo $a) => (string) ($a->tipo_codigo ?? '') === $tipoCodigo)
            ->sortByDesc('id')
            ->first();
        if (! $fonte instanceof ClienteAnexo) {
            return;
        }

        if (! ClienteAnexoStorage::exists($fonte)) {
            return;
        }

        $plain = ClienteAnexoStorage::readPlainContents($fonte);
        $this->anexoProcesso->armazenarConteudoPlainCifrado(
            $processo,
            $doc,
            $plain,
            (string) $fonte->nome_original,
            (string) ($fonte->mime ?? 'application/octet-stream'),
        );
    }

    private function copiarChaHabilitacao(Processo $processo, ProcessoDocumento $doc): void
    {
        $h = $this->resolverHabilitacaoCha($processo);
        if (! $h instanceof Habilitacao) {
            return;
        }

        $h->loadMissing('anexos');
        $fonte = $h->anexos
            ->filter(fn (HabilitacaoAnexo $a) => in_array((string) ($a->tipo_codigo ?? ''), HabilitacaoAnexoTiposCha::codigos(), true))
            ->sortByDesc('id')
            ->first();
        if (! $fonte instanceof HabilitacaoAnexo) {
            return;
        }

        if (! EncryptedS3AnexoStorage::exists($fonte->disk, $fonte->path)) {
            return;
        }

        $plain = EncryptedS3AnexoStorage::readPlain($fonte->disk, $fonte->path);
        $this->anexoProcesso->armazenarConteudoPlainCifrado(
            $processo,
            $doc,
            $plain,
            (string) $fonte->nome_original,
            (string) ($fonte->mime ?? 'application/octet-stream'),
        );
    }
}
