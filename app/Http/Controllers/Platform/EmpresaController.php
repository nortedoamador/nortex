<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Services\EmpresaRbacService;
use App\Services\StripeBillingSyncService;
use App\Support\BrasilEstados;
use App\Support\DocumentoBrasil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    public function __construct(
        private EmpresaRbacService $empresaRbac,
        private StripeBillingSyncService $stripeBilling,
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
        $ufs = BrasilEstados::options();

        return view('platform.empresas.create', compact('ufs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEmpresaPlataforma($request, null);

        $empresa = Empresa::query()->create([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'email_contato' => $data['email_contato'],
            'telefone' => $data['telefone'],
            'uf' => $data['uf'],
            'cidade' => $data['cidade'],
            'cep' => $data['cep'],
            'endereco' => $data['endereco'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'],
            'bairro' => $data['bairro'],
            'ativo' => $request->boolean('ativo', true),
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('empresas/'.$empresa->id, 'public');
            $empresa->logo_path = $path;
            $empresa->save();
        }

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
        $ufs = BrasilEstados::options();

        return view('platform.empresas.edit', compact('empresa', 'ufs'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $data = $this->validateEmpresaPlataforma($request, $empresa);

        $acessoAte = $data['acesso_plataforma_ate'] ?? null;
        if (is_string($acessoAte) && trim($acessoAte) === '') {
            $acessoAte = null;
        }

        $empresa->fill([
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'email_contato' => $data['email_contato'],
            'telefone' => $data['telefone'],
            'uf' => $data['uf'],
            'cidade' => $data['cidade'],
            'cep' => $data['cep'],
            'endereco' => $data['endereco'],
            'numero' => $data['numero'],
            'complemento' => $data['complemento'],
            'bairro' => $data['bairro'],
            'ativo' => $request->boolean('ativo', true),
            'acesso_plataforma_ate' => $acessoAte,
        ]);

        if ($request->hasFile('logo')) {
            if ($empresa->logo_path && Storage::disk('public')->exists($empresa->logo_path)) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $path = $request->file('logo')->store('empresas/'.$empresa->id, 'public');
            $empresa->logo_path = $path;
        }

        $empresa->save();

        $flashPlan = $this->aplicarAlteracaoPlanoStripe($request, $empresa);

        return redirect()
            ->route('platform.empresas.index')
            ->with('status', $flashPlan !== null ? $flashPlan : __('Empresa atualizada.'));
    }

    private function aplicarAlteracaoPlanoStripe(Request $request, Empresa $empresa): ?string
    {
        $ref = (string) $request->input('stripe_plano_referencia', 'manter');
        if ($ref === 'manter' || $ref === '') {
            return null;
        }

        $basic = config('services.stripe.price_basic');
        $full = config('services.stripe.price_full');
        $priceId = $ref === 'completo' ? (is_string($full) ? trim($full) : '') : (is_string($basic) ? trim($basic) : '');

        if ($priceId === '') {
            return __('Plano não alterado: configure STRIPE_PRICE_BASIC e STRIPE_PRICE_FULL no ambiente.');
        }

        $result = $this->stripeBilling->alterarPrecoSubscricaoSePossivel($empresa, $priceId);
        $empresa->refresh();

        if (! $result['ok']) {
            return __('Empresa guardada, mas o plano Stripe não foi atualizado: :m', ['m' => (string) ($result['message'] ?? '')]);
        }

        $extra = $result['message'];

        return $extra !== null && $extra !== ''
            ? __('Plano atualizado. :m', ['m' => $extra])
            : __('Plano e assinatura Stripe atualizados.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEmpresaPlataforma(Request $request, ?Empresa $empresa): array
    {
        $slugRule = Rule::unique('empresas', 'slug');
        if ($empresa) {
            $slugRule = $slugRule->ignore($empresa->id);
        }

        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'cnpj' => [
                'nullable',
                'string',
                'max:18',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }

                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (strlen($digits) !== 14 || ! DocumentoBrasil::cnpjValido($digits)) {
                        $fail(__('CNPJ inválido.'));
                    }
                },
            ],
            'email_contato' => ['nullable', 'string', 'email', 'max:255'],
            'telefone' => [
                'nullable',
                'string',
                'max:32',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }

                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (! in_array(strlen($digits), [10, 11], true)) {
                        $fail(__('Telefone inválido.'));
                    }
                },
            ],
            'uf' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $v = is_string($value) ? trim($value) : '';
                    if ($v === '') {
                        return;
                    }
                    if (strlen($v) !== 2 || ! array_key_exists($v, BrasilEstados::options())) {
                        $fail(__('UF inválida.'));
                    }
                },
            ],
            'cidade' => ['nullable', 'string', 'max:120'],
            'cep' => [
                'nullable',
                'string',
                'max:12',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $raw = trim((string) $value);
                    if ($raw === '') {
                        return;
                    }
                    $digits = DocumentoBrasil::apenasDigitos($raw);
                    if (strlen($digits) !== 8) {
                        $fail(__('CEP inválido.'));
                    }
                },
            ],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:32'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'ativo' => ['nullable', 'boolean'],
            'acesso_plataforma_ate' => ['nullable', 'date'],
        ];

        if ($empresa) {
            $rules['stripe_plano_referencia'] = [
                'nullable',
                Rule::in(['manter', 'essencial', 'completo']),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $v = is_string($value) && $value !== '' ? $value : 'manter';
                    if ($v === 'manter') {
                        return;
                    }
                    $basic = config('services.stripe.price_basic');
                    $full = config('services.stripe.price_full');
                    if ($v === 'essencial' && (! is_string($basic) || trim($basic) === '')) {
                        $fail(__('STRIPE_PRICE_BASIC não está configurado no servidor.'));
                    }
                    if ($v === 'completo' && (! is_string($full) || trim($full) === '')) {
                        $fail(__('STRIPE_PRICE_FULL não está configurado no servidor.'));
                    }
                },
            ];
        }

        $data = $request->validate($rules);

        $cnpj = trim((string) ($data['cnpj'] ?? ''));
        $telefone = trim((string) ($data['telefone'] ?? ''));

        $ufRaw = isset($data['uf']) ? strtoupper(trim((string) $data['uf'])) : '';
        $cidadeRaw = isset($data['cidade']) ? trim((string) $data['cidade']) : '';
        $nomeFantasiaRaw = isset($data['nome_fantasia']) ? trim((string) $data['nome_fantasia']) : '';
        $cepRaw = isset($data['cep']) ? trim((string) $data['cep']) : '';
        $enderecoRaw = isset($data['endereco']) ? trim((string) $data['endereco']) : '';
        $numeroRaw = isset($data['numero']) ? trim((string) $data['numero']) : '';
        $complementoRaw = isset($data['complemento']) ? trim((string) $data['complemento']) : '';
        $bairroRaw = isset($data['bairro']) ? trim((string) $data['bairro']) : '';

        return [
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'nome_fantasia' => $nomeFantasiaRaw !== '' ? $nomeFantasiaRaw : null,
            'cnpj' => $cnpj !== '' ? DocumentoBrasil::formatarCnpj($cnpj) : null,
            'email_contato' => isset($data['email_contato']) && trim((string) $data['email_contato']) !== '' ? trim((string) $data['email_contato']) : null,
            'telefone' => $telefone !== '' ? self::formatarTelefoneBr($telefone) : null,
            'uf' => $ufRaw !== '' ? $ufRaw : null,
            'cidade' => $cidadeRaw !== '' ? $cidadeRaw : null,
            'cep' => $cepRaw !== '' ? self::formatarCepBr($cepRaw) : null,
            'endereco' => $enderecoRaw !== '' ? $enderecoRaw : null,
            'numero' => $numeroRaw !== '' ? $numeroRaw : null,
            'complemento' => $complementoRaw !== '' ? $complementoRaw : null,
            'bairro' => $bairroRaw !== '' ? $bairroRaw : null,
            'acesso_plataforma_ate' => $data['acesso_plataforma_ate'] ?? null,
        ];
    }

    private static function formatarTelefoneBr(string $valor): string
    {
        $digits = DocumentoBrasil::apenasDigitos($valor);

        if (strlen($digits) === 11) {
            return '('.substr($digits, 0, 2).') '.substr($digits, 2, 5).'-'.substr($digits, 7, 4);
        }

        if (strlen($digits) === 10) {
            return '('.substr($digits, 0, 2).') '.substr($digits, 2, 4).'-'.substr($digits, 6, 4);
        }

        return $valor;
    }

    private static function formatarCepBr(string $valor): string
    {
        $digits = DocumentoBrasil::apenasDigitos($valor);
        if (strlen($digits) === 8) {
            return substr($digits, 0, 5).'-'.substr($digits, 5);
        }

        return $valor;
    }
}
