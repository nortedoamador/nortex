<?php

namespace App\Http\Controllers;

use App\Models\EmbarcacaoAnexo;
use App\Support\AnexoPrintHtml;
use App\Support\EncryptedS3AnexoStorage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmbarcacaoAnexoFileController extends Controller
{
    public function inline(EmbarcacaoAnexo $anexo): SymfonyResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $embarcacao = $anexo->embarcacao;
        abort_unless($embarcacao, 404);

        $this->authorize('view', $embarcacao);

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

        $absolute = Storage::disk($anexo->disk)->path($anexo->path);

        return response()->file($absolute, [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $anexo->nome_original,
                'document'
            ),
        ]);
    }

    public function download(EmbarcacaoAnexo $anexo): StreamedResponse
    {
        $embarcacao = $anexo->embarcacao;
        abort_unless($embarcacao, 404);

        $this->authorize('view', $embarcacao);

        abort_unless(EncryptedS3AnexoStorage::exists($anexo->disk, $anexo->path), 404);

        if (EncryptedS3AnexoStorage::isEncryptedDisk($anexo->disk)) {
            return response()->streamDownload(function () use ($anexo) {
                echo EncryptedS3AnexoStorage::readPlain($anexo->disk, $anexo->path);
            }, $anexo->nome_original, [
                'Content-Type' => $anexo->mime ?: 'application/octet-stream',
            ]);
        }

        return Storage::disk($anexo->disk)->download($anexo->path, $anexo->nome_original);
    }

    public function print(EmbarcacaoAnexo $anexo): Response
    {
        $embarcacao = $anexo->embarcacao;
        abort_unless($embarcacao, 404);

        $this->authorize('view', $embarcacao);

        return AnexoPrintHtml::response(
            $anexo->signedInlineUrl(),
            $anexo->nome_original
        );
    }
}
