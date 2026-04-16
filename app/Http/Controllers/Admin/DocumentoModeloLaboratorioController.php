<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoModelo;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Services\EmpresaProcessosDefaultsService;
use App\Support\ChecklistDocumentoModelo;
use App\Support\DocumentoModeloFicheiroUpload;
use App\Support\DocumentoModeloPadraoFicheiro;
use App\Support\DocumentoModeloTemplateAnexo5dPreamble;
use App\Support\DocumentoModeloTemplatePosUpload;
use App\Support\DocumentoModeloTemplateSpanBinder;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentoModeloLaboratorioController extends Controller
{
    /** @var list<string> */
    private const LAB_SORT_COLUMNS = ['slug', 'titulo', 'referencia', 'atualizado_em', 'precisa_embarcacao', 'tem_modelo'];

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        $clientes = Cliente::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $clienteId = $request->query('cliente_id');
        $embarcacaoId = $request->query('embarcacao_id');

        $embarcacoes = collect();
        if ($clienteId !== null && $clienteId !== '' && ctype_digit((string) $clienteId)) {
            $embarcacoes = Embarcacao::query()
                ->where('empresa_id', $empresaId)
                ->where('cliente_id', (int) $clienteId)
                ->orderBy('nome')
                ->get(['id', 'nome']);
        }

        $modelosCatalogo = EmpresaProcessosDefaultsService::modelosPdfPadraoDefinicoes();
        $slugsCatalogo = array_keys($modelosCatalogo);
        $catalogSlugsLookup = array_fill_keys($slugsCatalogo, true);

        $empresa = Empresa::query()->find($empresaId);

        $modelosPorSlug = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->get()
            ->keyBy('slug');

        $sort = (string) $request->query('sort', 'slug');
        if (! in_array($sort, self::LAB_SORT_COLUMNS, true)) {
            $sort = 'slug';
        }
        $dir = strtolower((string) $request->query('dir', 'asc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        $linhasLaboratorio = [];
        foreach ($modelosCatalogo as $slug => $meta) {
            if ($empresa !== null && $empresa->documentoModeloLabSlugEstaOculto($slug)) {
                continue;
            }
            $m = $modelosPorSlug->get($slug);
            $refCatalogo = isset($meta['referencia']) && $meta['referencia'] !== '' ? (string) $meta['referencia'] : null;
            $globalId = isset($meta['documento_modelo_global_id']) && $meta['documento_modelo_global_id'] !== null
                ? (int) $meta['documento_modelo_global_id']
                : null;
            $linhasLaboratorio[] = [
                'slug' => $slug,
                'titulo' => $meta['titulo'],
                'referencia' => filled($m?->referencia) ? (string) $m->referencia : $refCatalogo,
                'modelo' => $m,
                'atualizado_em' => $m?->updated_at?->getTimestamp() ?? 0,
                'precisa_embarcacao' => ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slug),
                'tem_modelo' => $m !== null,
                'documento_modelo_global_id' => $globalId,
                'personalizado' => (bool) ($m?->personalizado),
                'igual_ao_global' => $m !== null && $globalId !== null && ! $m->personalizado,
            ];
        }
        foreach ($modelosPorSlug->sortBy('titulo', SORT_NATURAL) as $m) {
            if (! array_key_exists($m->slug, $modelosCatalogo)) {
                $linhasLaboratorio[] = [
                    'slug' => $m->slug,
                    'titulo' => $m->titulo,
                    'referencia' => filled($m->referencia) ? (string) $m->referencia : null,
                    'modelo' => $m,
                    'atualizado_em' => $m->updated_at?->getTimestamp() ?? 0,
                    'precisa_embarcacao' => ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($m->slug),
                    'tem_modelo' => true,
                    'documento_modelo_global_id' => $m->documento_modelo_global_id,
                    'personalizado' => (bool) $m->personalizado,
                    'igual_ao_global' => $m->documento_modelo_global_id !== null && ! $m->personalizado,
                ];
            }
        }

        $factor = $dir === 'desc' ? -1 : 1;
        $linhasLaboratorio = collect($linhasLaboratorio)
            ->sort(function (array $a, array $b) use ($sort, $factor): int {
                $va = $a[$sort];
                $vb = $b[$sort];
                if ($sort === 'atualizado_em') {
                    return (((int) $va) <=> ((int) $vb)) * $factor;
                }
                if (is_bool($va) && is_bool($vb)) {
                    return ($va <=> $vb) * $factor;
                }

                return strnatcasecmp((string) $va, (string) $vb) * $factor;
            })
            ->values()
            ->all();

        $countModelosSoCatalogo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->whereNotIn('slug', $slugsCatalogo)
            ->count();

        return view('admin.documento-modelos.laboratorio', [
            'clientes' => $clientes,
            'embarcacoes' => $embarcacoes,
            'clienteId' => $clienteId,
            'embarcacaoId' => $embarcacaoId,
            'linhasLaboratorio' => $linhasLaboratorio,
            'countModelosSoCatalogo' => $countModelosSoCatalogo,
            'labSort' => $sort,
            'labDir' => $dir,
            'catalogSlugsLookup' => $catalogSlugsLookup,
            'precisaContexto' => static fn (string $slug): bool => ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slug),
        ]);
    }

    public function destroy(Request $request, Empresa $empresa, DocumentoModelo $modelo): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);
        abort_unless((int) $modelo->empresa_id === $empresaId, 404);

        $slug = $modelo->slug;
        $modelo->delete();

        $slugsCatalogo = array_keys(EmpresaProcessosDefaultsService::modelosPdfPadraoDefinicoes());
        if (in_array($slug, $slugsCatalogo, true)) {
            $empresa = Empresa::query()->find($empresaId);
            $empresa?->addDocumentoModeloLabSlugOculto($slug);
        }

        $query = $this->laboratorioRedirectQuery($request);

        return redirect()
            ->to(tenant_admin_route('documento-modelos.laboratorio', $query))
            ->with('status', __('Modelo removido (:slug).', ['slug' => $slug]));
    }

    public function reporEsqueletoGlobal(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $slugsCatalogo = array_keys(EmpresaProcessosDefaultsService::modelosPdfPadraoDefinicoes());

        $request->validate([
            'slug' => ['required', 'string', 'max:80', Rule::in($slugsCatalogo)],
            'cliente_id' => ['nullable', 'string'],
            'embarcacao_id' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'max:32'],
            'dir' => ['nullable', 'string', 'max:4'],
        ]);

        $slug = Str::lower(trim((string) $request->input('slug')));

        $empresa = Empresa::query()->findOrFail($empresaId);

        $erro = app(EmpresaProcessosDefaultsService::class)->reporEsqueletoGlobalNaEmpresa($empresa, $slug);
        if ($erro !== null) {
            return redirect()
                ->to(tenant_admin_route('documento-modelos.laboratorio', $this->laboratorioRedirectQuery($request)))
                ->withErrors(['slug' => $erro]);
        }

        $query = $this->laboratorioRedirectQuery($request);

        return redirect()
            ->to(tenant_admin_route('documento-modelos.laboratorio', $query))
            ->with('status', __('Conteúdo reposto a partir do documento automático global (:slug).', ['slug' => $slug]));
    }

    public function ocultarCatalogo(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $slugsCatalogo = array_keys(EmpresaProcessosDefaultsService::modelosPdfPadraoDefinicoes());

        $request->validate([
            'slug' => ['required', 'string', 'max:80', Rule::in($slugsCatalogo)],
            'cliente_id' => ['nullable', 'string'],
            'embarcacao_id' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'max:32'],
            'dir' => ['nullable', 'string', 'max:4'],
        ]);

        $slug = Str::lower(trim((string) $request->input('slug')));

        $empresa = Empresa::query()->findOrFail($empresaId);
        $empresa->addDocumentoModeloLabSlugOculto($slug);

        DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->delete();

        $query = $this->laboratorioRedirectQuery($request);

        return redirect()
            ->to(tenant_admin_route('documento-modelos.laboratorio', $query))
            ->with('status', __('Modelo removido da lista (:slug).', ['slug' => $slug]));
    }

    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $slugsCatalogo = array_keys(EmpresaProcessosDefaultsService::modelosPdfPadraoDefinicoes());

        $request->validate([
            'slug' => ['required', 'string', 'max:80'],
            'arquivo' => ['required', 'file', 'max:15360'],
            'cliente_id' => ['nullable', 'string'],
            'embarcacao_id' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'max:32'],
            'dir' => ['nullable', 'string', 'max:4'],
        ]);

        /** @var UploadedFile $arquivo */
        $arquivo = $request->file('arquivo');
        $lido = DocumentoModeloFicheiroUpload::lerConteudoValidado($arquivo);
        if (isset($lido['error'])) {
            return back()->withErrors(['arquivo' => $lido['error']]);
        }
        $binder = DocumentoModeloTemplateSpanBinder::aplicarComRelatorio($lido['content']);
        $conteudo = DocumentoModeloTemplateAnexo5dPreamble::prependSeNecessario($binder['html']);
        $mapeamentoUpload = [
            'gerado_em' => now()->toIso8601String(),
            'itens' => $binder['itens'],
        ];

        $slug = Str::lower(trim((string) $request->input('slug')));
        $slugNoCatalogo = in_array($slug, $slugsCatalogo, true);
        $slugExisteEmpresa = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->exists();
        if (! $slugNoCatalogo && ! $slugExisteEmpresa) {
            return back()->withErrors([
                'slug' => __('Este slug não corresponde a um modelo do catálogo nem a um modelo da sua empresa.'),
            ]);
        }

        $empresa = Empresa::query()->find($empresaId);
        abort_unless($empresa !== null, 404);

        app(EmpresaProcessosDefaultsService::class)->garantirModeloPdfPadraoPorSlug($empresa, $slug);

        $modelo = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->where('slug', $slug)
            ->first();

        abort_unless($modelo !== null, 404);

        $patch = [
            'conteudo' => $conteudo,
            'mapeamento_upload' => $mapeamentoUpload,
        ];
        if ($modelo->documento_modelo_global_id !== null) {
            $patch['personalizado'] = true;
            $patch['global_synced_at'] = null;
        }
        $modelo->update($patch);

        $empresa->removeDocumentoModeloLabSlugOculto($slug);

        $diskErr = DocumentoModeloPadraoFicheiro::gravarSeModeloEmpresaSemGlobal($modelo, $conteudo);

        $query = $this->laboratorioRedirectQuery($request);

        $status = __('Modelo atualizado a partir do ficheiro enviado (:slug). Revise o mapeamento na página seguinte.', ['slug' => $slug]);
        if ($diskErr !== null) {
            $status .= ' '.$diskErr;
        }

        return redirect()
            ->to(tenant_doc_modelo_route('verificacao', array_merge(['modelo' => $modelo], $query)))
            ->with('status', $status);
    }

    public function storeNovo(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && TenantEmpresaContext::canAccessLaboratorioPdf($user, $request), 403);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:80'],
            'arquivo' => ['required', 'file', 'max:15360'],
            'cliente_id' => ['nullable', 'string'],
            'embarcacao_id' => ['nullable', 'string'],
            'sort' => ['nullable', 'string', 'max:32'],
            'dir' => ['nullable', 'string', 'max:4'],
        ]);

        /** @var UploadedFile $arquivo */
        $arquivo = $request->file('arquivo');
        $lido = DocumentoModeloFicheiroUpload::lerConteudoValidado($arquivo);
        if (isset($lido['error'])) {
            return back()->withErrors(['arquivo' => $lido['error']])->withInput();
        }

        $raw = $lido['content'];
        $posUpload = DocumentoModeloTemplatePosUpload::processar($raw);

        $base = DocumentoModeloFicheiroUpload::normalizarSlugCandidato($request->input('slug'), (string) $request->input('titulo'));
        if ($base === '') {
            return back()->withErrors([
                'titulo' => __('Não foi possível gerar um identificador (slug). Indique um slug manualmente.'),
            ])->withInput();
        }
        if (strlen($base) > 80) {
            return back()->withErrors(['slug' => __('O identificador (slug) não pode exceder 80 caracteres.')])->withInput();
        }
        if (DocumentoModelo::query()->withoutGlobalScope('empresa')->where('empresa_id', $empresaId)->where('slug', $base)->exists()) {
            return back()->withErrors(['slug' => __('Já existe um modelo com este slug.')])->withInput();
        }

        $modelo = DocumentoModelo::query()->create([
            'empresa_id' => $empresaId,
            'slug' => $base,
            'titulo' => $request->input('titulo'),
            'referencia' => filled($request->input('referencia')) ? (string) $request->input('referencia') : null,
            'conteudo' => $raw,
            'conteudo_upload_bruto' => $raw,
            'upload_mapeamento_pendente' => true,
            'mapeamento_upload' => $posUpload['mapeamento_upload'],
            'documento_modelo_global_id' => null,
            'personalizado' => true,
            'global_synced_at' => null,
        ]);

        $query = $this->laboratorioRedirectQuery($request);

        $status = __('Novo modelo criado (:slug). Confirme o mapeamento na verificação para gravar variáveis e o ficheiro em disco.', ['slug' => $base]);

        return redirect()
            ->to(tenant_doc_modelo_route('verificacao', array_merge(['modelo' => $modelo], $query)))
            ->with('status', $status);
    }

    /**
     * @return array<string, string>
     */
    private function laboratorioRedirectQuery(Request $request): array
    {
        $query = array_filter(
            [
                'cliente_id' => $request->input('cliente_id'),
                'embarcacao_id' => $request->input('embarcacao_id'),
            ],
            static fn ($v) => $v !== null && $v !== '',
        );
        $sort = $request->input('sort');
        if (is_string($sort) && in_array($sort, self::LAB_SORT_COLUMNS, true)) {
            $query['sort'] = $sort;
        }
        $dir = $request->input('dir');
        if (is_string($dir) && in_array(strtolower($dir), ['asc', 'desc'], true)) {
            $query['dir'] = strtolower($dir);
        }

        return $query;
    }
}
