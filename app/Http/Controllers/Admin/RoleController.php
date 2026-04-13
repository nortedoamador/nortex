<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /** @var list<string> */
    private const RESERVED_SLUGS = ['administrador', 'operador', 'instrutor', 'financeiro', 'cliente'];

    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $papeis = Role::query()
            ->where('empresa_id', $empresaId)
            ->withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('papeis'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Role::class);

        $permissoes = Permission::query()->orderBy('name')->get();

        return view('admin.roles.create', compact('permissoes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $empresaId = TenantEmpresaContext::empresaId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'slug')->where('empresa_id', $empresaId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        if (in_array($data['slug'], self::RESERVED_SLUGS, true)) {
            return back()->withErrors(['slug' => __('Este identificador é reservado pelo sistema.')])->withInput();
        }

        $role = Role::query()->create([
            'empresa_id' => $empresaId,
            'slug' => $data['slug'],
            'name' => $data['name'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['permissions'] ?? [])));
        $role->permissions()->sync($ids);

        $this->activityLog->log(
            'role_created',
            __(':actor criou o papel «:nome».', ['actor' => $request->user()->name, 'nome' => $role->name]),
            $empresaId,
            Role::class,
            (int) $role->id,
            ['slug' => $role->slug, 'permissions' => $ids],
        );

        return redirect()
            ->to(tenant_admin_route('roles.index'))
            ->with('status', __('Papel criado.'));
    }

    public function edit(Request $request, Empresa $empresa, Role $papel): View
    {
        $this->authorize('update', $papel);

        $permissoes = Permission::query()->orderBy('name')->get();
        $papel->load('permissions');
        $reservado = in_array($papel->slug, self::RESERVED_SLUGS, true);

        return view('admin.roles.edit', [
            'papel' => $papel,
            'permissoes' => $permissoes,
            'reservado' => $reservado,
        ]);
    }

    public function update(Request $request, Empresa $empresa, Role $papel): RedirectResponse
    {
        $this->authorize('update', $papel);

        $empresaId = TenantEmpresaContext::empresaId($request);
        $reservado = in_array($papel->slug, self::RESERVED_SLUGS, true);

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        if (! $reservado) {
            $rules['slug'] = [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'slug')->where('empresa_id', $empresaId)->ignore($papel->id),
            ];
        }

        $data = $request->validate($rules);

        if (! $reservado && in_array($data['slug'], self::RESERVED_SLUGS, true)) {
            return back()->withErrors(['slug' => __('Este identificador é reservado pelo sistema.')])->withInput();
        }

        $attrs = ['name' => $data['name']];
        if (! $reservado) {
            $attrs['slug'] = $data['slug'];
        }
        $papel->update($attrs);

        $ids = array_values(array_unique(array_map('intval', $data['permissions'] ?? [])));
        $papel->permissions()->sync($ids);

        $this->activityLog->log(
            'role_updated',
            __(':actor atualizou o papel «:nome».', ['actor' => $request->user()->name, 'nome' => $papel->name]),
            $empresaId,
            Role::class,
            (int) $papel->id,
            ['permissions' => $ids],
        );

        return redirect()
            ->to(tenant_admin_route('roles.index'))
            ->with('status', __('Papel atualizado.'));
    }

    public function destroy(Request $request, Empresa $empresa, Role $papel): RedirectResponse
    {
        $this->authorize('delete', $papel);

        if (in_array($papel->slug, self::RESERVED_SLUGS, true)) {
            return redirect()
                ->to(tenant_admin_route('roles.index'))
                ->withErrors(['delete' => __('Não é possível remover papéis padrão do sistema.')]);
        }

        if ($papel->users()->exists()) {
            return redirect()
                ->to(tenant_admin_route('roles.index'))
                ->withErrors(['delete' => __('Remova o papel dos usuários antes de excluí-lo.')]);
        }

        $nome = $papel->name;
        $id = (int) $papel->id;
        $papel->permissions()->detach();
        $papel->delete();

        $this->activityLog->log(
            'role_deleted',
            __(':actor removeu o papel «:nome».', ['actor' => $request->user()->name, 'nome' => $nome]),
            TenantEmpresaContext::empresaId($request),
            Role::class,
            $id,
            null,
        );

        return redirect()
            ->to(tenant_admin_route('roles.index'))
            ->with('status', __('Papel removido.'));
    }
}
