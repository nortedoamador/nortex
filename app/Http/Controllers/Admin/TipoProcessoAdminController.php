<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoProcessoCategoria;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\DocumentoTipo;
use App\Models\PlatformTipoProcesso;
use App\Models\TipoProcesso;
use App\Services\ActivityLogService;
use App\Services\ProcessoChecklistService;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipoProcessoAdminController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private ProcessoChecklistService $processoChecklist,
    ) {}

    public function index(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $tipos = TipoProcesso::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->withCount('processos')
            ->orderBy('nome')
            ->get();

        return view('admin.tipo-processos.index', compact('tipos'));
    }

    public function create(): View
    {
        $categorias = TipoProcessoCategoria::cases();

        return view('admin.tipo-processos.create', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:128',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tipo_processos', 'slug')->where('empresa_id', $empresaId),
            ],
            'categoria' => ['required', Rule::enum(TipoProcessoCategoria::class)],
        ]);

        $tipo = TipoProcesso::query()->create([
            'empresa_id' => $empresaId,
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'categoria' => $data['categoria'],
        ]);

        $this->activityLog->log(
            'tipo_processo_created',
            __('Cadastro: tipo de processo «:n» criado.', ['n' => $tipo->nome]),
            $empresaId,
            TipoProcesso::class,
            (int) $tipo->id,
        );

        return redirect()
            ->to(tenant_admin_route('tipo-processos.edit-regras', ['tipo_processo' => $tipo]))
            ->with('status', __('Tipo criado. Defina o checklist de documentos.'));
    }

    public function edit(TipoProcesso $tipo_processo): View
    {
        $categorias = TipoProcessoCategoria::cases();
        $tipoProcesso = $tipo_processo;

        return view('admin.tipo-processos.edit', compact('tipoProcesso', 'categorias'));
    }

    public function update(Request $request, Empresa $empresa, TipoProcesso $tipo_processo): RedirectResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:128',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tipo_processos', 'slug')->where('empresa_id', $empresaId)->ignore($tipo_processo->id),
            ],
            'categoria' => ['required', Rule::enum(TipoProcessoCategoria::class)],
        ]);

        $tipo_processo->update($data);

        return redirect()
            ->to(tenant_admin_route('tipo-processos.index'))
            ->with('status', __('Tipo de processo atualizado.'));
    }

    public function editRegras(TipoProcesso $tipo_processo): View
    {
        $tipo_processo->load(['documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem')]);

        $attached = $tipo_processo->documentoRegras;
        $attachedIds = $attached->pluck('id')->all();

        $empresaId = TenantEmpresaContext::empresaId(request());

        $notAttachedQuery = DocumentoTipo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome');
        if ($attachedIds !== []) {
            $notAttachedQuery->whereNotIn('id', $attachedIds);
        }
        $notAttached = $notAttachedQuery->get();

        $documentoTipos = $attached->concat($notAttached);
        $tipoProcesso = $tipo_processo;

        return view('admin.tipo-processos.regras', compact('tipoProcesso', 'documentoTipos'));
    }

    public function updateRegras(Request $request, Empresa $empresa, TipoProcesso $tipo_processo): RedirectResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $request->validate([
            'linhas' => ['nullable', 'array'],
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

        $tipo_processo->documentoRegras()->sync($sync);

        $platformTipoId = PlatformTipoProcesso::query()
            ->where('slug', $tipo_processo->slug)
            ->value('id');
        if ($platformTipoId) {
            DB::table('documento_processo')
                ->where('tipo_processo_id', $tipo_processo->id)
                ->update([
                    'empresa_id' => $empresaId,
                    'platform_tipo_processo_id' => (int) $platformTipoId,
                ]);
        }

        $processosSincronizados = $this->processoChecklist->sincronizarChecklistsAposAlterarRegrasTipo($tipo_processo);

        $this->activityLog->log(
            'tipo_processo_regras',
            __('Checklist do tipo «:n» atualizado.', ['n' => $tipo_processo->nome]),
            $empresaId,
            TipoProcesso::class,
            (int) $tipo_processo->id,
            ['itens' => count($sync), 'processos_sincronizados' => $processosSincronizados],
        );

        $statusMsg = __('Regras de documentos salvas.');
        if ($processosSincronizados > 0) {
            $statusMsg .= ' '.trans_choice(
                '{1} Checklist de :count processo atualizado imediatamente.|[2,*] Checklists de :count processos atualizados imediatamente.',
                $processosSincronizados,
                ['count' => $processosSincronizados],
            );
        }

        return redirect()
            ->to(tenant_admin_route('tipo-processos.index'))
            ->with('status', $statusMsg);
    }

    public function destroy(Request $request, Empresa $empresa, TipoProcesso $tipo_processo): RedirectResponse
    {
        if ($tipo_processo->processos()->exists()) {
            return redirect()
                ->to(tenant_admin_route('tipo-processos.index'))
                ->withErrors(['delete' => __('Existem processos vinculados a este tipo. Não é possível excluir.')]);
        }

        $nome = $tipo_processo->nome;
        $tid = (int) $tipo_processo->id;
        $tipo_processo->documentoRegras()->detach();
        $tipo_processo->delete();

        $this->activityLog->log(
            'tipo_processo_deleted',
            __('Tipo de processo «:n» removido.', ['n' => $nome]),
            TenantEmpresaContext::empresaId($request),
            TipoProcesso::class,
            $tid,
        );

        return redirect()
            ->to(tenant_admin_route('tipo-processos.index'))
            ->with('status', __('Tipo removido.'));
    }
}
