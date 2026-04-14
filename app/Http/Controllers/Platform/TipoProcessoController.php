<?php

namespace App\Http\Controllers\Platform;

use App\Enums\TipoProcessoCategoria;
use App\Http\Controllers\Controller;
use App\Models\PlatformTipoProcesso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}

