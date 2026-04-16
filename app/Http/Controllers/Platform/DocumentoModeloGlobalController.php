<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DocumentoModelo;
use App\Models\DocumentoModeloGlobal;
use App\Models\Empresa;
use App\Services\DocumentoModeloGlobalPropagationService;
use App\Services\EmpresaProcessosDefaultsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentoModeloGlobalController extends Controller
{
    /** @var list<string> */
    private const INDEX_SORT_COLUMNS = ['slug', 'titulo', 'referencia', 'updated_at'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $sort = (string) $request->query('sort', 'slug');
        if (! in_array($sort, self::INDEX_SORT_COLUMNS, true)) {
            $sort = 'slug';
        }
        $dir = strtolower((string) $request->query('dir', 'asc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        $query = DocumentoModeloGlobal::query()->orderBy($sort, $dir);
        if ($sort !== 'slug') {
            $query->orderBy('slug', 'asc');
        }

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('titulo', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('referencia', 'like', $termo);
            });
        }

        $modelos = $query->get();

        return view('platform.cadastros.documentos-automatizados.index', compact('modelos', 'q', 'sort', 'dir'));
    }

    public function create(): View
    {
        return view('platform.cadastros.documentos-automatizados.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('documento_modelo_globais', 'slug')],
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'conteudo' => ['required', 'string'],
        ]);
        $data['referencia'] = filled($data['referencia'] ?? null) ? (string) $data['referencia'] : null;

        $global = DocumentoModeloGlobal::query()->create($data);

        $svc = app(EmpresaProcessosDefaultsService::class);
        foreach (Empresa::query()->cursor() as $empresa) {
            $svc->garantirModeloGlobalNaEmpresa($empresa, $global);
        }

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.edit', $global)
            ->with('status', __('Documento automático global criado e materializado nas empresas (não personalizadas).'));
    }

    public function edit(DocumentoModeloGlobal $documento_modelo_global): View
    {
        $modelo = $documento_modelo_global;
        $refsEmpresa = (int) DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('documento_modelo_global_id', $modelo->id)
            ->count();

        $empresas = Empresa::query()
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('platform.cadastros.documentos-automatizados.edit', compact('modelo', 'refsEmpresa', 'empresas'));
    }

    public function update(Request $request, DocumentoModeloGlobal $documento_modelo_global): RedirectResponse
    {
        $slugLocked = DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('documento_modelo_global_id', $documento_modelo_global->id)
            ->exists();

        $slugRules = ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        $slugRules[] = $slugLocked
            ? Rule::in([(string) $documento_modelo_global->slug])
            : Rule::unique('documento_modelo_globais', 'slug')->ignore($documento_modelo_global->id);

        $data = $request->validate([
            'slug' => $slugRules,
            'titulo' => ['required', 'string', 'max:160'],
            'referencia' => ['nullable', 'string', 'max:160'],
            'conteudo' => ['required', 'string'],
        ]);
        $data['referencia'] = filled($data['referencia'] ?? null) ? (string) $data['referencia'] : null;

        $documento_modelo_global->update($data);

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.index')
            ->with('status', __('Documento automático global atualizado. Use «Propagar» para sincronizar empresas não personalizadas.'));
    }

    public function destroy(DocumentoModeloGlobal $documento_modelo_global): RedirectResponse
    {
        $refs = (int) DocumentoModelo::query()
            ->withoutGlobalScope('empresa')
            ->where('documento_modelo_global_id', $documento_modelo_global->id)
            ->count();

        if ($refs > 0) {
            return redirect()
                ->route('platform.cadastros.documentos-automatizados.index')
                ->withErrors(['delete' => __('Não é possível eliminar: existem :n modelo(s) de empresa ligados.', ['n' => $refs])]);
        }

        $documento_modelo_global->delete();

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.index')
            ->with('status', __('Documento automático global eliminado.'));
    }

    public function propagar(Request $request, DocumentoModeloGlobal $documento_modelo_global, DocumentoModeloGlobalPropagationService $propagation): RedirectResponse
    {
        $validated = $request->validate([
            'confirmar' => ['required', 'accepted'],
            'propagacao_escopo' => ['required', Rule::in(['todas', 'selecionadas'])],
            'empresa_ids' => ['exclude_if:propagacao_escopo,todas', 'required_if:propagacao_escopo,selecionadas', 'array', 'min:1'],
            'empresa_ids.*' => ['exclude_if:propagacao_escopo,todas', 'integer', 'distinct', 'exists:empresas,id'],
        ]);

        $ids = null;
        if ($validated['propagacao_escopo'] === 'selecionadas') {
            /** @var list<int> $ids */
            $ids = array_map('intval', $validated['empresa_ids']);
        }

        $result = $propagation->propagarParaEmpresasNaoPersonalizadas($documento_modelo_global, $ids);

        $parts = [
            __(':u empresa(s) atualizada(s).', ['u' => $result['updated']]),
            __(':s ignorada(s) (personalizadas).', ['s' => $result['skipped_customized']]),
        ];
        if ($result['skipped_oculto'] > 0) {
            $parts[] = __(':o ignorada(s) (slug oculto na empresa).', ['o' => $result['skipped_oculto']]);
        }

        return redirect()
            ->route('platform.cadastros.documentos-automatizados.edit', $documento_modelo_global)
            ->with('status', implode(' ', $parts));
    }
}
