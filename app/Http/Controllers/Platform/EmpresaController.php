<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\EmpresaRbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    public function __construct(
        private EmpresaRbacService $empresaRbac,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = Empresa::query()
            ->withCount('users')
            ->orderBy('nome');

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('cnpj', 'like', $termo);
            });
        }

        $empresas = $query->paginate(20)->withQueryString();

        return view('platform.empresas.index', compact('empresas', 'q'));
    }

    public function create(): View
    {
        return view('platform.empresas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('empresas', 'slug')],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $empresa = Empresa::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'cnpj' => $data['cnpj'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        $this->empresaRbac->bootstrapEmpresa($empresa);

        return redirect()
            ->route('platform.empresas.index')
            ->with('status', __('Empresa criada e papéis padrão gerados.'));
    }

    public function edit(Empresa $empresa): View
    {
        return view('platform.empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('empresas', 'slug')->ignore($empresa->id)],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $empresa->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'cnpj' => $data['cnpj'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()
            ->route('platform.empresas.index')
            ->with('status', __('Empresa atualizada.'));
    }
}
