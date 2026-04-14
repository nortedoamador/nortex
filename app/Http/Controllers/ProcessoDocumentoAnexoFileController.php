<?php

namespace App\Http\Controllers;

use App\Models\ProcessoDocumentoAnexo;
use App\Support\EncryptedS3AnexoStorage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProcessoDocumentoAnexoFileController extends Controller
{
    public function inline(ProcessoDocumentoAnexo $anexo): SymfonyResponse|BinaryFileResponse
    {
        $anexo->loadMissing('processoDocumento.processo');
        $documento = $anexo->processoDocumento;
        $processo = $documento?->processo;
        abort_unless($documento && $processo, 404);

        $this->authorize('view', $processo);

        abort_unless(EncryptedS3AnexoStorage::exists($anexo->disk, $anexo->path), 404);

        if (EncryptedS3AnexoStorage::isEncryptedDisk($anexo->disk)) {
            $plain = EncryptedS3AnexoStorage::readPlain($anexo->disk, $anexo->path);

            return response($plain, 200, [
                'Content-Type' => $anexo->mime ?: 'application/octet-stream',
                'Content-Disposition' => HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_INLINE,
                    $anexo->nome_original,
                    'document'
                ),
            ]);
        }

        return response()->file(Storage::disk($anexo->disk)->path($anexo->path), [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $anexo->nome_original,
                'document'
            ),
        ]);
    }
}
