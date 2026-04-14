<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformTipoServico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TipoServicoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = PlatformTipoServico::query()
            ->orderBy('ordem')
            ->orderBy('nome');

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo);
            });
        }

        $tipos = $query->get();

        return view('platform.cadastros.tipos-servico.index', compact('tipos', 'q'));
    }

    public function create(): View
    {
        return view('platform.cadastros.tipos-servico.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_tipo_servicos', 'slug')],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $tipo = PlatformTipoServico::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
        ]);

        return redirect()
            ->route('platform.cadastros.tipos-servico.edit', $tipo)
            ->with('status', __('Tipo criado.'));
    }

    public function edit(PlatformTipoServico $tipo_servico): View
    {
        $tipo = $tipo_servico;

        return view('platform.cadastros.tipos-servico.edit', compact('tipo'));
    }

    public function update(Request $request, PlatformTipoServico $tipo_servico): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_tipo_servicos', 'slug')->ignore($tipo_servico->id)],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $tipo_servico->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
        ]);

        return redirect()
            ->route('platform.cadastros.tipos-servico.index')
            ->with('status', __('Tipo atualizado.'));
    }
}

