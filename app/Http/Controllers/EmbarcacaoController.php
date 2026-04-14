<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmbarcacaoRequest;
use App\Http\Requests\StoreEmbarcacaoFichaAnexosRequest;
use App\Http\Requests\StoreEmbarcacaoFotosCadastroRequest;
use App\Http\Requests\UpdateEmbarcacaoRequest;
use App\Enums\TipoProcessoCategoria;
use App\Models\Cliente;
use App\Models\Embarcacao;
use App\Models\EmbarcacaoAnexo;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use App\Services\EmbarcacaoAnexoService;
use App\Support\EmbarcacaoFotosGaleria;
use App\Support\EmbarcacaoTiposAnexo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class EmbarcacaoController extends Controller
{
    public function __construct(
        private EmbarcacaoAnexoService $anexoService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Embarcacao::class);

        $busca = trim((string) $request->query('q', ''));
        $tipo = trim((string) $request->query('tipo', ''));
        $atividade = trim((string) $request->query('atividade', ''));
        $construtor = trim((string) $request->query('construtor', ''));
        $anoConstrucao = trim((string) $request->query('ano_construcao', ''));
        $numeroMotor = trim((string) $request->query('numero_motor', ''));
        $perPage = (int) $request->query('per_page', 5);
        $allowedPerPage = [5, 10, 20, 50];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }

        $query = Embarcacao::query()
            ->with('cliente')
            ->orderByDesc('updated_at');

        if ($busca !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $busca).'%';
            $digits = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($termo, $digits) {
                $q->where('nome', 'like', $termo)
                    ->orWhere('inscricao', 'like', $termo)
                    ->orWhere('cpf', 'like', $termo)
                    ->orWhereHas('cliente', function ($c) use ($termo, $digits) {
                        $c->where('nome', 'like', $termo)
                            ->orWhere('cpf', 'like', $termo);
                        if ($digits !== '') {
                            $c->orWhereRaw(
                                'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,\'.\',\'\'),\'-\',\'\'),\'/\',\'\'),\' \',\'\'),\'(\',\'\'),\')\',\'\') like ?',
                                ['%'.$digits.'%']
                            );
                        }
                    });
                if ($digits !== '') {
                    $q->orWhereRaw(
                        'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,\'.\',\'\'),\'-\',\'\'),\'/\',\'\'),\' \',\'\'),\'(\',\'\'),\')\',\'\') like ?',
                        ['%'.$digits.'%']
                    );
                }
            });
        }

        if ($tipo !== '') {
            $query->where('tipo', $tipo);
        }
        if ($atividade !== '') {
            $query->where('atividade', $atividade);
        }
        if ($construtor !== '') {
            $t = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $construtor).'%';
            $query->where('construtor', 'like', $t);
        }
        if ($anoConstrucao !== '' && ctype_digit($anoConstrucao)) {
            $query->where('ano_construcao', (int) $anoConstrucao);
        }
        if ($numeroMotor !== '') {
            $t = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $numeroMotor).'%';
            $query->where('numero_motor', 'like', $t);
        }

        $embarcacoes = $query->paginate($perPage)->withQueryString();

        $clientes = $request->user()->can('create', Embarcacao::class)
            ? Cliente::query()->orderBy('nome')->get()
            : collect();

        $tipos = [
            'Balsa',
            'Barcaça',
            'Batelão',
            'Bote',
            'Caiaque',
            'Canoa',
            'Chata',
            'Draga',
            'Empurrador',
            'Escuna',
            'Flutuante',
            'Hidroavião',
            'Iate',
            'Jangada',
            'Jet Boat',
            'Lancha',
            'Laser',
            'Moto-Aquática/similar',
            'Multicasco (Catamarã, Trimarã, Tetramarã, etc)',
            'Outros',
            'Pesqueiro',
            'Pesquisa',
            'Petroleiro',
            'Plataforma Fixa',
            'Rebocador',
            'Traineira',
        ];
        $atividades = [
            'Esporte e Recreio',
            'Transporte de Passageiros',
            'Transporte de Carga',
            'Transporte de Passageiros e Carga',
        ];

        $sugestoesBuscaEmbarcacao = self::buildSugestoesBuscaEmbarcacao();
        $construtoresOptions = Embarcacao::query()
            ->whereNotNull('construtor')
            ->where('construtor', '!=', '')
            ->distinct()
            ->orderBy('construtor')
            ->pluck('construtor')
            ->map(static fn ($v) => is_string($v) ? trim($v) : '')
            ->filter()
            ->unique()
            ->sort(static fn (string $a, string $b): int => strnatcasecmp($a, $b))
            ->values()
            ->all();

        if ($request->wantsJson()) {
            $countText = $busca !== '' || $tipo !== '' || $atividade !== '' || $construtor !== '' || $anoConstrucao !== '' || $numeroMotor !== ''
                ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()])
                : trans_choice('{0} Nenhuma embarcação cadastrada|{1} :count cadastrada|[2,*] :count cadastradas', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()]);

            return response()->json([
                'count_text' => $countText,
                'tags_html' => view('embarcacoes.partials.index-tags', compact(
                    'busca',
                    'perPage',
                    'tipo',
                    'atividade',
                    'construtor',
                    'anoConstrucao',
                    'numeroMotor',
                ))->render(),
                'list_html' => view('embarcacoes.partials.index-list', compact(
                    'embarcacoes',
                    'busca',
                    'tipo',
                    'atividade',
                    'construtor',
                    'anoConstrucao',
                    'numeroMotor',
                ))->render(),
                'pagination_html' => $embarcacoes->hasPages() ? (string) $embarcacoes->links() : '',
            ]);
        }

        return view('embarcacoes.index', compact(
            'embarcacoes',
            'clientes',
            'busca',
            'tipo',
            'atividade',
            'construtor',
            'anoConstrucao',
            'numeroMotor',
            'perPage',
            'tipos',
            'atividades',
            'sugestoesBuscaEmbarcacao',
            'construtoresOptions',
        ));
    }

    /**
     * @return list<array{kind: string, label: string, value: string}>
     */
    private static function buildSugestoesBuscaEmbarcacao(): array
    {
        $out = [];

        Cliente::query()
            ->orderBy('nome')
            ->limit(400)
            ->get()
            ->each(function (Cliente $c) use (&$out) {
                $doc = $c->documentoFormatado() ?? (string) $c->cpf;
                $label = __('Cliente').': '.$c->nome.($doc !== '' ? ' — '.$doc : '');
                $out[] = [
                    'kind' => 'cliente',
                    'label' => $label,
                    'value' => $c->nome,
                ];
            });

        foreach (Embarcacao::query()
            ->whereNotNull('nome')
            ->where('nome', '!=', '')
            ->distinct()
            ->orderBy('nome')
            ->limit(300)
            ->pluck('nome') as $nome) {
            $nome = (string) $nome;
            if ($nome === '') {
                continue;
            }
            $out[] = [
                'kind' => 'embarcacao',
                'label' => __('Embarcação').': '.$nome,
                'value' => $nome,
            ];
        }

        foreach (Embarcacao::query()
            ->whereNotNull('inscricao')
            ->where('inscricao', '!=', '')
            ->distinct()
            ->orderBy('inscricao')
            ->limit(200)
            ->pluck('inscricao') as $ins) {
            $ins = (string) $ins;
            if ($ins === '') {
                continue;
            }
            $out[] = [
                'kind' => 'inscricao',
                'label' => __('Inscrição').': '.$ins,
                'value' => $ins,
            ];
        }

        return $out;
    }

    public function create(): View
    {
        $this->authorize('create', Embarcacao::class);

        return view('embarcacoes.create', [
            'clientes' => Cliente::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(StoreEmbarcacaoRequest $request): RedirectResponse
    {
        $this->authorize('create', Embarcacao::class);

        $data = $request->safe()->except(['foto_traves', 'foto_popa', 'fotos_outras']);
        $motoresNorm = Embarcacao::normalizeMotoresPayload($data['motores'] ?? null);
        $data['motores'] = $motoresNorm;
        $data = array_merge($data, Embarcacao::legacyAttributesFromMotores($motoresNorm));

        $embarcacao = Embarcacao::query()->create($data);

        $this->processarFotosCadastroEmbarcacao(
            $embarcacao,
            $request->file('foto_traves'),
            $request->file('foto_popa'),
            $request->file('fotos_outras', []),
            $request->input('fotos_outras_rotulo'),
        );

        return redirect()->route('embarcacoes.index')->with('status', 'Embarcação cadastrada.');
    }

    public function edit(Embarcacao $embarcacao): View
    {
        $this->authorize('update', $embarcacao);

        $embarcacao->load('anexos');

        return view('embarcacoes.edit', [
            'embarcacao' => $embarcacao,
            'clientes' => Cliente::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(UpdateEmbarcacaoRequest $request, Embarcacao $embarcacao): RedirectResponse
    {
        $this->authorize('update', $embarcacao);

        $data = $request->safe()->except(['foto_traves', 'foto_popa', 'fotos_outras']);
        $motoresNorm = Embarcacao::normalizeMotoresPayload($data['motores'] ?? null);
        $data['motores'] = $motoresNorm;
        $data = array_merge($data, Embarcacao::legacyAttributesFromMotores($motoresNorm));

        $embarcacao->update($data);

        $this->processarFotosCadastroEmbarcacao(
            $embarcacao,
            $request->file('foto_traves'),
            $request->file('foto_popa'),
            $request->file('fotos_outras', []),
            $request->input('fotos_outras_rotulo'),
        );

        return redirect()->route('embarcacoes.show', $embarcacao)->with('status', __('Embarcação atualizada.'));
    }

    public function show(Request $request, Embarcacao $embarcacao): View
    {
        $this->authorize('view', $embarcacao);

        $embarcacao->load([
            'cliente',
            'anexos',
            'processos' => function ($query) {
                $query->with(['tipoProcesso', 'tipoProcessoTenant', 'cliente'])
                    ->orderByDesc('updated_at');
            },
        ]);

        $fotosGaleriaItens = EmbarcacaoFotosGaleria::itensOrdenados($embarcacao);
        $fotosTotalGaleria = $fotosGaleriaItens->count();
        $fotosPorPagina = 6;
        $fotosPaginaAtual = LengthAwarePaginator::resolveCurrentPage('fotos_page');
        $fotosGaleriaPaginator = new LengthAwarePaginator(
            $fotosGaleriaItens->slice(($fotosPaginaAtual - 1) * $fotosPorPagina, $fotosPorPagina)->values(),
            $fotosTotalGaleria,
            $fotosPorPagina,
            $fotosPaginaAtual,
            [
                'path' => $request->url(),
                'pageName' => 'fotos_page',
            ]
        );
        $fotosGaleriaPaginator->withQueryString();

        $tiposProcessoModal = null;
        $clientesSuggestProcessoModal = null;
        $categoriasProcesso = [];
        $categoriaProcessoOld = null;
        $mostrarModalNovoProcesso = false;

        if ($request->user()->can('create', Processo::class)) {
            $empresaId = (int) $request->user()->empresa_id;
            $tiposProcessoModal = PlatformTipoProcesso::query()
                ->where('ativo', true)
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get();
            $tiposProcessoModal->load(['documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', $empresaId)->orderBy('documento_processo.ordem')]);

            $clientesSuggestProcessoModal = Cliente::query()
                ->orderBy('nome')
                ->get()
                ->filter(fn (Cliente $c) => filled($c->cpf))
                ->values()
                ->map(fn (Cliente $c) => [
                    'id' => $c->id,
                    'doc' => $c->documentoFormatado() ?? $c->cpf,
                    'docDigits' => preg_replace('/\D/', '', (string) $c->cpf),
                    'nome' => $c->nome,
                ]);

            $categoriasProcesso = TipoProcessoCategoria::cases();
            $mostrarModalNovoProcesso = $tiposProcessoModal->isNotEmpty();
        }

        return view('embarcacoes.show', compact(
            'embarcacao',
            'fotosGaleriaPaginator',
            'fotosTotalGaleria',
            'tiposProcessoModal',
            'clientesSuggestProcessoModal',
            'categoriasProcesso',
            'categoriaProcessoOld',
            'mostrarModalNovoProcesso',
        ));
    }

    public function storeFotosCadastro(StoreEmbarcacaoFotosCadastroRequest $request, Embarcacao $embarcacao): RedirectResponse
    {
        $outras = $request->file('fotos_outras', []);
        if (! is_array($outras)) {
            $outras = $outras instanceof UploadedFile ? [$outras] : [];
        }

        $n = $this->processarFotosCadastroEmbarcacao(
            $embarcacao,
            $request->file('foto_traves'),
            $request->file('foto_popa'),
            $outras,
            $request->input('fotos_outras_rotulo'),
        );

        if ($n === 0) {
            return back()->withErrors(['foto_traves' => __('Selecione pelo menos um ficheiro para enviar.')]);
        }

        return back()->with('status', $n === 1
            ? __('1 foto anexada.')
            : __(':count fotos anexadas.', ['count' => $n]));
    }

    public function storeAnexos(StoreEmbarcacaoFichaAnexosRequest $request, Embarcacao $embarcacao): RedirectResponse
    {
        $this->authorize('manage', $embarcacao);

        $n = $this->anexoService->armazenarVarios(
            $embarcacao,
            $request->file('arquivos', []),
            $request->validated('tipo_codigo'),
            $request->validated('platform_anexo_tipo_id'),
        );

        if ($n === 0) {
            return back()->withErrors(['arquivos' => 'Nenhum arquivo válido foi enviado.']);
        }

        return back()->with('status', $n === 1 ? '1 arquivo enviado.' : "{$n} arquivos enviados.");
    }

    public function destroyAnexo(EmbarcacaoAnexo $anexo): RedirectResponse
    {
        $embarcacao = $anexo->embarcacao;
        abort_unless($embarcacao, 404);

        $this->authorize('manage', $embarcacao);

        $this->anexoService->remover($anexo);

        return back()->with('status', 'Anexo removido.');
    }

    /**
     * @param  array<int, mixed>  $fotosOutras
     */
    private function processarFotosCadastroEmbarcacao(
        Embarcacao $embarcacao,
        ?UploadedFile $fotoTraves,
        ?UploadedFile $fotoPopa,
        array $fotosOutras,
        mixed $fotosOutrasRotulo = null,
    ): int {
        $n = 0;

        if ($fotoTraves instanceof UploadedFile && $fotoTraves->isValid()) {
            $n += $this->anexoService->armazenarVarios($embarcacao, [$fotoTraves], EmbarcacaoTiposAnexo::FOTO_TRAVES);
        }
        if ($fotoPopa instanceof UploadedFile && $fotoPopa->isValid()) {
            $n += $this->anexoService->armazenarVarios($embarcacao, [$fotoPopa], EmbarcacaoTiposAnexo::FOTO_POPA);
        }

        $outrasValidas = [];
        foreach ($fotosOutras as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $outrasValidas[] = $file;
            }
        }
        if ($outrasValidas !== []) {
            $rotulo = is_string($fotosOutrasRotulo) ? trim($fotosOutrasRotulo) : '';
            $rotulo = $rotulo !== '' ? $rotulo : null;
            $n += $this->anexoService->armazenarVarios($embarcacao, $outrasValidas, EmbarcacaoTiposAnexo::FOTO_OUTRAS, null, $rotulo);
        }

        return $n;
    }
}
