<?php

namespace App\Http\Controllers\Platform;

use App\Enums\TipoProcessoCategoria;
use App\Http\Controllers\Controller;
use App\Models\PlatformTipoProcesso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipoProcessoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = PlatformTipoProcesso::query()
            ->orderBy('ordem')
            ->orderBy('nome');

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('categoria', 'like', $termo);
            });
        }

        $tipos = $query->get();
        $categorias = TipoProcessoCategoria::cases();

        return view('platform.cadastros.tipos-processo.index', compact('tipos', 'q', 'categorias'));
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

        return redirect()
            ->route('platform.cadastros.tipos-processo.edit', $tipo)
            ->with('status', __('Tipo criado.'));
    }

    public function edit(PlatformTipoProcesso $tipo_processo): View
    {
        $categorias = TipoProcessoCategoria::cases();
        $tipo = $tipo_processo;

        return view('platform.cadastros.tipos-processo.edit', compact('tipo', 'categorias'));
    }

    public function update(Request $request, PlatformTipoProcesso $tipo_processo): RedirectResponse
    {
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

        return redirect()
            ->route('platform.cadastros.tipos-processo.index')
            ->with('status', __('Tipo atualizado.'));
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
        ]);

        $action = (string) $data['action'];
        $ids = array_values(array_unique(array_map('intval', $data['ids'] ?? [])));
        $q = trim((string) ($data['q'] ?? ''));

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
                ->route('platform.cadastros.tipos-processo.index', array_filter(['q' => $q]))
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
                ->route('platform.cadastros.tipos-processo.index', array_filter(['q' => $q]))
                ->with('status', trans_choice('{1} :count item ativado.|[2,*] :count itens ativados.', $affected, ['count' => $affected]));
        }

        if ($action === 'deactivate_selected' || $action === 'deactivate_all') {
            $affected = $query->update(['ativo' => false, 'updated_at' => now()]);
            return redirect()
                ->route('platform.cadastros.tipos-processo.index', array_filter(['q' => $q]))
                ->with('status', trans_choice('{1} :count item desativado.|[2,*] :count itens desativados.', $affected, ['count' => $affected]));
        }

        // delete_selected
        DB::transaction(function () use ($query, &$affected) {
            $affected = (int) $query->count();
            $query->delete();
        });

        return redirect()
            ->route('platform.cadastros.tipos-processo.index', array_filter(['q' => $q]))
            ->with('status', trans_choice('{1} :count item excluído permanentemente.|[2,*] :count itens excluídos permanentemente.', $affected, ['count' => $affected]));
    }
}

