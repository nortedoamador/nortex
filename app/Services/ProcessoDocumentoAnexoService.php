<?php

namespace App\Services;

use App\Enums\AnexoValidacaoStatus;
use App\Support\ChecklistDocumentoModelo;
use App\Support\Normam211DocumentoCodigos;
use App\Enums\ProcessoDocumentoStatus;
use App\Jobs\ValidarAnexoUploadJob;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoDocumentoAnexo;
use App\Support\EncryptedS3AnexoStorage;
use App\Support\UploadRasterCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Armazena anexos cifrados no disco s3 e marca o item do checklist como enviado quando aplicável.
 */
class ProcessoDocumentoAnexoService
{
    public function armazenarVarios(Processo $processo, ProcessoDocumento $documento, array $arquivos): int
    {
        $count = 0;

        foreach ($arquivos as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $this->armazenarUm($processo, $documento, $file);
            $count++;
        }

        $documento->refresh();

        if ($count > 0) {
            $documento->refresh();
            $this->aplicarEstadoAposNovosAnexos($documento);
        }

        return $count;
    }

    /**
     * Regista um ficheiro no checklist a partir de conteúdo já lido em claro (ex.: cópia da ficha do cliente).
     */
    public function armazenarConteudoPlainCifrado(
        Processo $processo,
        ProcessoDocumento $documento,
        string $plainContents,
        string $nomeOriginal,
        string $mime,
    ): void {
        $empresaId = (int) $processo->empresa_id;
        $dir = "processos/{$empresaId}/{$processo->id}/{$documento->id}";
        $path = EncryptedS3AnexoStorage::storeEncryptedPlainContents($dir, $plainContents);

        $anexo = ProcessoDocumentoAnexo::withoutGlobalScopes()->create([
            'processo_documento_id' => $documento->id,
            'disk' => EncryptedS3AnexoStorage::DISK,
            'path' => $path,
            'nome_original' => $nomeOriginal,
            'mime' => $mime,
            'tamanho' => strlen($plainContents),
            'extra_validation_status' => AnexoValidacaoStatus::Pendente,
        ]);

        ValidarAnexoUploadJob::dispatch(ProcessoDocumentoAnexo::class, $anexo->id);

        $documento->refresh();
        $this->aplicarEstadoAposNovosAnexos($documento);
    }

    private function aplicarEstadoAposNovosAnexos(ProcessoDocumento $documento): void
    {
        $documento->loadMissing('documentoTipo');
        $codigo = (string) ($documento->documentoTipo?->codigo ?? '');
        $data = [
            'satisfeito_via_ficha_embarcacao' => false,
        ];
        if ($codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP) {
            $data['declaracao_residencia_2g'] = false;
        }
        if (Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigo)) {
            $data['declaracao_anexo_5h'] = false;
        }
        if (Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)) {
            $data['declaracao_anexo_5d'] = false;
        }
        if (Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo)) {
            $data['declaracao_anexo_3d'] = false;
        }
        if (ChecklistDocumentoModelo::tipoTemModelo($documento->documentoTipo)) {
            $data['preenchido_via_modelo'] = false;
        }
        if (in_array($documento->status, [
            ProcessoDocumentoStatus::Pendente,
            ProcessoDocumentoStatus::Fisico,
            ProcessoDocumentoStatus::Dispensado,
        ], true)) {
            $data['status'] = ProcessoDocumentoStatus::Enviado;
        }
        $documento->update($data);
    }

    public function armazenarUm(Processo $processo, ProcessoDocumento $documento, UploadedFile $file): ProcessoDocumentoAnexo
    {
        $empresaId = (int) $processo->empresa_id;
        $dir = "processos/{$empresaId}/{$processo->id}/{$documento->id}";

        $raw = $file->get();
        if ($raw === false) {
            throw new \RuntimeException(__('Falha ao ler o arquivo enviado.'));
        }

        $mimeIn = (string) ($file->getClientMimeType() ?: 'application/octet-stream');
        $compressed = UploadRasterCompressor::tryCompress($raw, $mimeIn);

        if ($compressed !== null) {
            [$bytes, $mimeOut] = $compressed;
            $path = EncryptedS3AnexoStorage::storeEncryptedPlainContents($dir, $bytes);
            $mime = $mimeOut;
            $tamanho = strlen($bytes);
        } else {
            $path = EncryptedS3AnexoStorage::storeEncryptedUpload($file, $dir);
            $mime = $mimeIn;
            $tamanho = $file->getSize();
        }

        $anexo = ProcessoDocumentoAnexo::withoutGlobalScopes()->create([
            'processo_documento_id' => $documento->id,
            'disk' => EncryptedS3AnexoStorage::DISK,
            'path' => $path,
            'nome_original' => $file->getClientOriginalName(),
            'mime' => $mime,
            'tamanho' => $tamanho,
            'extra_validation_status' => AnexoValidacaoStatus::Pendente,
        ]);

        ValidarAnexoUploadJob::dispatch(ProcessoDocumentoAnexo::class, $anexo->id);

        return $anexo;
    }

    public function remover(ProcessoDocumentoAnexo $anexo): void
    {
        $documentoId = (int) $anexo->processo_documento_id;
        Storage::disk($anexo->disk)->delete($anexo->path);
        $anexo->delete();

        $documento = ProcessoDocumento::query()->find($documentoId);
        if ($documento && $documento->anexos()->count() === 0) {
            $documento->update([
                'status' => ProcessoDocumentoStatus::Pendente,
                'declaracao_residencia_2g' => false,
                'declaracao_anexo_5h' => false,
                'declaracao_anexo_5d' => false,
                'declaracao_anexo_3d' => false,
                'preenchido_via_modelo' => false,
                'satisfeito_via_ficha_embarcacao' => false,
            ]);
        }
    }
}
