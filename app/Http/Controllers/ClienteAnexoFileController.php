<?php

namespace App\Http\Controllers;

use App\Models\ClienteAnexo;
use App\Support\AnexoPrintHtml;
use App\Support\ClienteAnexoStorage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClienteAnexoFileController extends Controller
{
    /**
     * Exibe o ficheiro no browser (nova aba / iframe) sem depender da URL pública /storage no Apache.
     */
    public function inline(ClienteAnexo $anexo): SymfonyResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $cliente = $anexo->cliente;
        abort_unless($cliente, 404);

        $this->authorize('view', $cliente);

        abort_unless(ClienteAnexoStorage::exists($anexo), 404);

        if ($anexo->disk === ClienteAnexoStorage::DISK) {
            $plain = ClienteAnexoStorage::readPlainContents($anexo);

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

    public function download(ClienteAnexo $anexo): StreamedResponse
    {
        $cliente = $anexo->cliente;
        abort_unless($cliente, 404);

        $this->authorize('view', $cliente);

        abort_unless(ClienteAnexoStorage::exists($anexo), 404);

        if ($anexo->disk === ClienteAnexoStorage::DISK) {
            return response()->streamDownload(function () use ($anexo) {
                echo ClienteAnexoStorage::readPlainContents($anexo);
            }, $anexo->nome_original, [
                'Content-Type' => $anexo->mime ?: 'application/octet-stream',
            ]);
        }

        return Storage::disk($anexo->disk)->download($anexo->path, $anexo->nome_original);
    }

    public function print(ClienteAnexo $anexo): Response
    {
        $cliente = $anexo->cliente;
        abort_unless($cliente, 404);

        $this->authorize('view', $cliente);

        return AnexoPrintHtml::response(
            $anexo->signedInlineUrl(),
            $anexo->nome_original
        );
    }
}

