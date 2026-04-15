<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Services\EmpresaRbacService;
use App\Support\BrazilStates;
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
            ->withCount(['users', 'processos'])
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
        $ufs = BrazilStates::labels();

        return view('platform.empresas.create', compact('ufs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('empresas', 'slug')],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'uf' => ['nullable', 'string', Rule::in(BrazilStates::codes())],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $empresa = Empresa::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'cnpj' => $data['cnpj'] ?? null,
            'uf' => $data['uf'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        $this->empresaRbac->bootstrapEmpresa($empresa);

        return redirect()
            ->route('platform.empresas.index')
            ->with('status', __('Empresa criada e papéis padrão gerados.'));
    }

    public function show(Empresa $empresa): View
    {
        $empresa->loadCount(['users', 'roles']);

        $totClientes = Cliente::query()->where('empresa_id', $empresa->id)->count();
        $totProcessos = Processo::query()->where('empresa_id', $empresa->id)->count();
        $totTiposProcesso = TipoProcesso::query()->where('empresa_id', $empresa->id)->count();
        $totTiposDocumento = DocumentoTipo::query()->where('empresa_id', $empresa->id)->count();

        return view('platform.empresas.show', compact(
            'empresa',
            'totClientes',
            'totProcessos',
            'totTiposProcesso',
            'totTiposDocumento',
        ));
    }

    public function edit(Empresa $empresa): View
    {
        $ufs = BrazilStates::labels();

        return view('platform.empresas.edit', compact('empresa', 'ufs'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('empresas', 'slug')->ignore($empresa->id)],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'uf' => ['nullable', 'string', Rule::in(BrazilStates::codes())],
            'ativo' => ['nullable', 'boolean'],
            'acesso_plataforma_ate' => ['nullable', 'date'],
        ]);

        $acessoAte = $data['acesso_plataforma_ate'] ?? null;
        if (is_string($acessoAte) && trim($acessoAte) === '') {
            $acessoAte = null;
        }

        $empresa->update([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'cnpj' => $data['cnpj'] ?? null,
            'uf' => $data['uf'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'acesso_plataforma_ate' => $acessoAte,
        ]);

        return redirect()
            ->route('platform.empresas.index')
            ->with('status', __('Empresa atualizada.'));
    }
}
