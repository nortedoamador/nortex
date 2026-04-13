<?php

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\RespondsForNorteXAuthSpa;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaProcessosDefaultsService;
use App\Services\EmpresaRbacService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use RespondsForNorteXAuthSpa;

    /**
     * Display the registration view.
     */
    public function create(Request $request): View|JsonResponse
    {
        if ($this->nxAuthSpa($request)) {
            return $this->nxAuthSpaFragment('auth.partials.register-inner', [], 'Criar conta');
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'empresa_nome' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $base = Str::slug($request->empresa_nome);
        $base = $base !== '' ? $base : 'empresa';
        $candidate = $base;
        $i = 0;
        while (Empresa::query()->where('slug', $candidate)->exists()) {
            $i++;
            $candidate = $base.'-'.$i;
        }
        $slug = $candidate;

        $empresa = Empresa::create([
            'nome' => $request->empresa_nome,
            'slug' => $slug,
            'ativo' => true,
        ]);

        $user = User::create([
            'empresa_id' => $empresa->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $rbac = app(EmpresaRbacService::class);
        $rbac->bootstrapEmpresa($empresa);
        $rbac->assignRole($user, 'administrador');

        event(new Registered($user));

        Auth::login($user);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        if ($this->nxAuthSpa($request)) {
            return response()->json([
                'redirect' => url()->to(route('dashboard', absolute: false)),
            ]);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
