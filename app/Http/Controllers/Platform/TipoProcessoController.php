<?php

namespace App\Http\Controllers\Platform;

use App\Enums\TipoProcessoCategoria;
use App\Http\Controllers\Controller;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Services\ProcessoChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipoProcessoController extends Controller
{
    public function __construct(
        private ProcessoChecklistService $processoChecklist,
    ) {}

    private function resolveChecklistEmpresaId(): int
    {
        $configured = config('nortex.platform_checklist_empresa_id');
        if ($configured !== null && $configured !== '' && (int) $configured > 0) {
            return (int) $configured;
        }

        $id = Empresa::query()->orderBy('id')->value('id');

        return $id ? (int) $id : throw new \RuntimeException('NORTEX_PLATFORM_CHECKLIST_EMPRESA_ID');
    }

    private function tenantTipoEspelho(PlatformTipoProcesso $platform, int $empresaId): TipoProcesso
    {
        $cat = $platform->categoria instanceof TipoProcessoCategoria
            ? $platform->categoria
            : ($platform->categoria ? TipoProcessoCategoria::tryFrom((string) $platform->categoria) : null);

        return TipoProcesso::query()->withoutGlobalScope('empresa')->firstOrCreate(
            ['empresa_id' => $empresaId, 'slug' => $platform->slug],
            [
                'nome' => $platform->nome,
                'categoria' => $cat ?? TipoProcessoCategoria::Embarcacao,
            ],
        );
    }

    private function syncTenantEspelhoAposAlterarPlataforma(PlatformTipoProcesso $platform, string $slugAntes): void
    {
        try {
            $empresaId = $this->resolveChecklistEmpresaId();
        } catch (\Throwable) {
            return;
        }

        $cat = $platform->categoria instanceof TipoProcessoCategoria
            ? $platform->categoria
            : ($platform->categoria ? TipoProcessoCategoria::tryFrom((string) $platform->categoria) : null);

        $tenant = TipoProcesso::query()->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->where('slug', $slugAntes)
            ->first();

        if (! $tenant) {
            $this->tenantTipoEspelho($platform, $empresaId);

            return;
        }

        $tenant->update([
            'slug' => $platform->slug,
            'nome' => $platform->nome,
            'categoria' => $cat ?? TipoProcessoCategoria::Embarcacao,
        ]);
    }

    /** @return array<string, string> */
    private function indexPageQueryParams(string $q, string $sort, string $dir): array
    {
        $p = [];
        if ($q !== '') {
            $p['q'] = $q;
        }
        if ($sort !== 'ordem' || $dir !== 'asc') {
            $p['sort'] = $sort;
            $p['dir'] = $dir;
        }

        return $p;
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'ordem');
        $dir = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortAllowed = ['nome', 'slug', 'categoria', 'ativo', 'ordem'];
        if (! in_array($sort, $sortAllowed, true)) {
            $sort = 'ordem';
        }

        $query = PlatformTipoProcesso::query();

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('categoria', 'like', $termo);
            });
        }

        $query->orderBy($sort, $dir)->orderBy('id');

        $tipos = $query->get();
        $categorias = TipoProcessoCategoria::cases();

        return view('platform.cadastros.tipos-processo.index', compact('tipos', 'q', 'categorias', 'sort', 'dir'));
    }

    public function create(): View
    {
        $categorias = TipoProcessoCategoria::cases();

        return view('platform.cadastros.tipos-processo.create', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_tipo_processos', 'slug')],
            'categoria' => ['nullable', Rule::enum(TipoProcessoCategoria::class)],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $tipo = PlatformTipoProcesso::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'categoria' => $data['categoria'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
        ]);

        try {
            $this->tenantTipoEspelho($tipo, $this->resolveChecklistEmpresaId());
        } catch (\Throwable) {
            /* checklist exige empresa na base */
        }

        return redirect()
            ->route('platform.cadastros.tipos-processo.edit', $tipo)
            ->with('status', __('Tipo criado.'));
    }

    public function edit(PlatformTipoProcesso $tipo_processo): View
    {
        $categorias = TipoProcessoCategoria::cases();
        $tipo = $tipo_processo;

        $checklistEmpresa = null;
        $tenantTipo = null;
        $documentoTipos = collect();

        try {
            $empresaId = $this->resolveChecklistEmpresaId();
            $checklistEmpresa = Empresa::query()->find($empresaId);
            $tenantTipo = $this->tenantTipoEspelho($tipo_processo, $empresaId);
            $tenantTipo->load(['documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem')]);

            $attached = $tenantTipo->documentoRegras;
            $attachedIds = $attached->pluck('id')->all();

            $notAttachedQuery = DocumentoTipo::query()
                ->withoutGlobalScope('empresa')
                ->where('empresa_id', $empresaId)
                ->orderBy('nome');
            if ($attachedIds !== []) {
                $notAttachedQuery->whereNotIn('id', $attachedIds);
            }
            $documentoTipos = $attached->concat($notAttachedQuery->get());
        } catch (\Throwable) {
            /* sem empresa ou config */
        }

        return view('platform.cadastros.tipos-processo.edit', compact(
            'tipo',
            'categorias',
            'checklistEmpresa',
            'tenantTipo',
            'documentoTipos',
        ));
    }

    public function update(Request $request, PlatformTipoProcesso $tipo_processo): RedirectResponse
    {
        $slugAntes = $tipo_processo->slug;

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_tipo_processos', 'slug')->ignore($tipo_processo->id)],
            'categoria' => ['nullable', Rule::enum(TipoProcessoCategoria::class)],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $tipo_processo->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'categoria' => $data['categoria'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
        ]);

        $this->syncTenantEspelhoAposAlterarPlataforma($tipo_processo->fresh(), $slugAntes);

        return redirect()
            ->route('platform.cadastros.tipos-processo.index')
            ->with('status', __('Tipo atualizado.'));
    }

    public function updateRegras(Request $request, PlatformTipoProcesso $tipo_processo): RedirectResponse
    {
        try {
            $empresaId = $this->resolveChecklistEmpresaId();
        } catch (\Throwable) {
            return redirect()
                ->route('platform.cadastros.tipos-processo.edit', $tipo_processo)
                ->withErrors(['checklist' => __('Configure uma empresa na base ou defina NORTEX_PLATFORM_CHECKLIST_EMPRESA_ID no .env.')]);
        }

        $tenantTipo = $this->tenantTipoEspelho($tipo_processo, $empresaId);

        $request->validate([
            'linhas' => ['nullable', 'array'],
            'doc_fields' => ['nullable', 'array'],
        ]);

        $sync = [];
        foreach ($request->input('linhas', []) as $row) {
            if (empty($row['ativo'])) {
                continue;
            }
            $id = (int) ($row['documento_tipo_id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            if (! DocumentoTipo::query()->withoutGlobalScope('empresa')->where('empresa_id', $empresaId)->whereKey($id)->exists()) {
                continue;
            }
            $sync[$id] = [
                'obrigatorio' => ! empty($row['obrigatorio']),
                'ordem' => max(0, min(32767, (int) ($row['ordem'] ?? 0))),
            ];
        }

        $tenantTipo->documentoRegras()->sync($sync);

        DB::table('documento_processo')
            ->where('tipo_processo_id', $tenantTipo->id)
            ->update([
                'empresa_id' => $empresaId,
                'platform_tipo_processo_id' => (int) $tipo_processo->id,
            ]);

        foreach ($request->input('doc_fields', []) as $rawId => $fields) {
            $id = (int) $rawId;
            if ($id < 1 || ! is_array($fields)) {
                continue;
            }
            /** @var DocumentoTipo|null $doc */
            $doc = DocumentoTipo::query()->withoutGlobalScope('empresa')->where('empresa_id', $empresaId)->whereKey($id)->first();
            if (! $doc) {
                continue;
            }

            $v = Validator::make($fields, [
                'codigo' => ['required', 'string', 'max:64', Rule::unique('documento_tipos', 'codigo')->where('empresa_id', $empresaId)->ignore($doc->id)],
                'nome' => ['required', 'string', 'max:500'],
                'modelo_slug' => ['nullable', 'string', 'max:128'],
            ]);
            if ($v->fails()) {
                return redirect()
                    ->route('platform.cadastros.tipos-processo.edit', $tipo_processo)
                    ->withErrors($v)
                    ->withInput();
            }

            $validated = $v->validated();
            $doc->update([
                'codigo' => $validated['codigo'],
                'nome' => $validated['nome'],
                'modelo_slug' => $validated['modelo_slug'] ?? null,
            ]);
        }

        $processosSincronizados = $this->processoChecklist->sincronizarChecklistsAposAlterarRegrasTipo($tenantTipo);

        $statusMsg = __('Checklist guardado.');
        if ($processosSincronizados > 0) {
            $statusMsg .= ' '.trans_choice(
                '{1} Checklist de :count processo atualizado.|[2,*] Checklists de :count processos atualizados.',
                $processosSincronizados,
                ['count' => $processosSincronizados],
            );
        }

        return redirect()
            ->route('platform.cadastros.tipos-processo.edit', $tipo_processo)
            ->with('status', $statusMsg);
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', Rule::in([
                'activate_selected',
                'deactivate_selected',
                'delete_selected',
                'activate_all',
                'deactivate_all',
            ])],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(['nome', 'slug', 'categoria', 'ativo', 'ordem'])],
            'dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $action = (string) $data['action'];
        $ids = array_values(array_unique(array_map('intval', $data['ids'] ?? [])));
        $q = trim((string) ($data['q'] ?? ''));
        $sort = isset($data['sort']) && in_array((string) $data['sort'], ['nome', 'slug', 'categoria', 'ativo', 'ordem'], true)
            ? (string) $data['sort']
            : 'ordem';
        $dir = isset($data['dir']) && strtolower((string) $data['dir']) === 'desc' ? 'desc' : 'asc';
        $indexParams = $this->indexPageQueryParams($q, $sort, $dir);

        $baseQuery = PlatformTipoProcesso::query();
        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $baseQuery->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('categoria', 'like', $termo);
            });
        }

        if (in_array($action, ['activate_selected', 'deactivate_selected', 'delete_selected'], true) && $ids === []) {
            return redirect()
                ->route('platform.cadastros.tipos-processo.index', $indexParams)
                ->withErrors(['bulk' => __('Selecione pelo menos um item.')]);
        }

        $query = match ($action) {
            'activate_selected', 'deactivate_selected', 'delete_selected' => $baseQuery->whereKey($ids),
            default => $baseQuery,
        };

        $affected = 0;

        if ($action === 'activate_selected' || $action === 'activate_all') {
            $affected = $query->update(['ativo' => true, 'updated_at' => now()]);

            return redirect()
                ->route('platform.cadastros.tipos-processo.index', $indexParams)
                ->with('status', trans_choice('{1} :count item ativado.|[2,*] :count itens ativados.', $affected, ['count' => $affected]));
        }

        if ($action === 'deactivate_selected' || $action === 'deactivate_all') {
            $affected = $query->update(['ativo' => false, 'updated_at' => now()]);

            return redirect()
                ->route('platform.cadastros.tipos-processo.index', $indexParams)
                ->with('status', trans_choice('{1} :count item desativado.|[2,*] :count itens desativados.', $affected, ['count' => $affected]));
        }

        // delete_selected
        DB::transaction(function () use ($query, &$affected) {
            $affected = (int) $query->count();
            $query->delete();
        });

        return redirect()
            ->route('platform.cadastros.tipos-processo.index', $indexParams)
            ->with('status', trans_choice('{1} :count item excluído permanentemente.|[2,*] :count itens excluídos permanentemente.', $affected, ['count' => $affected]));
    }
}
