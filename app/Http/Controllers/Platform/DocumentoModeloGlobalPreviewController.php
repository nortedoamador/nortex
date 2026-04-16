<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoModeloGlobal;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Support\AulaNauticaAraPdfData;
use App\Support\DocumentoModeloTemplateAliases;
use App\Support\Normam211212TemplateVars;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentoModeloGlobalPreviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user && $user->is_platform_admin, 403);

        $validated = $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'slug' => ['required', 'string', 'max:80'],
            'embarcacao_id' => ['nullable', 'integer'],
            'format' => ['nullable', Rule::in(['html', 'pdf', 'doc', 'docx'])],
        ]);

        $slug = trim(mb_strtolower(preg_replace('/\s+/', '-', $validated['slug'])));
        $global = DocumentoModeloGlobal::query()->where('slug', $slug)->firstOrFail();

        $cliente = Cliente::query()
            ->where('empresa_id', $validated['empresa_id'])
            ->whereKey($validated['cliente_id'])
            ->firstOrFail();

        $embarcacao = null;
        if (! empty($validated['embarcacao_id'])) {
            $embarcacao = Embarcacao::query()
                ->where('empresa_id', $validated['empresa_id'])
                ->where('cliente_id', $cliente->id)
                ->whereKey((int) $validated['embarcacao_id'])
                ->firstOrFail();
        }

        $format = strtolower((string) $request->query('format', 'html'));
        $print = $request->boolean('print', false);

        $normam = Normam211212TemplateVars::variablesFor($cliente, $embarcacao);
        $empresa = Empresa::query()->find((int) $validated['empresa_id']);
        if ($empresa !== null) {
            $normam = array_merge($normam, DocumentoModeloTemplateAliases::paraEmpresaCliente($empresa, $cliente, $normam));
        }

        $clienteSlug = Str::slug((string) ($cliente->nome ?? ''), '_');
        $clienteSlug = $clienteSlug !== '' ? $clienteSlug : 'cliente';
        $baseName = $slug.'_'.$clienteSlug;

        $preamble = "@php\nextract(\$__documento_modelo_vars ?? [], EXTR_SKIP);\n@endphp\n";
        $hoje = now();
        $mergeData = array_merge($normam, [
            '__documento_modelo_vars' => $normam,
            'empresa' => $empresa,
            'cliente' => $cliente,
            'embarcacao' => $embarcacao,
            'hoje' => $hoje,
        ]);
        if ($slug === 'atestado-ara') {
            $mergeData['araPdf'] = AulaNauticaAraPdfData::buildPreviewForCliente(
                $cliente,
                (int) $validated['empresa_id'],
                $hoje
            );
        }
        $html = Blade::render($preamble.$global->conteudo, $mergeData);

        if ($format === 'pdf') {
            $opts = new Options();
            $opts->set('isRemoteEnabled', true);
            $opts->set('isHtml5ParserEnabled', true);
            $opts->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($opts);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();

            $pdf = $dompdf->output();

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$baseName.'.pdf"',
            ]);
        }

        if ($format === 'doc' || $format === 'docx') {
            $docHtml = '<!doctype html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>';

            return response($docHtml, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$baseName.'.doc"',
            ]);
        }

        if ($print) {
            $titulo = (string) $global->titulo;
            $html = "<!doctype html><html><head><meta charset=\"UTF-8\"><title>{$titulo}</title><style>@media print{body{margin:0}} </style></head><body>{$html}<script>window.addEventListener('load',()=>{try{window.focus();window.print();}catch(e){}});</script></body></html>";
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
