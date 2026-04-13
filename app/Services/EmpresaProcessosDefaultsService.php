<?php

namespace App\Services;

use App\Models\DocumentoModelo;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Support\Normam211DocumentoCodigos;
use Illuminate\Support\Facades\Cache;

/**
 * Garante tipos de processo e checklists Marinha (embarcação/TIE, CHA e CIR).
 */
final class EmpresaProcessosDefaultsService
{
    /** Incrementar quando os templates PHP (Normam / checklist) mudarem de forma a exigir nova sincronização. */
    private const TEMPLATE_BASICO_CACHE_BUSTER = 'v12';

    public function garantirTemplateBasico(Empresa $empresa): void
    {
        $cacheKey = 'nx.empresa.'.$empresa->id.'.processos_template_basico.'.self::TEMPLATE_BASICO_CACHE_BUSTER;
        if (! app()->runningUnitTests() && Cache::get($cacheKey) === true) {
            return;
        }

        app(EmbarcacaoProcessosTemplateService::class)->sincronizar($empresa);
        app(TieProcessosTemplateService::class)->sincronizar($empresa);
        app(HabilitacaoChaProcessosTemplateService::class)->sincronizar($empresa);
        app(CirProcessosTemplateService::class)->sincronizar($empresa);

        DocumentoTipo::query()
            ->where('empresa_id', $empresa->id)
            ->where('codigo', Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP)
            ->update([
                'auto_gerado' => false,
                'modelo_slug' => 'anexo-2g',
            ]);

        DocumentoTipo::query()
            ->where('empresa_id', $empresa->id)
            ->whereIn('codigo', Normam211DocumentoCodigos::codigosDeclaracaoAnexo5h())
            ->update([
                'auto_gerado' => false,
                'modelo_slug' => 'anexo-5h',
            ]);

        DocumentoTipo::query()
            ->where('empresa_id', $empresa->id)
            ->whereIn('codigo', Normam211DocumentoCodigos::codigosDeclaracaoAnexo5d())
            ->update([
                'auto_gerado' => false,
                'modelo_slug' => 'anexo-5d',
            ]);

        $this->garantirModelosDocumentoPdfPadrao($empresa);

        if (! app()->runningUnitTests()) {
            Cache::forever($cacheKey, true);
        }
    }

    /**
     * Garante um registo em documento_modelos a partir do ficheiro padrão (ex.: primeiro acesso ao render).
     *
     * @see DocumentoModeloRenderController
     */
    public function garantirModeloPdfPadraoPorSlug(Empresa $empresa, string $slug): void
    {
        $slug = trim(mb_strtolower(preg_replace('/\s+/', '-', $slug)));
        if ($slug === '') {
            return;
        }

        $map = self::mapModelosPdfFicheiroPadrao();
        if (! isset($map[$slug])) {
            return;
        }

        $meta = $map[$slug];
        $titulo = $meta['titulo'];
        $relativePath = $meta['relativePath'];
        $referencia = isset($meta['referencia']) && $meta['referencia'] !== ''
            ? (string) $meta['referencia']
            : null;
        $this->garantirModeloPdfDeArquivo($empresa, $slug, $titulo, $relativePath, $referencia);
    }

    /**
     * Slugs com template em resources/views/documento-modelos/defaults/.
     *
     * @return array<string, array{titulo: string, relativePath: string, referencia?: string}>
     */
    public static function modelosPdfPadraoDefinicoes(): array
    {
        return self::mapModelosPdfFicheiroPadrao();
    }

    /**
     * @return array<string, array{titulo: string, relativePath: string, referencia?: string}>
     */
    private static function mapModelosPdfFicheiroPadrao(): array
    {
        return [
            'anexo-2g' => [
                'titulo' => 'ANEXO 2-G - Declaração de residência',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2g.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-5h' => [
                'titulo' => 'ANEXO 5-H - Requerimento (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-5h.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-5d' => [
                'titulo' => 'ANEXO 5-D - Declaração de extravio/dano (CHA, NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-5d.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-1c-normam212' => [
                'titulo' => 'ANEXO 1-C - Declaração de residência (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-1c-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2a-normam212' => [
                'titulo' => 'ANEXO 2-A - Requerimento (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2a-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2b-bdmoto-normam212' => [
                'titulo' => 'ANEXO 2-B - BDMOTO (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bdmoto-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2c-normam212' => [
                'titulo' => 'ANEXO 2-C - Declaração de perda/roubo/extravio de TIE (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2c-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-3a-cha-mta-normam212' => [
                'titulo' => 'ANEXO 3-A - Requerimento CHA-MTA (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-3a-cha-mta-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-3d-extravio-cha-mta-normam212' => [
                'titulo' => 'ANEXO 3-D - Declaração de extravio CHA-MTA (NORMAM 212)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-3d-extravio-cha-mta-normam212.blade.php',
                'referencia' => 'NORMAM-212/DPC',
            ],
            'anexo-2b-bsade' => [
                'titulo' => 'ANEXO 2-B - BSADE (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2b-bsade.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2c-normam211' => [
                'titulo' => 'ANEXO 2-C - Requerimento (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2c-normam211.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
            'anexo-2h-normam211' => [
                'titulo' => 'ANEXO 2-H - Declaração de extravio (NORMAM 211)',
                'relativePath' => 'views/documento-modelos/defaults/anexo-2h-normam211.blade.php',
                'referencia' => 'NORMAM-211/DPC',
            ],
        ];
    }

    /**
     * Registros em documento_modelos exigidos por {@see DocumentoModeloRenderController}.
     * Sem eles, /clientes/{id}/documento-modelos/anexo-5h responde 404.
     */
    private function garantirModelosDocumentoPdfPadrao(Empresa $empresa): void
    {
        foreach (self::mapModelosPdfFicheiroPadrao() as $slug => $meta) {
            $this->garantirModeloPdfDeArquivo($empresa, $slug, $meta['titulo'], $meta['relativePath']);
        }

        $mapTipoModelo = [
            'TIE_COMPROVANTE_RESID_212_1C' => 'anexo-1c-normam212',
            'CHA_COMPROVANTE_RESIDENCIA_212_2C' => 'anexo-1c-normam212',
            'TIE_REQ_INTERESSADO_ANEXO_2A_212' => 'anexo-2a-normam212',
            'TIE_BDMOTO_212_2B' => 'anexo-2b-bdmoto-normam212',
            'TIE_BDMOTO_SE_ALTERACAO' => 'anexo-2b-bdmoto-normam212',
            'CHA_REQ_ANEXO_3A_212' => 'anexo-3a-cha-mta-normam212',
            'CHA_DECL_EXTRAVIO_MTA_3D_212' => 'anexo-3d-extravio-cha-mta-normam212',
            'TIE_REQ_INTERESSADO_ANEXO_2C_211' => 'anexo-2c-normam211',
            'TIE_BSADE_211_2B_DUAS_VIAS' => 'anexo-2b-bsade',
            'REQ_NORMAM_2C' => 'anexo-2c-normam211',
            'DECL_EXTRAVIO_2H' => 'anexo-2h-normam211',
            // Compat legado (corrigido para 2-B): manter para que o item antigo passe a ter modelo também.
            'BSADE_NORMAM_2D' => 'anexo-2b-bsade',
        ];
        foreach ($mapTipoModelo as $codigo => $slug) {
            DocumentoTipo::query()
                ->where('empresa_id', $empresa->id)
                ->where('codigo', $codigo)
                ->update([
                    'auto_gerado' => false,
                    'modelo_slug' => $slug,
                ]);
        }
    }

    private function garantirModeloPdfDeArquivo(Empresa $empresa, string $slug, string $titulo, string $relativeToViews, ?string $referencia = null): void
    {
        if ($empresa->documentoModeloLabSlugEstaOculto($slug)) {
            return;
        }

        $path = resource_path($relativeToViews);
        $conteudo = is_file($path) ? file_get_contents($path) : false;
        if (! is_string($conteudo) || trim($conteudo) === '') {
            return;
        }

        DocumentoModelo::query()->updateOrCreate(
            ['empresa_id' => $empresa->id, 'slug' => $slug],
            [
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'conteudo_upload_bruto' => $conteudo,
                'upload_mapeamento_pendente' => false,
                'referencia' => $referencia,
            ],
        );
    }
}
