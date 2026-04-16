<?php

namespace App\Services;

use App\Models\DocumentoModelo;
use App\Models\DocumentoModeloGlobal;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Support\DocumentoModeloCatalogoPadrao;
use App\Support\Normam211DocumentoCodigos;
use Illuminate\Support\Facades\Cache;

/**
 * Garante tipos de processo e checklists Marinha (embarcação/TIE, CHA e CIR).
 */
final class EmpresaProcessosDefaultsService
{
    /** Incrementar quando os templates PHP (Normam / checklist) mudarem de forma a exigir nova sincronização. */
    private const TEMPLATE_BASICO_CACHE_BUSTER = 'v14';

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

        $global = DocumentoModeloGlobal::query()->where('slug', $slug)->first();
        if ($global !== null) {
            $this->garantirModeloGlobalNaEmpresa($empresa, $global);

            return;
        }

        $meta = DocumentoModeloCatalogoPadrao::metaPorSlug($slug);
        if ($meta === null) {
            return;
        }

        $referencia = isset($meta['referencia']) && $meta['referencia'] !== ''
            ? (string) $meta['referencia']
            : null;
        $this->garantirModeloPdfDeArquivoLegado($empresa, $slug, $meta['titulo'], $meta['relativePath'], $referencia);
    }

    /**
     * Catálogo para documentos automatizados e validações: primeiro a tabela global; fallback ao mapa em ficheiros.
     *
     * @return array<string, array{titulo: string, relativePath?: string, referencia?: string|null, documento_modelo_global_id?: int|null}>
     */
    public static function modelosPdfPadraoDefinicoes(): array
    {
        $out = [];
        foreach (DocumentoModeloGlobal::query()->orderBy('slug')->get() as $g) {
            $out[$g->slug] = [
                'titulo' => $g->titulo,
                'referencia' => $g->referencia,
                'documento_modelo_global_id' => (int) $g->id,
            ];
        }
        foreach (DocumentoModeloCatalogoPadrao::mapaFicheirosRelativos() as $slug => $meta) {
            if (isset($out[$slug])) {
                continue;
            }
            if (DocumentoModeloCatalogoPadrao::conteudoDoFicheiroPadrao($slug) === null) {
                continue;
            }
            $out[$slug] = [
                'titulo' => $meta['titulo'],
                'relativePath' => $meta['relativePath'],
                'referencia' => $meta['referencia'] ?? null,
                'documento_modelo_global_id' => null,
            ];
        }

        return $out;
    }

    /**
     * Registros em documento_modelos exigidos por {@see DocumentoModeloRenderController}.
     * Sem eles, /clientes/{id}/documento-modelos/anexo-5h responde 404.
     */
    private function garantirModelosDocumentoPdfPadrao(Empresa $empresa): void
    {
        foreach (DocumentoModeloGlobal::query()->orderBy('slug')->cursor() as $global) {
            $this->garantirModeloGlobalNaEmpresa($empresa, $global);
        }
        foreach (DocumentoModeloCatalogoPadrao::mapaFicheirosRelativos() as $slug => $meta) {
            if (DocumentoModeloGlobal::query()->where('slug', $slug)->exists()) {
                continue;
            }
            $this->garantirModeloPdfDeArquivoLegado($empresa, $slug, $meta['titulo'], $meta['relativePath'], $meta['referencia'] ?? null);
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

    /**
     * Repõe o conteúdo da empresa a partir do registo global (marca como não personalizado).
     *
     * @return string|null Mensagem de erro ou null se OK
     */
    public function reporEsqueletoGlobalNaEmpresa(Empresa $empresa, string $slug): ?string
    {
        $slug = trim(mb_strtolower(preg_replace('/\s+/', '-', $slug)));
        if ($slug === '') {
            return __('Slug inválido.');
        }

        $global = DocumentoModeloGlobal::query()->where('slug', $slug)->first();
        if ($global === null) {
            return __('Não existe documento automático global para este slug.');
        }

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', $slug)
            ->first();

        if ($modelo === null) {
            $this->garantirModeloGlobalNaEmpresa($empresa, $global);

            return null;
        }

        $modelo->update([
            'titulo' => $global->titulo,
            'referencia' => $global->referencia,
            'conteudo' => $global->conteudo,
            'conteudo_upload_bruto' => $global->conteudo,
            'upload_mapeamento_pendente' => false,
            'mapeamento_upload' => null,
            'documento_modelo_global_id' => $global->id,
            'personalizado' => false,
            'global_synced_at' => now(),
        ]);

        return null;
    }

    public function garantirModeloGlobalNaEmpresa(Empresa $empresa, DocumentoModeloGlobal $global): void
    {
        if ($empresa->documentoModeloLabSlugEstaOculto($global->slug)) {
            return;
        }

        $existing = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', $global->slug)
            ->first();

        if ($existing !== null && $existing->personalizado) {
            if ($existing->documento_modelo_global_id === null) {
                $existing->update(['documento_modelo_global_id' => $global->id]);
            }

            return;
        }

        DocumentoModelo::query()->updateOrCreate(
            ['empresa_id' => $empresa->id, 'slug' => $global->slug],
            [
                'titulo' => $global->titulo,
                'conteudo' => $global->conteudo,
                'conteudo_upload_bruto' => $global->conteudo,
                'upload_mapeamento_pendente' => false,
                'referencia' => $global->referencia,
                'documento_modelo_global_id' => $global->id,
                'personalizado' => false,
                'global_synced_at' => now(),
            ],
        );
    }

    /**
     * Fallback quando existe ficheiro em resources/views mas ainda não há linha em documento_modelo_globais.
     */
    private function garantirModeloPdfDeArquivoLegado(Empresa $empresa, string $slug, string $titulo, string $relativeToViews, ?string $referencia = null): void
    {
        if ($empresa->documentoModeloLabSlugEstaOculto($slug)) {
            return;
        }

        $path = resource_path($relativeToViews);
        $conteudo = is_file($path) ? file_get_contents($path) : false;
        if (! is_string($conteudo) || trim($conteudo) === '') {
            return;
        }

        $existing = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresa->id)
            ->where('slug', $slug)
            ->first();

        if ($existing !== null && $existing->personalizado) {
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
                'documento_modelo_global_id' => null,
                'personalizado' => false,
                'global_synced_at' => null,
            ],
        );
    }
}
