<?php

namespace App\Http\Controllers;

use App\Models\AulaNautica;
use App\Models\Cliente;
use App\Models\EmpresaAtestadoNormamDuracao;
use App\Support\AulaCurriculoNormam;
use App\Support\AulaNauticaAraPdfData;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class AulaNauticaPdfController extends Controller
{
    public function comunicado(Request $request, AulaNautica $aula): Response
    {
        $this->assertEmpresa($request, $aula);
        $aula->load(['alunos', 'instrutores']);

        return $this->renderPdf('aulas.pdf.comunicado', $aula, 'comunicado_de_aula');
    }

    public function ara(Request $request, AulaNautica $aula, Cliente $aluno): Response
    {
        $this->assertEmpresa($request, $aula);
        abort_unless(
            $aula->alunos()->whereKey($aluno->getKey())->exists(),
            404
        );

        $aula->load([
            'empresa',
            'escolaInstrutores.cliente',
            'instrutores',
        ]);

        $duracoes = EmpresaAtestadoNormamDuracao::query()
            ->where('empresa_id', $aula->empresa_id)
            ->where('programa', AulaCurriculoNormam::PROGRAMA_ARA)
            ->get()
            ->keyBy('item_key');

        $araPdf = AulaNauticaAraPdfData::build($aula, $aluno, $duracoes);

        return $this->renderPdf(
            'aulas.pdf.ara',
            $aula,
            'atestado_ara',
            [
                'aluno' => $aluno,
                'araPdf' => $araPdf,
            ]
        );
    }

    public function mta(Request $request, AulaNautica $aula): Response
    {
        $this->assertEmpresa($request, $aula);
        $aula->load(['alunos', 'instrutores']);

        return $this->renderPdf('aulas.pdf.mta', $aula, 'atestado_mta');
    }

    private function assertEmpresa(Request $request, AulaNautica $aula): void
    {
        abort_unless((int) $aula->empresa_id === (int) $request->user()->empresa_id, 404);
    }

    /**
     * @param  array<string, mixed>  $extraViewData
     */
    private function renderPdf(string $view, AulaNautica $aula, string $baseFileName, array $extraViewData = []): Response
    {
        $html = view($view, array_merge(compact('aula'), $extraViewData))->render();

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
        $fileName = $baseFileName.'_'.$suffix;
        if (isset($extraViewData['aluno']) && $extraViewData['aluno'] instanceof Cliente) {
            /** @var Cliente $aluno */
            $aluno = $extraViewData['aluno'];
            $fileName .= '_'.preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) $aluno->getRouteKey());
        }
        $fileName .= '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
