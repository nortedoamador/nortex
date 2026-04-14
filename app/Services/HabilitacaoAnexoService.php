<?php

namespace App\Services;

use App\Enums\AnexoValidacaoStatus;
use App\Jobs\ValidarAnexoUploadJob;
use App\Models\Habilitacao;
use App\Models\HabilitacaoAnexo;
use App\Models\PlatformAnexoTipo;
use App\Support\EncryptedS3AnexoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HabilitacaoAnexoService
{
    public function armazenarVarios(Habilitacao $habilitacao, array $arquivos, ?string $tipoCodigo = null, ?int $platformAnexoTipoId = null): int
    {
        $empresaId = (int) $habilitacao->empresa_id;
        $count = 0;

        $platformTipo = null;
        if ($platformAnexoTipoId) {
            $platformTipo = PlatformAnexoTipo::query()->where('ativo', true)->find($platformAnexoTipoId);
        }

        foreach ($arquivos as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            if ($platformTipo && ! $this->arquivoAceitoPeloTipo($file, $platformTipo)) {
                continue;
            }

            $dir = "habilitacoes/{$empresaId}/{$habilitacao->id}";
            $path = EncryptedS3AnexoStorage::storeEncryptedUpload($file, $dir);

            $anexo = HabilitacaoAnexo::withoutGlobalScopes()->create([
                'habilitacao_id' => $habilitacao->id,
                'tipo_codigo' => $tipoCodigo,
                'platform_anexo_tipo_id' => $platformTipo?->id,
                'disk' => EncryptedS3AnexoStorage::DISK,
                'path' => $path,
                'nome_original' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'tamanho' => $file->getSize(),
                'extra_validation_status' => AnexoValidacaoStatus::Pendente,
            ]);

            ValidarAnexoUploadJob::dispatch(HabilitacaoAnexo::class, $anexo->id);
            $count++;
        }

        return $count;
    }

    public function remover(HabilitacaoAnexo $anexo): void
    {
        Storage::disk($anexo->disk)->delete($anexo->path);
        $anexo->delete();
    }

    private function arquivoAceitoPeloTipo(UploadedFile $file, PlatformAnexoTipo $tipo): bool
    {
        $maxBytes = max(1, (int) $tipo->max_size_mb) * 1024 * 1024;
        if ($file->getSize() !== false && $file->getSize() > $maxBytes) {
            return false;
        }

        $mime = (string) ($file->getClientMimeType() ?? '');
        if (is_array($tipo->allowed_mime_types) && $tipo->allowed_mime_types !== []) {
            if ($mime === '' || ! in_array($mime, $tipo->allowed_mime_types, true)) {
                return false;
            }
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (is_array($tipo->allowed_extensions) && $tipo->allowed_extensions !== []) {
            if ($ext === '' || ! in_array($ext, $tipo->allowed_extensions, true)) {
                return false;
            }
        }

        return true;
    }
}
