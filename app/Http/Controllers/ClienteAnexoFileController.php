<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteAnexo;
use App\Support\AnexoPrintHtml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClienteAnexoFileController extends Controller
{
    /**
     * Exibe o ficheiro no browser (nova aba / iframe) sem depender da URL pública /storage no Apache.
     */
    public function inline(Cliente $cliente, ClienteAnexo $anexo): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view', $cliente);

        if ((int) $anexo->cliente_id !== (int) $cliente->id) {
            abort(404);
        }

        abort_unless(Storage::disk($anexo->disk)->exists($anexo->path), 404);

        $absolute = Storage::disk($anexo->disk)->path($anexo->path);

        return response()->file($absolute, [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $anexo->nome_original,
                'document'
            ),
        ]);
    }

    public function download(Cliente $cliente, ClienteAnexo $anexo): StreamedResponse
    {
        $this->authorize('view', $cliente);

        if ((int) $anexo->cliente_id !== (int) $cliente->id) {
            abort(404);
        }

        return Storage::disk($anexo->disk)->download($anexo->path, $anexo->nome_original);
    }

    public function print(Cliente $cliente, ClienteAnexo $anexo): Response
    {
        $this->authorize('view', $cliente);

        if ((int) $anexo->cliente_id !== (int) $cliente->id) {
            abort(404);
        }

        return AnexoPrintHtml::response(
            route('clientes.anexos.inline', [$cliente, $anexo]),
            $anexo->nome_original
        );
    }
}

