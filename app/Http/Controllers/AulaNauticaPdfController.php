<?php

namespace App\Http\Controllers;

use App\Models\AulaNautica;
use App\Models\EmpresaAtestadoNormamDuracao;
use App\Models\EscolaNautica;
use App\Support\AulaCurriculoNormam;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class AulaNauticaPdfController extends Controller
{
    public function comunicado(Request $request, AulaNautica $aula): Response
    {
        $this->assertEmpresa($request, $aula);
        $aula->load(['alunos', 'instrutores']);

        return $this->renderPdf('aulas.pdf.comunicado', ['aula' => $aula], 'comunicado_de_aula');
    }

    public function ara(Request $request, AulaNautica $aula): Response
    {
        $this->assertEmpresa($request, $aula);
        $aula->load(['alunos', 'escolaInstrutores.cliente']);

        $escola = EscolaNautica::query()
            ->where('empresa_id', $aula->empresa_id)
            ->with('diretor')
            ->first();

        $duracoesMap = EmpresaAtestadoNormamDuracao::query()
            ->where('programa', AulaCurriculoNormam::PROGRAMA_ARA)
            ->get()
            ->keyBy('item_key');

        $curriculoAra = AulaCurriculoNormam::itensAra();

        return $this->renderPdf('aulas.pdf.ara', [
            'aula' => $aula,
            'escola' => $escola,
            'duracoesMap' => $duracoesMap,
            'curriculoAra' => $curriculoAra,
        ], 'atestado_ara');
    }

    public function mta(Request $request, AulaNautica $aula): Response
    {
        $this->assertEmpresa($request, $aula);
        $aula->load(['alunos', 'instrutores']);

        return $this->renderPdf('aulas.pdf.mta', ['aula' => $aula], 'atestado_mta');
    }

    private function assertEmpresa(Request $request, AulaNautica $aula): void
    {
        abort_unless((int) $aula->empresa_id === (int) $request->user()->empresa_id, 404);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderPdf(string $view, array $data, string $baseFileName): Response
    {
        $aula = $data['aula'] ?? null;
        if (! $aula instanceof AulaNautica) {
            throw new \InvalidArgumentException('renderPdf espera chave "aula" com AulaNautica.');
        }

        $html = Blade::render(view($view, $data)->render(), $data);

        $opts = new Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('isHtml5ParserEnabled', true);
        $opts->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $pdf = $dompdf->output();

        $suffix = Str::slug((string) $aula->numero_oficio, '_');
        $suffix = $suffix !== '' ? $suffix : (string) $aula->id;
        $fileName = $baseFileName.'_'.$suffix.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}

