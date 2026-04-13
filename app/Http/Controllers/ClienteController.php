<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteAnexosRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\ClienteAnexo;
use App\Services\ClienteAnexoService;
use App\Support\BrasilEstados;
use App\Support\ClienteTiposAnexo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use App\Models\Embarcacao;
use App\Models\Habilitacao;

class ClienteController extends Controller
{
    public function __construct(
        private ClienteAnexoService $anexoService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Cliente::class);

        $busca = trim((string) $request->query('q', ''));
        $tipos = $request->query('tipo', []);
        $tipos = is_array($tipos) ? $tipos : [$tipos];
        $tipos = array_values(array_unique(array_filter(array_map(
            static fn ($v) => is_string($v) ? trim($v) : '',
            $tipos,
        ))));
        $tipos = array_values(array_intersect($tipos, ['pf', 'pj']));

        $cidadeRaw = $request->query('cidade', '');
        $cidade = is_string($cidadeRaw) ? trim($cidadeRaw) : '';
        $contatoRaw = $request->query('contato', '');
        $contato = is_string($contatoRaw) ? trim($contatoRaw) : '';
        $perPage = (int) $request->query('per_page', 5);
        $allowedPerPage = [5, 10, 20, 50];
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }

        $query = Cliente::query()->orderBy('nome');

        if ($busca !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $busca).'%';
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('cpf', 'like', $termo)
                    ->orWhere('telefone', 'like', $termo)
                    ->orWhere('celular', 'like', $termo)
                    ->orWhere('documento_identidade_numero', 'like', $termo)
                    ->orWhere('cidade', 'like', $termo)
                    ->orWhere('cep', 'like', $termo)
                    ->orWhere('bairro', 'like', $termo)
                    ->orWhere('endereco', 'like', $termo);
            });
        }

        // Tipo de cadastro (PF/PJ) inferido pelo tamanho do documento na coluna `cpf` (CPF=11, CNPJ=14).
        // Se vierem ambos, não filtra.
        if (count($tipos) === 1) {
            // Alguns registros estão armazenando CPF/CNPJ com pontuação.
            // Para filtrar corretamente, removemos caracteres comuns de formatação.
            $digitsExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),'/',''),' ',''),'(',''),')','')";
            if ($tipos[0] === 'pf') {
                $query->whereRaw("LENGTH($digitsExpr) = 11");
            } elseif ($tipos[0] === 'pj') {
                $query->whereRaw("LENGTH($digitsExpr) = 14");
            }
        }

        if ($cidade !== '') {
            $termoCidade = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $cidade).'%';
            $query->where('cidade', 'like', $termoCidade);
        }

        if ($contato !== '') {
            $termoContato = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $contato).'%';
            $query->where(function ($q) use ($termoContato) {
                $q->where('telefone', 'like', $termoContato)
                    ->orWhere('celular', 'like', $termoContato);
            });
        }

        $clientes = $query->paginate($perPage)->withQueryString();

        $empresaIdFiltros = (int) $request->user()->empresa_id;
        $filtrosCacheKey = 'clientes.index.filtros.'.$empresaIdFiltros;
        [$cidadesOptions, $contatosOptions] = Cache::remember($filtrosCacheKey, 60, function () {
            $cidadesOptions = Cliente::query()
                ->select('cidade')
                ->whereNotNull('cidade')
                ->where('cidade', '!=', '')
                ->distinct()
                ->orderBy('cidade')
                ->pluck('cidade')
                ->values()
                ->all();

            $telefonesOpts = Cliente::query()
                ->whereNotNull('telefone')
                ->where('telefone', '!=', '')
                ->distinct()
                ->pluck('telefone');
            $celularesOpts = Cliente::query()
                ->whereNotNull('celular')
                ->where('celular', '!=', '')
                ->distinct()
                ->pluck('celular');
            $contatosOptions = $telefonesOpts
                ->merge($celularesOpts)
                ->map(static fn ($v) => is_string($v) ? trim($v) : '')
                ->filter()
                ->unique()
                ->sort(static fn (string $a, string $b): int => strnatcasecmp($a, $b))
                ->values()
                ->all();

            return [$cidadesOptions, $contatosOptions];
        });

        $ufs = $request->user()->can('create', Cliente::class)
            ? BrasilEstados::options()
            : [];

        if ($request->wantsJson()) {
            $countText = $busca !== ''
                ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $clientes->total(), ['count' => $clientes->total()])
                : trans_choice('{0} Nenhum cliente cadastrado|{1} :count cadastrado|[2,*] :count cadastrados', (int) $clientes->total(), ['count' => $clientes->total()]);

            return response()->json([
                'count_text' => $countText,
                'tags_html' => view('clientes.partials.index-tags', compact('busca', 'perPage', 'tipos', 'cidade', 'contato'))->render(),
                'list_html' => view('clientes.partials.index-list', compact('clientes', 'busca'))->render(),
                'pagination_html' => $clientes->hasPages() ? (string) $clientes->links() : '',
            ]);
        }

        return view('clientes.index', compact('clientes', 'busca', 'ufs', 'perPage', 'tipos', 'cidade', 'contato', 'cidadesOptions', 'contatosOptions'));
    }

    public function create(): View
    {
        $this->authorize('create', Cliente::class);

        return view('clientes.create', [
            'ufs' => BrasilEstados::options(),
        ]);
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $payload = $request->safe()->except([
            'anexo_cnh',
            'anexo_comprovante',
            'anexo_outro',
            'anexo_outro_tipo',
        ]);

        $cliente = Cliente::query()->create($payload);

        $n = $this->armazenarAnexosDaFicha($request, $cliente);

        $status = __('Cliente cadastrado.');
        if ($n > 0) {
            $status .= ' '.($n === 1 ? __('1 arquivo enviado.') : __(':count arquivos enviados.', ['count' => $n]));
        }

        return redirect()
            ->route('clientes.index')
            ->with('status', $status);
    }

    public function show(Cliente $cliente): View
    {
        $this->authorize('view', $cliente);

        $empresaId = (int) $cliente->empresa_id;
        $cliente->load([
            'anexos',
            'embarcacoes',
            'habilitacoes',
            'processos.tipoProcesso.documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', $empresaId)->orderByPivot('ordem'),
            'processos.documentosChecklist.anexos',
        ]);

        return view('clientes.show', compact('cliente'));
    }

    public function embarcacoesOptions(Cliente $cliente): JsonResponse
    {
        $this->authorize('view', $cliente);

        $embarcacoes = $cliente->embarcacoes()
            ->orderBy('nome')
            ->get(['id', 'nome', 'inscricao', 'tipo']);

        return response()->json([
            'embarcacoes' => $embarcacoes->map(fn (Embarcacao $e) => [
                'id' => (int) $e->id,
                'nome' => (string) ($e->nome ?? ''),
                'inscricao' => (string) ($e->inscricao ?? ''),
                'tipo' => (string) ($e->tipo ?? ''),
            ])->values(),
        ]);
    }

    public function habilitacoesOptions(Cliente $cliente): JsonResponse
    {
        $this->authorize('view', $cliente);

        $habilitacoes = $cliente->habilitacoes()
            ->orderByDesc('data_validade')
            ->orderByDesc('id')
            ->get(['id', 'numero_cha', 'categoria', 'data_validade', 'jurisdicao']);

        return response()->json([
            'habilitacoes' => $habilitacoes->map(fn (Habilitacao $h) => [
                'id' => (int) $h->id,
                'numero_cha' => (string) ($h->numero_cha ?? ''),
                'categoria' => (string) ($h->categoria ?? ''),
                'data_validade' => $h->data_validade?->format('Y-m-d'),
                'jurisdicao' => (string) ($h->jurisdicao ?? ''),
            ])->values(),
        ]);
    }

    public function edit(Cliente $cliente): View
    {
        $this->authorize('update', $cliente);

        return view('clientes.edit', [
            'cliente' => $cliente,
            'ufs' => BrasilEstados::options(),
        ]);
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $payload = $request->safe()->except([
            'anexo_cnh',
            'anexo_comprovante',
            'anexo_outro',
            'anexo_outro_tipo',
        ]);

        $cliente->update($payload);

        $n = $this->armazenarAnexosDaFicha($request, $cliente);

        $status = __('Dados do cliente atualizados.');
        if ($n > 0) {
            $status .= ' '.($n === 1 ? __('1 arquivo enviado.') : __(':count arquivos enviados.', ['count' => $n]));
        }

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('status', $status);
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $this->authorize('delete', $cliente);

        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('status', __('Cliente removido.'));
    }

    public function storeAnexos(StoreClienteAnexosRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->authorize('manage', $cliente);

        $files = $request->file('arquivos') ?? [];

        $n = $this->anexoService->armazenarVarios(
            $cliente,
            $files,
            $request->validated('tipo_codigo'),
            $request->validated('platform_anexo_tipo_id'),
        );

        if ($n === 0) {
            return back();
        }

        return back()->with('status', $n === 1 ? __('1 arquivo enviado.') : __(':count arquivos enviados.', ['count' => $n]));
    }

    public function destroyAnexo(Cliente $cliente, ClienteAnexo $anexo): RedirectResponse
    {
        $this->authorize('manage', $cliente);

        if ((int) $anexo->cliente_id !== (int) $cliente->id) {
            abort(404);
        }

        $this->anexoService->remover($anexo);

        return back()->with('status', __('Anexo removido.'));
    }

    private function armazenarAnexosDaFicha(Request $request, Cliente $cliente): int
    {
        $n = 0;
        $n += $this->anexoService->armazenarVarios(
            $cliente,
            $request->file('anexo_cnh') ?? [],
            ClienteTiposAnexo::CNH,
        );
        $n += $this->anexoService->armazenarVarios(
            $cliente,
            $request->file('anexo_comprovante') ?? [],
            ClienteTiposAnexo::COMPROVANTE_ENDERECO,
        );
        $tipoOutro = $request->input('anexo_outro_tipo');
        $tipoOutro = is_string($tipoOutro) && trim($tipoOutro) !== '' ? trim($tipoOutro) : null;
        $n += $this->anexoService->armazenarVarios(
            $cliente,
            $request->file('anexo_outro') ?? [],
            $tipoOutro,
        );

        return $n;
    }
}
