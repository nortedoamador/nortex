<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHabilitacaoAnexosRequest;
use App\Http\Requests\StoreHabilitacaoRequest;
use App\Http\Requests\UpdateHabilitacaoRequest;
use App\Models\Cliente;
use App\Models\Habilitacao;
use App\Models\HabilitacaoAnexo;
use App\Services\HabilitacaoAnexoService;
use App\Support\ClienteCpfSuggest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HabilitacaoController extends Controller
{
    public function __construct(
        private HabilitacaoAnexoService $anexoService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Habilitacao::class);

        $busca = trim((string) $request->query('q', ''));
        $clienteBusca = trim((string) $request->query('cliente', ''));
        $categoria = trim((string) $request->query('categoria', ''));
        $jurisdicao = trim((string) $request->query('jurisdicao', ''));
        $vigencia = trim((string) $request->query('vigencia', ''));
        $perPage = (int) $request->query('per_page', 5);
        $allowedPerPage = [5, 10, 20, 50];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }

        $query = Habilitacao::query()
            ->with('cliente')
            ->orderByDesc('updated_at');

        if ($busca !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $busca).'%';
            $digits = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($termo, $digits) {
                $q->where('nome', 'like', $termo)
                    ->orWhere('numero_cha', 'like', $termo)
                    ->orWhere('categoria', 'like', $termo)
                    ->orWhere('jurisdicao', 'like', $termo)
                    ->orWhere('cpf', 'like', $termo);

                if ($digits !== '') {
                    $q->orWhereRaw('REPLACE(REPLACE(REPLACE(cpf, ".", ""), "-", ""), "/", "") like ?', ['%'.$digits.'%']);
                }

                $q->orWhereHas('cliente', fn ($c) => $c->where('nome', 'like', $termo));
            });
        }

        if ($clienteBusca !== '') {
            $t = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $clienteBusca).'%';
            $digitsCliente = preg_replace('/\D/', '', $clienteBusca);
            $query->whereHas('cliente', function ($c) use ($t, $digitsCliente) {
                $c->where('nome', 'like', $t)->orWhere('cpf', 'like', $t);
                if ($digitsCliente !== '') {
                    $c->orWhereRaw(
                        'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,\'.\',\'\'),\'-\',\'\'),\'/\',\'\'),\' \',\'\'),\'(\',\'\'),\')\',\'\') like ?',
                        ['%'.$digitsCliente.'%']
                    );
                }
            });
        }
        if ($categoria !== '') {
            $query->where('categoria', $categoria);
        }
        if ($jurisdicao !== '') {
            $query->where('jurisdicao', $jurisdicao);
        }
        if ($vigencia === 'vencida') {
            $query->whereNotNull('data_validade')->whereDate('data_validade', '<', now()->toDateString());
        } elseif ($vigencia === 'em_vigor') {
            $query->whereNotNull('data_validade')->whereDate('data_validade', '>=', now()->toDateString());
        }

        $habilitacoes = $query->paginate($perPage)->withQueryString();

        $clientes = $request->user()->can('create', Habilitacao::class)
            ? Cliente::query()->orderBy('nome')->get()
            : collect();

        $clientesSuggest = ClienteCpfSuggest::collection(
            Cliente::query()->orderBy('nome')->get()
        )->values();

        $categoriasCha = Habilitacao::CATEGORIAS_CHA;
        $jurisdicoesCha = Habilitacao::JURISDICOES;

        if ($request->wantsJson()) {
            $hasDrawerFiltros = $clienteBusca !== '' || $categoria !== '' || $jurisdicao !== '' || $vigencia !== '';
            $countText = $busca !== '' || $hasDrawerFiltros
                ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()])
                : trans_choice('{0} Nenhum cadastro de CHA|{1} :count cadastro|[2,*] :count cadastros', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()]);

            return response()->json([
                'count_text' => $countText,
                'tags_html' => view('habilitacoes.partials.index-tags', compact(
                    'clienteBusca',
                    'categoria',
                    'jurisdicao',
                    'vigencia',
                ))->render(),
                'list_html' => view('habilitacoes.partials.index-list', compact(
                    'habilitacoes',
                    'busca',
                    'clienteBusca',
                    'categoria',
                    'jurisdicao',
                    'vigencia',
                ))->render(),
                'pagination_html' => $habilitacoes->hasPages() ? (string) $habilitacoes->links() : '',
            ]);
        }

        return view('habilitacoes.index', compact(
            'habilitacoes',
            'busca',
            'clientes',
            'clientesSuggest',
            'clienteBusca',
            'categoria',
            'jurisdicao',
            'vigencia',
            'perPage',
            'categoriasCha',
            'jurisdicoesCha',
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Habilitacao::class);

        $clientes = Cliente::query()->orderBy('nome')->get();

        $clientesSuggest = ClienteCpfSuggest::collection($clientes);

        return view('habilitacoes.create', compact('clientes', 'clientesSuggest'));
    }

    public function store(StoreHabilitacaoRequest $request): RedirectResponse
    {
        $this->authorize('create', Habilitacao::class);

        $habilitacao = Habilitacao::query()->create($request->validated());

        return redirect()
            ->route('habilitacoes.show', $habilitacao)
            ->with('status', __('Cadastro de habilitação criado.'));
    }

    public function edit(Habilitacao $habilitacao): View
    {
        $this->authorize('update', $habilitacao);

        $clientes = Cliente::query()->orderBy('nome')->get();

        $clientesSuggest = ClienteCpfSuggest::collection($clientes);

        return view('habilitacoes.edit', compact('habilitacao', 'clientes', 'clientesSuggest'));
    }

    public function update(UpdateHabilitacaoRequest $request, Habilitacao $habilitacao): RedirectResponse
    {
        $this->authorize('update', $habilitacao);

        $habilitacao->update($request->validated());

        return redirect()
            ->route('habilitacoes.show', $habilitacao)
            ->with('status', __('Dados atualizados.'));
    }

    public function show(Habilitacao $habilitacao): View
    {
        $this->authorize('view', $habilitacao);

        $habilitacao->load(['cliente', 'anexos']);

        return view('habilitacoes.show', compact('habilitacao'));
    }

    public function storeAnexos(StoreHabilitacaoAnexosRequest $request, Habilitacao $habilitacao): RedirectResponse
    {
        $this->authorize('manage', $habilitacao);

        $n = $this->anexoService->armazenarVarios(
            $habilitacao,
            $request->file('arquivos', []),
            $request->validated('tipo_codigo'),
            $request->validated('platform_anexo_tipo_id'),
        );

        if ($n === 0) {
            return back()->withErrors(['arquivos' => __('Nenhum arquivo válido foi enviado.')]);
        }

        return back()->with('status', $n === 1 ? __('1 arquivo enviado.') : __(':count arquivos enviados.', ['count' => $n]));
    }

    public function destroyAnexo(HabilitacaoAnexo $anexo): RedirectResponse
    {
        $habilitacao = $anexo->habilitacao;
        abort_unless($habilitacao, 404);

        $this->authorize('manage', $habilitacao);

        $this->anexoService->remover($anexo);

        return back()->with('status', __('Anexo removido.'));
    }
}
