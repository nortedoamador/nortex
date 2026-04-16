<?php

namespace App\Services;

use App\Models\AulaNautica;
use App\Models\DocumentoModelo;
use App\Models\Empresa;
use App\Models\EmpresaAtestadoNormamDuracao;
use App\Support\AulaCurriculoNormam;
use App\Support\AulaEscolaInstrutorProgramaAtestado;
use App\Support\AulaNauticaAraPdfData;
use App\Support\DocumentoModeloSincroniaDiscoBd;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AulaNauticaDocumentosAutomaticosService
{
    public function __construct(
        private readonly EmpresaProcessosDefaultsService $empresaProcessosDefaults,
    ) {}

    public const SLUG_COMUNICADO = 'comunicado-de-aula';

    public const SLUG_ANEXO_5_E_211 = 'atestado-de-treinamento-nautico-de-motonauta-anexo-5-e-da-normam-211';

    public const SLUG_ANEXO_3_B_212 = 'atestado-de-treinamento-nautico-de-motonauta-anexo-3-b-da-normam-212';

    public function gerar(AulaNautica $aula): void
    {
        $empresa = $aula->empresa ?? Empresa::query()->find($aula->empresa_id);
        if ($empresa === null) {
            return;
        }

        foreach ([self::SLUG_COMUNICADO, self::SLUG_ANEXO_5_E_211, self::SLUG_ANEXO_3_B_212] as $slug) {
            $this->empresaProcessosDefaults->garantirModeloPdfPadraoPorSlug($empresa, $slug);
        }

        $aula->loadMissing(['alunos', 'instrutores', 'escolaInstrutores.cliente', 'empresa']);

        $entries = [];
        $entries = array_merge($entries, $this->gerarComunicado($aula));
        $entries = array_merge($entries, $this->gerarMta($aula));
        $entries = array_merge($entries, $this->gerarAraPorAluno($aula));

        $aula->forceFill(['documentos_automaticos' => $entries])->save();
    }

    private function modeloDocumento(int $empresaId, string $slug): ?DocumentoModelo
    {
        $modelo = DocumentoModelo::query()
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->first();

        if ($modelo === null) {
            return null;
        }

        DocumentoModeloSincroniaDiscoBd::aplicar($modelo);
        $modelo->refresh();

        return $modelo;
    }

    /**
     * @return list<array{slug: string, titulo: string, path: string, filename: string, cliente_id: int|null}>
     */
    private function gerarComunicado(AulaNautica $aula): array
    {
        $modelo = $this->modeloDocumento((int) $aula->empresa_id, self::SLUG_COMUNICADO);
        if ($modelo === null) {
            return [];
        }

        $vars = $this->variaveisBase($aula);
        $pdf = $this->renderModeloParaPdf($modelo, $vars);
        if ($pdf === null) {
            return [];
        }

        $path = $this->caminhoStorage($aula, 'comunicado-de-aula.pdf');
        Storage::disk('local')->put($path, $pdf);

        return [[
            'slug' => self::SLUG_COMUNICADO,
            'titulo' => (string) $modelo->titulo,
            'path' => $path,
            'filename' => $this->nomeFicheiroDownload($aula, 'comunicado-de-aula.pdf'),
            'cliente_id' => null,
        ]];
    }

    /**
     * @return list<array{slug: string, titulo: string, path: string, filename: string, cliente_id: int|null}>
     */
    private function gerarMta(AulaNautica $aula): array
    {
        $modelo = $this->modeloDocumento((int) $aula->empresa_id, self::SLUG_ANEXO_3_B_212);
        if ($modelo === null) {
            return [];
        }

        $instrutorEscolaMta = $aula->escolaInstrutores->first(
            fn ($row) => AulaEscolaInstrutorProgramaAtestado::apareceNoMta($row->pivot->programa_atestado ?? null)
        );

        $vars = array_merge($this->variaveisBase($aula), [
            'instrutorEscolaMta' => $instrutorEscolaMta,
        ]);

        $pdf = $this->renderModeloParaPdf($modelo, $vars);
        if ($pdf === null) {
            return [];
        }

        $path = $this->caminhoStorage($aula, 'atestado-motonauta-anexo-3-b-normam-212.pdf');
        Storage::disk('local')->put($path, $pdf);

        return [[
            'slug' => self::SLUG_ANEXO_3_B_212,
            'titulo' => (string) $modelo->titulo,
            'path' => $path,
            'filename' => $this->nomeFicheiroDownload($aula, 'atestado-motonauta-normam-212.pdf'),
            'cliente_id' => null,
        ]];
    }

    /**
     * @return list<array{slug: string, titulo: string, path: string, filename: string, cliente_id: int|null}>
     */
    private function gerarAraPorAluno(AulaNautica $aula): array
    {
        $modelo = $this->modeloDocumento((int) $aula->empresa_id, self::SLUG_ANEXO_5_E_211);
        if ($modelo === null) {
            return [];
        }

        $duracoes = EmpresaAtestadoNormamDuracao::query()
            ->where('empresa_id', $aula->empresa_id)
            ->where('programa', AulaCurriculoNormam::PROGRAMA_ARA)
            ->get()
            ->keyBy('item_key');

        $out = [];
        foreach ($aula->alunos as $aluno) {
            $araPdf = AulaNauticaAraPdfData::build($aula, $aluno, $duracoes);

            $vars = array_merge($this->variaveisBase($aula), [
                'aluno' => $aluno,
                'araPdf' => $araPdf,
            ]);

            $pdf = $this->renderModeloParaPdf($modelo, $vars);
            if ($pdf === null) {
                continue;
            }

            $safeKey = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) $aluno->getRouteKey());
            $safeKey = $safeKey !== '' ? $safeKey : (string) $aluno->id;
            $path = $this->caminhoStorage($aula, 'atestado-normam-211-anexo-5-e-'.$safeKey.'.pdf');
            Storage::disk('local')->put($path, $pdf);

            $out[] = [
                'slug' => self::SLUG_ANEXO_5_E_211,
                'titulo' => (string) $modelo->titulo.' — '.$aluno->nome,
                'path' => $path,
                'filename' => $this->nomeFicheiroDownload($aula, 'anexo-5-e-'.$safeKey.'.pdf'),
                'cliente_id' => (int) $aluno->id,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function variaveisBase(AulaNautica $aula): array
    {
        $empresa = $aula->empresa ?? Empresa::query()->find($aula->empresa_id);

        return [
            'aula' => $aula,
            'empresa' => $empresa,
        ];
    }

    private function caminhoStorage(AulaNautica $aula, string $fileName): string
    {
        return 'empresas/'.$aula->empresa_id.'/aulas_nauticas/'.$aula->id.'/'.$fileName;
    }

    private function nomeFicheiroDownload(AulaNautica $aula, string $suffix): string
    {
        $base = Str::slug((string) $aula->numero_oficio, '_');
        $base = $base !== '' ? $base : 'aula_'.$aula->id;

        return $base.'_'.$suffix;
    }

    /**
     * @param  array<string, mixed>  $vars
     */
    private function renderModeloParaPdf(DocumentoModelo $modelo, array $vars): ?string
    {
        try {
            $preamble = "@php\nextract(\$__documento_modelo_vars ?? [], EXTR_SKIP);\n@endphp\n";
            $html = Blade::render($preamble.$modelo->conteudo, array_merge($vars, [
                '__documento_modelo_vars' => $vars,
            ]));

            $opts = new Options();
            $opts->set('isRemoteEnabled', true);
            $opts->set('isHtml5ParserEnabled', true);
            $opts->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($opts);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();

            return $dompdf->output();
        } catch (\Throwable $e) {
            Log::warning('AulaNauticaDocumentosAutomaticosService: falha ao gerar PDF do modelo '.$modelo->slug.': '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return null;
        }
    }
}
