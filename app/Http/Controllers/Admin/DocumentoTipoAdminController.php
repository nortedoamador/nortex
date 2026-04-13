<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\ProcessoDocumento;
use App\Services\ActivityLogService;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentoTipoAdminController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $tipos = DocumentoTipo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get();

        return view('admin.documento-tipos.index', compact('tipos'));
    }

    public function create(): View
    {
        return view('admin.documento-tipos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:64',
                Rule::unique('documento_tipos', 'codigo')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:500'],
            'auto_gerado' => ['nullable', 'boolean'],
            'modelo_slug' => ['nullable', 'string', 'max:128'],
        ]);

        $tipo = DocumentoTipo::query()->create([
            'empresa_id' => $empresaId,
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'auto_gerado' => $request->boolean('auto_gerado'),
            'modelo_slug' => $data['modelo_slug'] ?? null,
        ]);

        $this->activityLog->log(
            'documento_tipo_created',
            __('Tipo de documento «:n» criado.', ['n' => $tipo->nome]),
            $empresaId,
            DocumentoTipo::class,
            (int) $tipo->id,
        );

        return redirect()
            ->to(tenant_admin_route('documento-tipos.index'))
            ->with('status', __('Tipo de documento criado.'));
    }

    public function edit(Empresa $empresa, DocumentoTipo $documento_tipo): View
    {
        $documentoTipo = $documento_tipo;

        return view('admin.documento-tipos.edit', compact('documentoTipo'));
    }

    public function update(Request $request, Empresa $empresa, DocumentoTipo $documento_tipo): RedirectResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:64',
                Rule::unique('documento_tipos', 'codigo')->where('empresa_id', $empresaId)->ignore($documento_tipo->id),
            ],
            'nome' => ['required', 'string', 'max:500'],
            'auto_gerado' => ['nullable', 'boolean'],
            'modelo_slug' => ['nullable', 'string', 'max:128'],
        ]);

        $documento_tipo->update([
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'auto_gerado' => $request->boolean('auto_gerado'),
            'modelo_slug' => $data['modelo_slug'] ?? null,
        ]);

        return redirect()
            ->to(tenant_admin_route('documento-tipos.index'))
            ->with('status', __('Tipo de documento atualizado.'));
    }

    public function destroy(Request $request, Empresa $empresa, DocumentoTipo $documento_tipo): RedirectResponse
    {
        if (ProcessoDocumento::query()->where('documento_tipo_id', $documento_tipo->id)->exists()) {
            return redirect()
                ->to(tenant_admin_route('documento-tipos.index'))
                ->withErrors(['delete' => __('Este tipo está em uso em processos. Não é possível excluir.')]);
        }

        if ($documento_tipo->tipoProcessos()->exists()) {
            $documento_tipo->tipoProcessos()->detach();
        }

        $nome = $documento_tipo->nome;
        $did = (int) $documento_tipo->id;
        $documento_tipo->delete();

        $this->activityLog->log(
            'documento_tipo_deleted',
            __('Tipo de documento «:n» removido.', ['n' => $nome]),
            TenantEmpresaContext::empresaId($request),
            DocumentoTipo::class,
            $did,
        );

        return redirect()
            ->to(tenant_admin_route('documento-tipos.index'))
            ->with('status', __('Tipo removido.'));
    }
}
