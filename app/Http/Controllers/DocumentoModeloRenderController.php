<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Services\EmpresaProcessosDefaultsService;
use App\Support\DocumentoModeloSincroniaDiscoBd;
use App\Support\DocumentoModeloTemplateAliases;
use App\Support\Normam211212TemplateVars;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;
use Dompdf\Dompdf;
use Dompdf\Options;

class DocumentoModeloRenderController extends Controller
{
    public function render(Request $request, Cliente $cliente, string $slug): Response
    {
        $user = auth()->user();
        abort_unless((int) $cliente->empresa_id === (int) $user->empresa_id, 404);

        $empresaId = (int) $user->empresa_id;
        $modelo = DocumentoModelo::query()
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->first();

        if ($modelo === null && $empresaId > 0) {
            $empresa = Empresa::query()->find($empresaId);
            if ($empresa) {
                app(EmpresaProcessosDefaultsService::class)->garantirModeloPdfPadraoPorSlug($empresa, $slug);
                $modelo = DocumentoModelo::query()
                    ->where('empresa_id', $empresaId)
                    ->where('slug', $slug)
                    ->first();
            }
        }

        abort_if($modelo === null, 404);

        DocumentoModeloSincroniaDiscoBd::aplicar($modelo);

        $embarcacao = null;
        // `contexto_id`: embarcação do cliente usada para pré-preencher modelos (ex.: Anexo 5-H / CHA).
        // Legado: `embarcacao_id` (ainda aceito para links antigos).
        $embId = $request->query('contexto_id') ?? $request->query('embarcacao_id');
        if ($embId !== null && $embId !== '' && ctype_digit((string) $embId)) {
            $embarcacao = Embarcacao::query()
                ->where('empresa_id', $user->empresa_id)
                ->where('cliente_id', $cliente->id)
                ->find((int) $embId);
        }

        $format = strtolower((string) $request->query('format', 'html'));
        $print = $request->boolean('print', false);

        $normam = Normam211212TemplateVars::variablesFor($cliente, $embarcacao);
        $empresa = Empresa::query()->find($cliente->empresa_id);
        if ($empresa !== null) {
            $normam = array_merge($normam, DocumentoModeloTemplateAliases::paraEmpresaCliente($empresa, $cliente, $normam));
        }

        $clienteSlug = Str::slug((string) ($cliente->nome ?? ''), '_');
        $clienteSlug = $clienteSlug !== '' ? $clienteSlug : 'cliente';
        $baseName = $slug.'_'.$clienteSlug;

        // Preâmbulo no mesmo ficheiro compilado que o SVG: garante $nome, $cpf, … neste escopo
        // (evita falhas quando o extract inicial do motor não alinha com o template em string).
        $preamble = "@php\nextract(\$__documento_modelo_vars ?? [], EXTR_SKIP);\n@endphp\n";
        $html = Blade::render($preamble.$modelo->conteudo, array_merge($normam, [
            '__documento_modelo_vars' => $normam,
            'empresa' => $empresa,
            'cliente' => $cliente,
            'embarcacao' => $embarcacao,
            'hoje' => now(),
        ]));

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
            $html = "<!doctype html><html><head><meta charset=\"UTF-8\"><title>{$modelo->titulo}</title><style>@media print{body{margin:0}} </style></head><body>{$html}<script>window.addEventListener('load',()=>{try{window.focus();window.print();}catch(e){}});</script></body></html>";
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }
}
