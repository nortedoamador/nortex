<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\ProcessoDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChecklistDocumentosController extends Controller
{
    private function resolveChecklistEmpresaIdOrNull(): ?int
    {
        $configured = config('nortex.platform_checklist_empresa_id');
        if ($configured !== null && $configured !== '' && (int) $configured > 0) {
            return (int) $configured;
        }

        $id = Empresa::query()->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }

    private function resolveChecklistEmpresaId(): int
    {
        $id = $this->resolveChecklistEmpresaIdOrNull();
        if ($id === null) {
            abort(503, 'NORTEX_PLATFORM_CHECKLIST_EMPRESA_ID');
        }

        return $id;
    }

    private function documentoTipoDaReferencia(int $id): DocumentoTipo
    {
        $empresaId = $this->resolveChecklistEmpresaId();

        return DocumentoTipo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function index(Request $request): View
    {
        $empresaId = $this->resolveChecklistEmpresaIdOrNull();
        if ($empresaId === null) {
            return view('platform.cadastros.checklist-documentos.unavailable');
        }

        $checklistEmpresa = Empresa::query()->find($empresaId);

        $q = trim((string) $request->query('q', ''));

        $allowedSorts = ['codigo', 'nome', 'modelo_slug', 'auto_gerado'];
        $sort = (string) $request->query('sort', 'nome');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'nome';
        }
        $direction = strtolower((string) $request->query('direction', 'asc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = DocumentoTipo::query()
            ->withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaId)
            ->orderBy($sort, $direction);

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('codigo', 'like', $termo)
                    ->orWhere('modelo_slug', 'like', $termo);
            });
        }

        $tipos = $query->get();

        return view('platform.cadastros.checklist-documentos.index', compact(
            'tipos',
            'q',
            'checklistEmpresa',
            'sort',
            'direction',
        ));
    }

    public function create(): View
    {
        $empresaId = $this->resolveChecklistEmpresaIdOrNull();
        if ($empresaId === null) {
            return view('platform.cadastros.checklist-documentos.unavailable');
        }

        $checklistEmpresa = Empresa::query()->find($empresaId);

        return view('platform.cadastros.checklist-documentos.create', compact('checklistEmpresa'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = $this->resolveChecklistEmpresaId();

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

        return redirect()
            ->route('platform.cadastros.checklist-documentos.edit', $tipo->id)
            ->with('status', __('Tipo de documento criado.'));
    }

    public function edit(int $documento_tipo): View
    {
        $empresaId = $this->resolveChecklistEmpresaIdOrNull();
        if ($empresaId === null) {
            return view('platform.cadastros.checklist-documentos.unavailable');
        }

        $documentoTipo = $this->documentoTipoDaReferencia($documento_tipo);
        $checklistEmpresa = Empresa::query()->find($empresaId);

        return view('platform.cadastros.checklist-documentos.edit', compact('documentoTipo', 'checklistEmpresa'));
    }

    public function update(Request $request, int $documento_tipo): RedirectResponse
    {
        $empresaId = $this->resolveChecklistEmpresaId();
        $documentoTipo = $this->documentoTipoDaReferencia($documento_tipo);

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:64',
                Rule::unique('documento_tipos', 'codigo')->where('empresa_id', $empresaId)->ignore($documentoTipo->id),
            ],
            'nome' => ['required', 'string', 'max:500'],
            'auto_gerado' => ['nullable', 'boolean'],
            'modelo_slug' => ['nullable', 'string', 'max:128'],
        ]);

        $documentoTipo->update([
            'codigo' => $data['codigo'],
            'nome' => $data['nome'],
            'auto_gerado' => $request->boolean('auto_gerado'),
            'modelo_slug' => $data['modelo_slug'] ?? null,
        ]);

        return redirect()
            ->route('platform.cadastros.checklist-documentos.index')
            ->with('status', __('Tipo de documento atualizado.'));
    }

    public function destroy(int $documento_tipo): RedirectResponse
    {
        $documentoTipo = $this->documentoTipoDaReferencia($documento_tipo);

        if (ProcessoDocumento::query()->where('documento_tipo_id', $documentoTipo->id)->exists()) {
            return redirect()
                ->route('platform.cadastros.checklist-documentos.index')
                ->withErrors(['delete' => __('Este tipo está em uso em processos. Não é possível excluir.')]);
        }

        if ($documentoTipo->tipoProcessos()->exists()) {
            $documentoTipo->tipoProcessos()->detach();
        }

        $documentoTipo->delete();

        return redirect()
            ->route('platform.cadastros.checklist-documentos.index')
            ->with('status', __('Tipo removido.'));
    }
}
