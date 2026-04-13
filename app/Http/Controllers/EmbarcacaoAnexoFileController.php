<?php

namespace App\Http\Controllers;

use App\Models\Embarcacao;
use App\Models\EmbarcacaoAnexo;
use App\Support\AnexoPrintHtml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmbarcacaoAnexoFileController extends Controller
{
    public function inline(Embarcacao $embarcacao, EmbarcacaoAnexo $anexo): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view', $embarcacao);

        if ((int) $anexo->embarcacao_id !== (int) $embarcacao->id) {
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

    public function download(Embarcacao $embarcacao, EmbarcacaoAnexo $anexo): StreamedResponse
    {
        $this->authorize('view', $embarcacao);

        if ((int) $anexo->embarcacao_id !== (int) $embarcacao->id) {
            abort(404);
        }

        return Storage::disk($anexo->disk)->download($anexo->path, $anexo->nome_original);
    }

    public function print(Embarcacao $embarcacao, EmbarcacaoAnexo $anexo): Response
    {
        $this->authorize('view', $embarcacao);

        if ((int) $anexo->embarcacao_id !== (int) $embarcacao->id) {
            abort(404);
        }

        return AnexoPrintHtml::response(
            route('embarcacoes.anexos.inline', [$embarcacao, $anexo]),
            $anexo->nome_original
        );
    }
}
