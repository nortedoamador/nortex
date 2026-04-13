<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAnexoTipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnexoTipoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = PlatformAnexoTipo::query()
            ->orderBy('ordem')
            ->orderBy('nome');

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo);
            });
        }

        $tipos = $query->paginate(30)->withQueryString();

        return view('platform.cadastros.anexo-tipos.index', compact('tipos', 'q'));
    }

    public function create(): View
    {
        return view('platform.cadastros.anexo-tipos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_anexo_tipos', 'slug')],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'max_size_mb' => ['required', 'integer', 'min:1', 'max:2048'],
            'allowed_mime_types' => ['nullable', 'string', 'max:2000'],
            'allowed_extensions' => ['nullable', 'string', 'max:2000'],
            'is_multiple' => ['nullable', 'boolean'],
        ]);

        $tipo = PlatformAnexoTipo::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
            'max_size_mb' => (int) $data['max_size_mb'],
            'allowed_mime_types' => $this->csvToList($data['allowed_mime_types'] ?? null),
            'allowed_extensions' => $this->csvToList($data['allowed_extensions'] ?? null),
            'is_multiple' => $request->boolean('is_multiple', true),
        ]);

        return redirect()
            ->route('platform.cadastros.anexo-tipos.edit', $tipo)
            ->with('status', __('Tipo criado.'));
    }

    public function edit(PlatformAnexoTipo $anexo_tipo): View
    {
        $tipo = $anexo_tipo;

        return view('platform.cadastros.anexo-tipos.edit', compact('tipo'));
    }

    public function update(Request $request, PlatformAnexoTipo $anexo_tipo): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:128', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('platform_anexo_tipos', 'slug')->ignore($anexo_tipo->id)],
            'ativo' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'max_size_mb' => ['required', 'integer', 'min:1', 'max:2048'],
            'allowed_mime_types' => ['nullable', 'string', 'max:2000'],
            'allowed_extensions' => ['nullable', 'string', 'max:2000'],
            'is_multiple' => ['nullable', 'boolean'],
        ]);

        $anexo_tipo->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'ativo' => $request->boolean('ativo', true),
            'ordem' => (int) ($data['ordem'] ?? 0),
            'max_size_mb' => (int) $data['max_size_mb'],
            'allowed_mime_types' => $this->csvToList($data['allowed_mime_types'] ?? null),
            'allowed_extensions' => $this->csvToList($data['allowed_extensions'] ?? null),
            'is_multiple' => $request->boolean('is_multiple', true),
        ]);

        return redirect()
            ->route('platform.cadastros.anexo-tipos.index')
            ->with('status', __('Tipo atualizado.'));
    }

    /** @return list<string>|null */
    private function csvToList(?string $csv): ?array
    {
        $csv = trim((string) $csv);
        if ($csv === '') {
            return null;
        }

        $items = array_values(array_filter(array_map(fn ($v) => trim((string) $v), preg_split('/[,\\n]+/', $csv) ?: [])));
        $items = array_values(array_unique(array_filter($items, fn ($v) => $v !== '')));

        return $items === [] ? null : $items;
    }
}

