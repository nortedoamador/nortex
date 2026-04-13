<?php

namespace App\Http\Controllers;

use App\Models\Habilitacao;
use App\Models\HabilitacaoAnexo;
use App\Support\AnexoPrintHtml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HabilitacaoAnexoFileController extends Controller
{
    public function inline(Habilitacao $habilitacao, HabilitacaoAnexo $anexo): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('view', $habilitacao);

        if ((int) $anexo->habilitacao_id !== (int) $habilitacao->id) {
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

    public function download(Habilitacao $habilitacao, HabilitacaoAnexo $anexo): StreamedResponse
    {
        $this->authorize('view', $habilitacao);

        if ((int) $anexo->habilitacao_id !== (int) $habilitacao->id) {
            abort(404);
        }

        return Storage::disk($anexo->disk)->download($anexo->path, $anexo->nome_original);
    }

    public function print(Habilitacao $habilitacao, HabilitacaoAnexo $anexo): Response
    {
        $this->authorize('view', $habilitacao);

        if ((int) $anexo->habilitacao_id !== (int) $habilitacao->id) {
            abort(404);
        }

        return AnexoPrintHtml::response(
            route('habilitacoes.anexos.inline', [$habilitacao, $anexo]),
            $anexo->nome_original
        );
    }
}
