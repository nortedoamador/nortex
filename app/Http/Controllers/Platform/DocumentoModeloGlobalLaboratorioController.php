<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoModeloGlobal;
use App\Models\Embarcacao;
use App\Models\Empresa;
use App\Services\EmpresaProcessosDefaultsService;
use App\Support\ChecklistDocumentoModelo;
use App\Support\DocumentoModeloFicheiroUpload;
use App\Support\DocumentoModeloTemplateAnexo5dPreamble;
use App\Support\DocumentoModeloTemplatePosUpload;
use App\Support\DocumentoModeloTemplateSpanBinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentoModeloGlobalLaboratorioController extends Controller
{
    /** @var list<string> */
    private const LAB_SORT_COLUMNS = ['slug', 'titulo', 'referencia', 'atualizado_em', 'precisa_embarcacao', 'tem_modelo'];

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->is_platform_admin, 403);

        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);

        $empresaId = $request->query('empresa_id');
        $empresaIdInt = null;
        if ($empresaId !== null && $empresaId !== '' && ctype_digit((string) $empresaId)) {
            $empresaIdInt = (int) $empresaId;
        }

        $clientes = collect();
        if ($empresaIdInt !== null) {
            $clientes = Cliente::query()
                ->where('empresa_id', $empresaIdInt)
                ->orderBy('nome')
                ->get(['id', 'nome']);
        }

        $clienteId = $request->query('cliente_id');
        $embarcacaoId = $request->query('embarcacao_id');

        $embarcacoes = collect();
        if ($empresaIdInt !== null && $clienteId !== null && $clienteId !== '' && ctype_digit((string) $clienteId)) {
            $embarcacoes = Embarcacao::query()
                ->where('empresa_id', $empresaIdInt)
                ->where('cliente_id', (int) $clienteId)
                ->orderBy('nome')
                ->get(['id', 'nome']);
        }

        $sort = (string) $request->query('sort', 'slug');
        if (! in_array($sort, self::LAB_SORT_COLUMNS, true)) {
            $sort = 'slug';
        }
        $dir = strtolower((string) $request->query('dir', 'asc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        $linhasLaboratorio = [];
        foreach (DocumentoModeloGlobal::query()->orderBy('slug')->get() as $g) {
            $linhasLaboratorio[] = [
                'slug' => $g->slug,
                'titulo' => $g->titulo,
                'referencia' => filled($g->referencia) ? (string) $g->referencia : null,
                'modelo' => $g,
                'atualizado_em' => $g->updated_at?->getTimestamp() ?? 0,
                'precisa_embarcacao' => ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($g->slug),
                'tem_modelo' => true,
            ];
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

        $countGlobais = DocumentoModeloGlobal::query()->count();

        return view('platform.cadastros.documentos-automatizados.laboratorio', [
            'empresas' => $empresas,
            'clientes' => $clientes,
            'embarcacoes' => $embarcacoes,
            'empresaId' => $empresaId,
            'clienteId' => $clienteId,
            'embarcacaoId' => $embarcacaoId,
            'linhasLaboratorio' => $linhasLaboratorio,
            'countGlobais' => $countGlobais,
            'labSort' => $sort,
            'labDir' => $dir,
            'precisaContexto' => static fn (string $slug): bool => ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slug),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->is_platform_admin, 403);

        $request->validate([
            'slug' => ['required', 'string', 'max:80'],
            'arquivo' => ['required', 'file', 'max:'.upload_max_kb()],
            'empresa_id' => ['nullable', 'string'],
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
        $global = DocumentoModeloGlobal::query()->where('slug', $slug)->first();
        if ($global === null) {
            return back()->withErrors([
                'slug' => __('Não existe um documento automático global com este slug.'),
            ]);
        }

        $global->update([
            'conteudo' => $conteudo,
            'mapeamento_upload' => $mapeamentoUpload,
            'upload_mapeamento_pendente' => false,
            'conteudo_upload_bruto' => null,
        ]);

        $query = $this->laboratorioRedirectQuery($request);

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.laboratorio', $query)
            ->with('status', __('Documento global atualizado a partir do ficheiro enviado (:slug).', ['slug' => $slug]));
    }

    public function storeNovo(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->is_platform_admin, 403);

        $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:80'],
            'arquivo' => ['required', 'file', 'max:'.upload_max_kb()],
            'empresa_id' => ['nullable', 'string'],
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
        if (DocumentoModeloGlobal::query()->where('slug', $base)->exists()) {
            return back()->withErrors(['slug' => __('Já existe um documento automático global com este slug.')])->withInput();
        }

        $global = DocumentoModeloGlobal::query()->create([
            'slug' => $base,
            'titulo' => $request->input('titulo'),
            'referencia' => filled($request->input('referencia')) ? (string) $request->input('referencia') : null,
            'conteudo' => $posUpload['html'],
            'conteudo_upload_bruto' => null,
            'upload_mapeamento_pendente' => false,
            'mapeamento_upload' => $posUpload['mapeamento_upload'],
        ]);

        $svc = app(EmpresaProcessosDefaultsService::class);
        foreach (Empresa::query()->cursor() as $empresa) {
            $svc->garantirModeloGlobalNaEmpresa($empresa, $global);
        }

        $query = $this->laboratorioRedirectQuery($request);

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.laboratorio', $query)
            ->with('status', __('Novo documento automático global criado (:slug) e materializado nas empresas (não personalizadas).', ['slug' => $base]));
    }

    /**
     * @return array<string, string|int>
     */
    private function laboratorioRedirectQuery(Request $request): array
    {
        $query = array_filter(
            [
                'empresa_id' => $request->input('empresa_id'),
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
