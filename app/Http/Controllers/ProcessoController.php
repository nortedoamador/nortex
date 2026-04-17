<?php

namespace App\Http\Controllers;

use App\Enums\ProcessoDocumentoStatus;
use App\Enums\ProcessoStatus;
use App\Enums\TipoProcessoCategoria;
use App\Http\Requests\StoreMultiplosAnexosRequest;
use App\Http\Requests\StoreProcessoRequest;
use App\Http\Requests\UpdateProcessoDocumentoRequest;
use App\Http\Requests\UpdateProcessoProtocoloMarinhaRequest;
use App\Http\Requests\UpdateProcessoProvaMarinhaRequest;
use App\Http\Requests\UpdateProcessoStatusRequest;
use App\Models\ActivityLog;
use App\Models\Cliente;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoDocumentoAnexo;
use App\Models\ProcessoPostIt;
use App\Models\TipoProcesso;
use App\Services\DashboardAgendaService;
use App\Services\DashboardAlertasService;
use App\Services\DashboardProvasMarinhaService;
use App\Services\EmpresaProcessosDefaultsService;
use App\Services\Marinha\EmbarcacaoChecklistAnexosRulesService;
use App\Services\Marinha\ProcessoChecklistPreencherDeFichaService;
use App\Services\Marinha\SyncChaAtestadoMedicoDispensaPorCnhService;
use App\Services\Marinha\SyncChaDeclaracaoExtravioPorCategoriaService;
use App\Services\ProcessoDocumentoAnexoService;
use App\Services\ProcessoProgressoService;
use App\Services\ProcessoStatusService;
use App\Services\StripeBillingSyncService;
use App\Support\ChecklistDocumentoModelo;
use App\Support\ClienteCpfSuggest;
use App\Support\EmbarcacaoTipoServicoCatalogo;
use App\Support\Normam211DocumentoCodigos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Js;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProcessoController extends Controller
{
    public function __construct(
        private ProcessoProgressoService $progresso,
        private ProcessoStatusService $statusService,
        private ProcessoDocumentoAnexoService $anexoService,
        private EmpresaProcessosDefaultsService $empresaProcessosDefaults,
        private ProcessoChecklistPreencherDeFichaService $checklistFichaSync,
        private SyncChaAtestadoMedicoDispensaPorCnhService $chaAtestadoDispensaSync,
        private SyncChaDeclaracaoExtravioPorCategoriaService $chaDeclaracaoExtravioSync,
        private DashboardAlertasService $dashboardAlertas,
        private DashboardAgendaService $dashboardAgenda,
        private DashboardProvasMarinhaService $dashboardProvasMarinha,
        private StripeBillingSyncService $stripeBillingSync,
    ) {}

    public function create(Request $request): View
    {
        $this->authorize('create', Processo::class);

        $empresa = $request->user()->empresa;
        if ($empresa) {
            $this->empresaProcessosDefaults->garantirTemplateBasico($empresa);
        }

        $empresaId = (int) $request->user()->empresa_id;

        $tipos = PlatformTipoProcesso::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $tipos->load(['documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', $empresaId)->orderBy('documento_processo.ordem')]);

        $clientesSuggest = ClienteCpfSuggest::collection(Cliente::query()->orderBy('nome')->get());

        $tiposExigenciasJson = Js::from(
            $tipos->map(fn (PlatformTipoProcesso $t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'slug' => $t->slug,
                'categoria' => $t->categoria instanceof TipoProcessoCategoria ? $t->categoria->value : (string) $t->categoria,
                'documentos' => $t->documentoRegras->map(fn ($d) => [
                    'nome' => $d->nome,
                    'obrigatorio' => (bool) $d->pivot->obrigatorio,
                ])->values(),
            ])->values(),
        );

        return view('processos.create', array_merge(
            compact('tipos', 'clientesSuggest', 'tiposExigenciasJson'),
            $this->dadosSelectsProcesso($tipos),
        ));
    }

    public function store(StoreProcessoRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Processo::class);

        $obs = $request->validated('observacoes');

        $empresaId = (int) $request->user()->empresa_id;
        $platformTipoId = (int) $request->validated('platform_tipo_processo_id');
        $platformTipo = PlatformTipoProcesso::query()->findOrFail($platformTipoId);

        // Compat: a tabela processos ainda exige tipo_processo_id (tenant).
        // Mantemos um registro por empresa/slug para integridade referencial.
        $tenantCategoria = $platformTipo->categoria instanceof TipoProcessoCategoria
            ? $platformTipo->categoria
            : ($platformTipo->categoria ? TipoProcessoCategoria::tryFrom((string) $platformTipo->categoria) : null);
        $tenantTipo = TipoProcesso::withoutGlobalScopes()->firstOrCreate(
            ['empresa_id' => $empresaId, 'slug' => $platformTipo->slug],
            [
                'nome' => $platformTipo->nome,
                'categoria' => $tenantCategoria,
            ],
        );

        $processo = Processo::query()->create([
            'cliente_id' => $request->validated('cliente_id'),
            'embarcacao_id' => $request->validated('embarcacao_id'),
            'habilitacao_id' => $request->validated('habilitacao_id'),
            'tipo_processo_id' => $tenantTipo->id,
            'platform_tipo_processo_id' => $platformTipoId,
            'status' => ProcessoStatus::EmMontagem,
            'observacoes' => null,
            'jurisdicao' => $request->validated('jurisdicao'),
        ]);

        $this->chaDeclaracaoExtravioSync->sync($processo);

        if (filled($obs)) {
            ProcessoPostIt::query()->create([
                'processo_id' => $processo->id,
                'user_id' => $request->user()->id,
                'conteudo' => trim((string) $obs),
            ]);
        }

        if ($request->wantsJson()) {
            $processo->load([
                'cliente.embarcacoes',
                'documentosChecklist.documentoTipo',
                'documentosChecklist.anexos',
                'tipoProcesso.documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $request->user()->empresa_id)->orderBy('documento_processo.ordem'),
            ]);

            return response()->json([
                'processo' => ['id' => $processo->id],
                'documentos' => $this->serializarChecklistModal($processo),
                'progresso' => $this->progresso->calcular($processo),
                'show_url' => route('processos.show', $processo),
            ]);
        }

        return redirect()
            ->route('processos.show', $processo)
            ->with('status', __('Processo criado. Complete o checklist e os anexos.'));
    }

    public function updateObservacoes(Request $request, Processo $processo): JsonResponse|RedirectResponse
    {
        $this->authorize('updateDocumento', $processo);

        $validated = $request->validate([
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ]);

        $text = isset($validated['observacoes']) ? trim((string) $validated['observacoes']) : '';
        if ($text !== '') {
            ProcessoPostIt::query()->create([
                'processo_id' => $processo->id,
                'user_id' => $request->user()->id,
                'conteudo' => $text,
            ]);
        }

        $processo->update(['observacoes' => null]);

        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => route('processos.show', $processo),
                'message' => __('Processo criado. Complete o checklist e os anexos.'),
            ]);
        }

        return back()->with('status', __('Observações atualizadas.'));
    }

    public function updateProtocoloMarinha(UpdateProcessoProtocoloMarinhaRequest $request, Processo $processo): RedirectResponse|JsonResponse
    {
        $data = [
            'marinha_protocolo_numero' => trim((string) $request->validated('marinha_protocolo_numero')),
            'marinha_protocolo_data' => $request->validated('marinha_protocolo_data'),
        ];

        if ($request->hasFile('marinha_protocolo_anexo')) {
            $this->deleteMarinhaProtocoloAnexoFile($processo);
            $file = $request->file('marinha_protocolo_anexo');
            $path = $file->store(
                'processos/'.$processo->empresa_id.'/'.$processo->id.'/marinha-protocolo',
                'local'
            );
            $data['marinha_protocolo_anexo_path'] = $path;
            $data['marinha_protocolo_anexo_original_name'] = $file->getClientOriginalName();
        } elseif ($request->boolean('remover_marinha_protocolo_anexo')) {
            $this->deleteMarinhaProtocoloAnexoFile($processo);
            $data['marinha_protocolo_anexo_path'] = null;
            $data['marinha_protocolo_anexo_original_name'] = null;
        }

        $processo->update($data);
        $processo->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Dados de protocolo da Marinha guardados.'),
                'falta_protocolo' => $processo->faltaIdentificacaoProtocoloMarinha(),
                'marinha_protocolo_anexo_original_name' => $processo->marinha_protocolo_anexo_original_name,
                'marinha_protocolo_anexo_url' => filled($processo->marinha_protocolo_anexo_path)
                    ? route('processos.protocolo-marinha.anexo', $processo)
                    : null,
            ]);
        }

        return redirect()
            ->route('processos.show', $processo)
            ->with('status', __('Dados de protocolo da Marinha guardados.'));
    }

    public function updateProvaMarinha(UpdateProcessoProvaMarinhaRequest $request, Processo $processo): RedirectResponse
    {
        $processo->update([
            'marinha_prova_data' => $request->validated('marinha_prova_data'),
        ]);

        return back()->with('status', __('Data da prova na Marinha guardada.'));
    }

    public function downloadProtocoloMarinhaAnexo(Request $request, Processo $processo): BinaryFileResponse|StreamedResponse
    {
        $this->authorize('view', $processo);

        $path = $processo->marinha_protocolo_anexo_path;
        abort_unless(filled($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        $name = $processo->marinha_protocolo_anexo_original_name ?: 'protocolo-marinha.pdf';

        if ($request->boolean('inline')) {
            $mime = (string) (Storage::disk('local')->mimeType($path) ?: 'application/octet-stream');
            if (str_starts_with($mime, 'image/')) {
                $absolutePath = Storage::disk('local')->path($path);

                return response()->file($absolutePath, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => HeaderUtils::makeDisposition(
                        HeaderUtils::DISPOSITION_INLINE,
                        $name,
                        pathinfo($name, PATHINFO_FILENAME) ?: 'preview'
                    ),
                ]);
            }
        }

        return Storage::disk('local')->download($path, $name);
    }

    private function deleteMarinhaProtocoloAnexoFile(Processo $processo): void
    {
        $path = $processo->marinha_protocolo_anexo_path;
        if (! filled($path)) {
            return;
        }
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function storePostIt(Request $request, Processo $processo): JsonResponse
    {
        $this->authorize('updateDocumento', $processo);

        $validated = $request->validate([
            'conteudo' => ['required', 'string', 'max:5000'],
        ]);

        $postIt = ProcessoPostIt::query()->create([
            'processo_id' => $processo->id,
            'user_id' => $request->user()->id,
            'conteudo' => trim($validated['conteudo']),
        ]);
        $postIt->load('user:id,name');

        return response()->json([
            'post_it' => $this->serializarPostIt($postIt),
        ], 201);
    }

    public function updatePostIt(Request $request, Processo $processo, ProcessoPostIt $postIt): JsonResponse
    {
        $this->authorize('updateDocumento', $processo);

        if ((int) $postIt->processo_id !== (int) $processo->id) {
            abort(404);
        }

        $validated = $request->validate([
            'conteudo' => ['required', 'string', 'max:5000'],
        ]);

        $postIt->update([
            'conteudo' => trim($validated['conteudo']),
        ]);
        $postIt->load('user:id,name');

        return response()->json([
            'post_it' => $this->serializarPostIt($postIt),
        ]);
    }

    public function destroyPostIt(Request $request, Processo $processo, ProcessoPostIt $postIt): JsonResponse
    {
        $this->authorize('updateDocumento', $processo);

        if ((int) $postIt->processo_id !== (int) $processo->id) {
            abort(404);
        }

        $postIt->delete();

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, Processo $processo): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $processo);

        if ($processo->status !== ProcessoStatus::EmMontagem) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Só é possível descartar processos em «Em montagem».'),
                ], 422);
            }

            abort(422);
        }

        $this->eliminarProcessoComAnexos($processo);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('processos.index')
            ->with('status', __('Processo removido.'));
    }

    public function destroyMany(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Processo::class);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $user = $request->user();
        $empresaId = (int) $user->empresa_id;

        $processos = Processo::query()
            ->whereIn('id', $validated['ids'])
            ->where('empresa_id', $empresaId)
            ->get()
            ->keyBy('id');

        $removed = 0;
        $skipped = 0;

        foreach ($validated['ids'] as $id) {
            $processo = $processos->get($id);
            if (! $processo) {
                $skipped++;

                continue;
            }
            if (! $user->can('delete', $processo)) {
                $skipped++;

                continue;
            }
            if ($processo->status !== ProcessoStatus::EmMontagem) {
                $skipped++;

                continue;
            }
            $this->eliminarProcessoComAnexos($processo);
            $removed++;
        }

        if ($removed === 0) {
            return redirect()
                ->route('processos.index', $this->redirectParamsProcessosIndex($request))
                ->withErrors(['status' => __('Nenhum dos processos selecionados pôde ser excluído (só rascunhos em «Em montagem»).')]);
        }

        $msg = __('Removidos :count processo(s).', ['count' => $removed]);
        if ($skipped > 0) {
            $msg .= ' '.__(':count não removido(s) (sem permissão ou fora de «Em montagem»).', ['count' => $skipped]);
        }

        return redirect()
            ->route('processos.index', $this->redirectParamsProcessosIndex($request))
            ->with('status', $msg);
    }

    public function updateStatusMany(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', Processo::class);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct'],
            'status' => ['required', Rule::enum(ProcessoStatus::class)],
            'confirmar_ciencia_pendencias_documentais' => ['sometimes', 'boolean'],
            'redirect_v' => ['sometimes', 'string'],
            'redirect_q' => ['sometimes', 'nullable', 'string'],
            'redirect_status' => ['sometimes', 'nullable', 'string'],
            'redirect_tipo' => ['sometimes', 'nullable', 'string'],
            'redirect_cat' => ['sometimes', 'nullable', 'string'],
            'redirect_cliente' => ['sometimes', 'nullable', 'string'],
            'redirect_processo' => ['sometimes', 'nullable', 'string'],
            'redirect_doc_pendente' => ['sometimes', 'nullable', 'string'],
            'redirect_atualizado_de' => ['sometimes', 'nullable', 'string'],
            'redirect_atualizado_ate' => ['sometimes', 'nullable', 'string'],
            'redirect_jurisdicao' => ['sometimes', 'nullable', 'string', Rule::in(Habilitacao::JURISDICOES)],
        ]);

        $novo = ProcessoStatus::from($validated['status']);
        $confirmar = $request->boolean('confirmar_ciencia_pendencias_documentais');
        $user = $request->user();
        $empresaId = (int) $user->empresa_id;

        $processos = Processo::query()
            ->whereIn('id', $validated['ids'])
            ->where('empresa_id', $empresaId)
            ->with('tipoProcesso')
            ->get()
            ->keyBy('id');

        $wantsJson = $request->expectsJson()
            || $request->wantsJson()
            || str_contains((string) $request->header('Accept', ''), 'application/json');

        if (! $confirmar) {
            foreach ($validated['ids'] as $id) {
                $p = $processos->get($id);
                if (! $p || ! $user->can('updateStatus', $p)) {
                    continue;
                }
                if ($p->status === $novo) {
                    continue;
                }
                if (! $p->aceitaDestinoStatus($novo)) {
                    continue;
                }
                if ($this->statusService->requerConfirmacaoCienciaPendenciasDocumentais($p, $novo)) {
                    if ($wantsJson) {
                        return response()->json([
                            'needs_ciencia' => true,
                            'message' => __('Um ou mais processos têm documentos obrigatórios pendentes. Confirme para alterar a etapa mesmo assim.'),
                        ], 422);
                    }

                    return redirect()
                        ->route('processos.index', $this->redirectParamsProcessosIndex($request))
                        ->withErrors(['status' => __('Confirme a ciência das pendências documentais antes de alterar a etapa em lote.')]);
                }
            }
        }

        $updated = 0;
        $skipped = 0;

        foreach ($validated['ids'] as $id) {
            $p = $processos->get($id);
            if (! $p || ! $user->can('updateStatus', $p)) {
                $skipped++;

                continue;
            }
            if ($p->status === $novo) {
                continue;
            }
            if (! $p->aceitaDestinoStatus($novo)) {
                $skipped++;

                continue;
            }
            if (! $this->statusService->podeAlterarStatus($p, $novo, $confirmar)) {
                $skipped++;

                continue;
            }
            $p->update(['status' => $novo]);
            $updated++;
        }

        if ($updated === 0) {
            $msg = __('Nenhum processo teve a etapa alterada.');
            if ($wantsJson) {
                return response()->json([
                    'ok' => false,
                    'message' => $msg,
                    'updated' => 0,
                    'skipped' => $skipped,
                ], 422);
            }

            return redirect()
                ->route('processos.index', $this->redirectParamsProcessosIndex($request))
                ->withErrors(['status' => $msg]);
        }

        $msg = __('Etapa atualizada em :count processo(s).', ['count' => $updated]);
        if ($skipped > 0) {
            $msg .= ' '.__(':count ignorado(s).', ['count' => $skipped]);
        }

        if ($wantsJson) {
            return response()->json([
                'ok' => true,
                'message' => $msg,
                'updated' => $updated,
                'skipped' => $skipped,
            ]);
        }

        return redirect()
            ->route('processos.index', $this->redirectParamsProcessosIndex($request))
            ->with('status', $msg);
    }

    /** @return array<string, string> */
    private function redirectParamsProcessosIndex(Request $request): array
    {
        $v = $request->input('redirect_v', 'list');
        $params = ['v' => in_array($v, ['list', 'grid'], true) ? $v : 'list'];
        if (filled($request->input('redirect_q'))) {
            $params['q'] = (string) $request->input('redirect_q');
        }
        if (filled($request->input('redirect_status'))) {
            $params['status'] = (string) $request->input('redirect_status');
        }
        foreach ($this->processosIndexFiltrosRedirectInputMap() as $queryKey => $inputKey) {
            $val = $request->input($inputKey);
            if (! filled($val)) {
                continue;
            }
            if ($queryKey === 'doc_pendente' && (string) $val !== '1') {
                continue;
            }
            $params[$queryKey] = is_scalar($val) ? (string) $val : '';
        }

        return $params;
    }

    /**
     * @return array<string, string> query string key => request input name (redirect_*)
     */
    private function processosIndexFiltrosRedirectInputMap(): array
    {
        return [
            'tipo' => 'redirect_tipo',
            'cat' => 'redirect_cat',
            'cliente' => 'redirect_cliente',
            'processo' => 'redirect_processo',
            'doc_pendente' => 'redirect_doc_pendente',
            'atualizado_de' => 'redirect_atualizado_de',
            'atualizado_ate' => 'redirect_atualizado_ate',
            'jurisdicao' => 'redirect_jurisdicao',
        ];
    }

    /**
     * @return array{
     *     tipo: int,
     *     cat: string|null,
     *     cliente: int,
     *     processo: int,
     *     doc_pendente: bool,
     *     atualizado_de: string|null,
     *     atualizado_ate: string|null,
     *     jurisdicao: string|null,
     *     avancados_ativos: int
     * }
     */
    private function processosIndexFiltrosAvancadosFromRequest(Request $request): array
    {
        $tipo = max(0, (int) $request->query('tipo', 0));
        $cliente = max(0, (int) $request->query('cliente', 0));
        $processo = max(0, (int) $request->query('processo', 0));
        $catRaw = $request->query('cat');
        $cat = is_string($catRaw) && $catRaw !== '' ? $catRaw : null;
        $docPendente = $request->query('doc_pendente') === '1';
        $deRaw = $request->query('atualizado_de');
        $ateRaw = $request->query('atualizado_ate');
        $atualizadoDe = null;
        $atualizadoAte = null;
        if (is_string($deRaw) && $deRaw !== '') {
            $deRaw = trim($deRaw);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $deRaw)) {
                $atualizadoDe = $deRaw;
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $deRaw)) {
                try {
                    $atualizadoDe = Carbon::createFromFormat('d/m/Y', $deRaw)->format('Y-m-d');
                } catch (\Throwable) {
                    $atualizadoDe = null;
                }
            }
        }
        if (is_string($ateRaw) && $ateRaw !== '') {
            $ateRaw = trim($ateRaw);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ateRaw)) {
                $atualizadoAte = $ateRaw;
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $ateRaw)) {
                try {
                    $atualizadoAte = Carbon::createFromFormat('d/m/Y', $ateRaw)->format('Y-m-d');
                } catch (\Throwable) {
                    $atualizadoAte = null;
                }
            }
        }
        $jurRaw = $request->query('jurisdicao');
        $jurisdicao = null;
        if (is_string($jurRaw) && $jurRaw !== '' && in_array($jurRaw, Habilitacao::JURISDICOES, true)) {
            $jurisdicao = $jurRaw;
        }

        $n = 0;
        if ($tipo > 0) {
            $n++;
        }
        if ($cat !== null) {
            $n++;
        }
        if ($cliente > 0) {
            $n++;
        }
        if ($processo > 0) {
            $n++;
        }
        if ($docPendente) {
            $n++;
        }
        if ($atualizadoDe !== null) {
            $n++;
        }
        if ($atualizadoAte !== null) {
            $n++;
        }
        if ($jurisdicao !== null) {
            $n++;
        }

        return [
            'tipo' => $tipo,
            'cat' => $cat,
            'cliente' => $cliente,
            'processo' => $processo,
            'doc_pendente' => $docPendente,
            'atualizado_de' => $atualizadoDe,
            'atualizado_ate' => $atualizadoAte,
            'jurisdicao' => $jurisdicao,
            'avancados_ativos' => $n,
        ];
    }

    private function aplicarFiltrosAvancadosProcessosIndex($query, array $filtros): void
    {
        if ($filtros['tipo'] > 0) {
            $query->where('platform_tipo_processo_id', $filtros['tipo']);
        }
        if ($filtros['cat'] !== null) {
            try {
                $categoria = TipoProcessoCategoria::from($filtros['cat']);
                $query->whereHas('tipoProcesso', fn ($q) => $q->where('categoria', $categoria->value));
            } catch (\ValueError) {
                // ignora categoria inválida na URL
            }
        }
        if ($filtros['cliente'] > 0) {
            $query->where('cliente_id', $filtros['cliente']);
        }
        if ($filtros['processo'] > 0) {
            $query->where('id', $filtros['processo']);
        }
        if ($filtros['doc_pendente']) {
            $pendente = ProcessoDocumentoStatus::Pendente->value;
            $query->whereExists(function ($sub) use ($pendente) {
                $sub->selectRaw('1')
                    ->from('processo_documentos as pd')
                    ->join('documento_processo as dpr', 'dpr.documento_tipo_id', '=', 'pd.documento_tipo_id')
                    ->whereColumn('dpr.platform_tipo_processo_id', 'processos.platform_tipo_processo_id')
                    ->whereColumn('dpr.empresa_id', 'processos.empresa_id')
                    ->where('dpr.obrigatorio', true)
                    ->where('pd.status', $pendente)
                    ->whereColumn('pd.processo_id', 'processos.id');
            });
        }
        if ($filtros['atualizado_de'] !== null) {
            $query->whereDate('updated_at', '>=', $filtros['atualizado_de']);
        }
        if ($filtros['atualizado_ate'] !== null) {
            $query->whereDate('updated_at', '<=', $filtros['atualizado_ate']);
        }
        if (filled($filtros['jurisdicao'] ?? null)) {
            $query->where('jurisdicao', $filtros['jurisdicao']);
        }
    }

    /**
     * @return array{id: int, conteudo: string, created_at: string|null, updated_at: string|null, user: array{name: string}|null}
     */
    private function serializarPostIt(ProcessoPostIt $postIt): array
    {
        return [
            'id' => $postIt->id,
            'conteudo' => $postIt->conteudo,
            'created_at' => $postIt->created_at?->toIso8601String(),
            'updated_at' => $postIt->updated_at?->toIso8601String(),
            'user' => $postIt->relationLoaded('user') && $postIt->user
                ? ['name' => (string) $postIt->user->name]
                : null,
        ];
    }

    private function eliminarProcessoComAnexos(Processo $processo): void
    {
        $processo->load('documentosChecklist.anexos');
        foreach ($processo->documentosChecklist as $doc) {
            foreach ($doc->anexos as $anexo) {
                $this->anexoService->remover($anexo);
            }
        }

        $processo->delete();
    }

    /**
     * @return list<array{
     *     id: int,
     *     nome: string,
     *     status: string,
     *     obrigatorio: bool,
     *     anexos: list<array{id: int, nome_original: string, url: string}>
     * }>
     */
    private function serializarChecklistModal(Processo $processo): array
    {
        $processo->loadMissing([
            'cliente.embarcacoes',
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
            'tipoProcesso.documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $processo->empresa_id)->orderBy('documento_processo.ordem'),
        ]);

        $ordemIds = $processo->tipoProcesso?->documentoRegras
            ?->pluck('id')
            ->all() ?? [];
        $pos = array_flip($ordemIds);

        return $processo->documentosChecklist
            ->sortBy(fn (ProcessoDocumento $pd) => $pos[$pd->documento_tipo_id] ?? 9999)
            ->values()
            ->map(fn (ProcessoDocumento $pd) => $this->mapearLinhaChecklistModal($processo, $pd))
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     nome: string,
     *     codigo: string,
     *     modelo_slug: string,
     *     status: string,
     *     obrigatorio: bool,
     *     declaracao_residencia_2g: bool,
     *     url_declaracao_2g: ?string,
     *     declaracao_anexo_5h: bool,
     *     url_declaracao_5h: ?string,
     *     declaracao_anexo_5d: bool,
     *     url_declaracao_5d: ?string,
     *     preenchido_via_modelo: bool,
     *     url_visualizar_modelo: ?string,
     *     data_validade_documento: ?string,
     *     anexos: list<array{id: int, nome_original: string, url: string}>
     * }
     */
    private function urlDocumentoModeloCliente(Processo $processo, Cliente $cliente, string $slug, bool $anexarContextoEmbarcacao): string
    {
        $url = route('clientes.documento-modelos.render', [
            'cliente' => $cliente,
            'slug' => $slug,
        ]);

        if ($anexarContextoEmbarcacao) {
            $embId = $processo->embarcacao_id
                ?: ($cliente->relationLoaded('embarcacoes')
                    ? $cliente->embarcacoes->sortBy('id')->first()?->id
                    : $cliente->embarcacoes()->orderBy('id')->value('id'));
            if ($embId) {
                $url .= (str_contains($url, '?') ? '&' : '?').'contexto_id='.$embId;
            }
        }

        return $url;
    }

    private function resolverModeloSlugChecklist(Processo $processo, ProcessoDocumento $pd): string
    {
        $slugBase = (string) ($pd->documentoTipo?->modeloSlugParaRender() ?? '');
        $codigo = (string) ($pd->documentoTipo?->codigo ?? '');

        $processo->loadMissing('embarcacao');
        if (! $processo->embarcacao) {
            return $slugBase;
        }

        $slugs = app(EmbarcacaoChecklistAnexosRulesService::class)->resolver($processo->embarcacao);

        // 1) Identificar qual anexo deve ser usado (requerimento / BADE-BSADE / residência)
        if ($codigo === 'TIE_REQ_INTERESSADO'
            || $codigo === 'TIE_REQ_INTERESSADO_ANEXO_2C_211'
            || $codigo === 'REQ_NORMAM_2C'
            || $codigo === 'TIE_REQ_INTERESSADO_ANEXO_2A_212') {
            return $slugs['requerimento_slug'];
        }

        if (in_array($codigo, [
            'TIE_BSADE_211_2B_DUAS_VIAS',
            'BSADE_NORMAM_2D',
            'TIE_BADE',
            'TIE_BADE_OU_BSADE',
            Normam211DocumentoCodigos::TIE_BDMOTO_212_2B,
            Normam211DocumentoCodigos::TIE_BDMOTO_SE_ALTERACAO,
        ], true)) {
            return $slugs['bade_bsade_slug'];
        }

        if ($codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
            || $codigo === 'TIE_COMPROVANTE_RESID_ATUAL_OU_DECL'
            || $codigo === 'TIE_COMPROVANTE_RESIDENCIA'
            || $codigo === 'TIE_COMPROVANTE_RESID_90_OU_DECL'
            || $codigo === 'TIE_COMPROVANTE_RESID_212_1C'
            || $codigo === Normam211DocumentoCodigos::CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY) {
            return $slugs['declaracao_residencia_slug'];
        }

        return $slugBase;
    }

    private function mapearLinhaChecklistModal(Processo $processo, ProcessoDocumento $pd): array
    {
        $tipoDoc = $processo->tipoProcesso?->documentoRegras
            ->firstWhere('id', $pd->documento_tipo_id);
        $obr = $tipoDoc ? (bool) $tipoDoc->pivot->obrigatorio : true;

        $codigo = (string) ($pd->documentoTipo?->codigo ?? '');
        $slugRender = $this->resolverModeloSlugChecklist($processo, $pd);

        $urlAbrirModelo = null;
        if ($processo->cliente && $slugRender !== '') {
            $urlAbrirModelo = $this->urlDocumentoModeloCliente(
                $processo,
                $processo->cliente,
                $slugRender,
                ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slugRender),
            );
        }

        $urlVisualizarModelo = null;
        if ($processo->cliente
            && $slugRender !== ''
            && $pd->status === ProcessoDocumentoStatus::Enviado
            && ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($pd)) {
            $urlVisualizarModelo = $this->urlDocumentoModeloCliente(
                $processo,
                $processo->cliente,
                $slugRender,
                ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slugRender),
            );
        }

        $urlDecl2g = $codigo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
            ? $urlVisualizarModelo
            : null;

        $urlDecl5h = Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigo)
            ? $urlVisualizarModelo
            : null;

        $urlDecl5d = Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigo)
            ? $urlVisualizarModelo
            : null;

        $urlDecl3d = Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigo)
            ? $urlVisualizarModelo
            : null;

        return [
            'id' => $pd->id,
            'nome' => $pd->documentoTipo?->nome ?? '',
            'codigo' => $codigo,
            'modelo_slug' => $slugRender,
            'status' => $pd->status->value,
            'obrigatorio' => $obr,
            'declaracao_residencia_2g' => (bool) ($pd->declaracao_residencia_2g ?? false),
            'url_declaracao_2g' => $urlDecl2g,
            'declaracao_anexo_5h' => (bool) ($pd->declaracao_anexo_5h ?? false),
            'url_declaracao_5h' => $urlDecl5h,
            'declaracao_anexo_5d' => (bool) ($pd->declaracao_anexo_5d ?? false),
            'url_declaracao_5d' => $urlDecl5d,
            'declaracao_anexo_3d' => (bool) ($pd->declaracao_anexo_3d ?? false),
            'url_declaracao_3d' => $urlDecl3d,
            'preenchido_via_modelo' => (bool) ($pd->preenchido_via_modelo ?? false),
            'satisfeito_via_ficha_embarcacao' => (bool) ($pd->satisfeito_via_ficha_embarcacao ?? false),
            'url_abrir_modelo' => $urlAbrirModelo,
            'url_visualizar_modelo' => $urlVisualizarModelo,
            'data_validade_documento' => $pd->data_validade_documento?->format('Y-m-d'),
            'anexos' => $pd->anexos
                ->map(fn (ProcessoDocumentoAnexo $a) => [
                    'id' => $a->id,
                    'nome_original' => $a->nome_original,
                    'url' => $a->signedInlineUrl(),
                ])
                ->values()
                ->all(),
        ];
    }

    private function serializarUmDocumentoChecklistModal(Processo $processo, ProcessoDocumento $documento): array
    {
        $processo->loadMissing([
            'cliente.embarcacoes',
            'tipoProcesso.documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
        ]);
        $documento->loadMissing(['documentoTipo', 'anexos']);

        return $this->mapearLinhaChecklistModal($processo, $documento);
    }

    /**
     * @return array{message: string, documento: array, progresso: array, documentos_extra?: list<array<string, mixed>>}
     */
    private function jsonRespostaDocumentoChecklistAposDispensa(Processo $processo, ProcessoDocumento $documento, string $message): array
    {
        $this->checklistFichaSync->sync($processo);
        $extraIds = $this->chaAtestadoDispensaSync->sync($processo);
        $processo->refresh();
        $documento->refresh();
        $documento->load(['anexos', 'documentoTipo']);

        $payload = [
            'message' => $message,
            'documento' => $this->serializarUmDocumentoChecklistModal($processo, $documento),
            'progresso' => $this->progresso->calcular($processo),
        ];

        $docId = (int) $documento->id;
        $extraIds = array_values(array_unique(array_map(
            static fn ($id) => (int) $id,
            array_filter($extraIds, fn ($id) => (int) $id !== $docId),
        )));

        if ($extraIds !== []) {
            $processo->loadMissing([
                'cliente.embarcacoes',
                'documentosChecklist.documentoTipo',
                'documentosChecklist.anexos',
                'tipoProcesso.documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
            ]);
            $payload['documentos_extra'] = collect($extraIds)
                ->map(fn (int $id) => $processo->documentosChecklist->firstWhere('id', $id))
                ->filter()
                ->map(fn (ProcessoDocumento $pd) => $this->mapearLinhaChecklistModal($processo, $pd))
                ->values()
                ->all();
        }

        return $payload;
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $voltouDoCheckout = is_string($sessionId) && $sessionId !== '';

        if ($voltouDoCheckout && is_string(config('services.stripe.secret')) && config('services.stripe.secret') !== '') {
            try {
                Stripe::setApiKey((string) config('services.stripe.secret'));
                $session = StripeCheckoutSession::retrieve($sessionId);
                $this->stripeBillingSync->syncFromCheckoutSession($session);
            } catch (\Throwable) {
                // Webhook ou nova tentativa pode sincronizar depois.
            }
        }

        if ($voltouDoCheckout) {
            $request->user()->unsetRelation('empresa');
            $request->user()->loadMissing('empresa');
            if ($request->user()->empresa !== null && $request->user()->empresa->assinaturaPlataformaAtiva()) {
                return redirect()
                    ->route('dashboard')
                    ->with('status', __('Plano ativo. Pode utilizar todos os módulos.'));
            }
        }

        $kanban = null;
        $metricasDashboard = [];
        $alertasResumo = null;
        if ($request->user()->hasPermission('processos.view')) {
            $kanban = $this->kanbanBoardData($request);
            $metricasDashboard = $this->metricasResumoProcessos($request);
            $empresaId = (int) ($request->user()->empresa_id ?? 0);
            if ($empresaId > 0) {
                $alertasResumo = $this->dashboardAlertas->resumo($empresaId);
            }
        }

        $request->user()->loadMissing('empresa');
        $planoAtivo = $request->user()->empresa !== null
            && $request->user()->empresa->assinaturaPlataformaAtiva();

        $agendaItens = $this->dashboardAgenda->proximosItens($request->user());
        $provasMarinhaItens = $this->dashboardProvasMarinha->itens($request->user());

        $atividadeRecente = collect();
        $empresaIdDashboard = (int) ($request->user()->empresa_id ?? 0);
        if ($empresaIdDashboard > 0) {
            $atividadeRecente = ActivityLog::query()
                ->where('empresa_id', $empresaIdDashboard)
                ->with('user:id,name')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get();
        }

        return view('dashboard', compact('kanban', 'metricasDashboard', 'alertasResumo', 'agendaItens', 'provasMarinhaItens', 'planoAtivo', 'atividadeRecente'));
    }

    /**
     * @return array{
     *     em_montagem: int,
     *     a_protocolar: int,
     *     protocolado: int,
     *     em_andamento: int,
     *     em_exigencia: int,
     *     aguardando_prova: int,
     *     indeferido: int,
     *     a_disposicao: int,
     *     concluido: int,
     *     processos_ativos: int,
     *     processos_ativos_semana: int
     *     clientes_total: int,
     *     clientes_mes: int
     *     embarcacoes_total: int
     * }
     */
    private function metricasResumoProcessos(Request $request): array
    {
        $totais = Processo::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($n) => (int) $n);

        $n = fn (ProcessoStatus $s) => (int) ($totais[$s->value] ?? 0);

        $processosAtivos = Processo::query()
            ->whereNotIn('status', [ProcessoStatus::Concluido, ProcessoStatus::Indeferido])
            ->count();

        $processosAtivosSemana = Processo::query()
            ->whereNotIn('status', [ProcessoStatus::Concluido, ProcessoStatus::Indeferido])
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $clientesTotal = Cliente::query()->count();
        $clientesMes = Cliente::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $embarcacoesTotal = Embarcacao::query()->count();

        $out = [
            'em_montagem' => $n(ProcessoStatus::EmMontagem),
            'a_protocolar' => $n(ProcessoStatus::AProtocolar),
            'protocolado' => $n(ProcessoStatus::Protocolado),
            'em_andamento' => $n(ProcessoStatus::EmAndamento),
            'em_exigencia' => $n(ProcessoStatus::EmExigencia),
            'aguardando_prova' => $n(ProcessoStatus::AguardandoProva),
            'indeferido' => $n(ProcessoStatus::Indeferido),
            'a_disposicao' => $n(ProcessoStatus::ADisposicao),
            'concluido' => $n(ProcessoStatus::Concluido),
            'processos_ativos' => $processosAtivos,
            'processos_ativos_semana' => $processosAtivosSemana,
            'clientes_total' => $clientesTotal,
            'clientes_mes' => $clientesMes,
            'embarcacoes_total' => $embarcacoesTotal,
        ];

        return $out;
    }

    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        $this->authorize('viewAny', Processo::class);

        $empresaIndex = $request->user()->empresa;
        if ($empresaIndex) {
            $this->empresaProcessosDefaults->garantirTemplateBasico($empresaIndex);
        }

        $visualizacao = $request->query('v', 'list');
        if (! in_array($visualizacao, ['list', 'grid'], true)) {
            $visualizacao = 'list';
        }

        if ($request->query('limpar') === '1') {
            return redirect()->route('processos.index', ['v' => $visualizacao]);
        }

        $busca = trim((string) $request->query('q', ''));
        $statusFiltro = $request->query('status');
        $filtrosAvancados = $this->processosIndexFiltrosAvancadosFromRequest($request);

        $empresaIdUsuario = (int) ($request->user()->empresa_id ?? 0);

        $query = Processo::query()
            ->with(['cliente', 'tipoProcesso'])
            ->withCount([
                'documentosChecklist as nx_docs_pendentes_count' => function ($q) {
                    $q->where(function ($q) {
                        $q->where('status', ProcessoDocumentoStatus::Pendente)
                            ->orWhere(function ($q) {
                                $q->where('status', ProcessoDocumentoStatus::Enviado)
                                    ->whereDoesntHave('anexos')
                                    ->where('preenchido_via_modelo', false)
                                    ->where('declaracao_residencia_2g', false)
                                    ->where('declaracao_anexo_5h', false)
                                    ->where('declaracao_anexo_5d', false)
                                    ->where('declaracao_anexo_3d', false);
                            });
                    });
                },
            ])
            ->orderByDesc('updated_at');

        if (is_string($statusFiltro) && $statusFiltro !== '') {
            try {
                $query->where('status', ProcessoStatus::from($statusFiltro));
            } catch (\ValueError) {
                // ignora valor inválido na URL
            }
        }

        if ($busca !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $busca).'%';
            $query->where(function ($q) use ($termo) {
                $q->whereHas('cliente', fn ($c) => $c->where('nome', 'like', $termo))
                    ->orWhereHas('tipoProcesso', fn ($t) => $t->where('nome', 'like', $termo));
            });
        }

        $this->aplicarFiltrosAvancadosProcessosIndex($query, $filtrosAvancados);

        $clientesParaFiltroProcessos = $empresaIdUsuario > 0
            ? Cliente::query()
                ->where('empresa_id', $empresaIdUsuario)
                ->orderBy('nome')
                ->limit(500)
                ->get(['id', 'nome'])
            : collect();

        if ($visualizacao === 'grid') {
            $processosTodos = (clone $query)->get();
            $processosGrid = $processosTodos->groupBy(fn (Processo $p) => $p->status->gridResumoColumnKey());
            $colunasGridResumo = ProcessoStatus::gridResumoColumns();
            if (($processosGrid->get('outras')?->count() ?? 0) > 0) {
                $colunasGridResumo[] = ['key' => 'outras', 'dot' => 'bg-slate-400'];
            }
            $processos = null;
        } else {
            $processosGrid = null;
            $colunasGridResumo = null;
            $processos = $query->paginate(12)->withQueryString();
        }

        $totalAtivos = Processo::query()
            ->whereNotIn('status', [
                ProcessoStatus::Concluido->value,
                ProcessoStatus::Indeferido->value,
            ])
            ->count();

        $etapas = ProcessoStatus::cases();
        $podeAlterarStatus = $request->user()->hasPermission('processos.alterar_status');
        $podeExcluirLote = $request->user()->hasPermission('processos.edit');
        $idsSelecaoLotePagina = [];
        $idsExclusaoLotePagina = [];
        if ($visualizacao === 'list' && $processos !== null) {
            $idsSelecaoLotePagina = $processos->getCollection()
                ->filter(fn (Processo $p) => $request->user()->can('updateStatus', $p)
                    || ($request->user()->can('delete', $p) && $p->status === ProcessoStatus::EmMontagem))
                ->pluck('id')
                ->values()
                ->all();
            $idsExclusaoLotePagina = $processos->getCollection()
                ->filter(fn (Processo $p) => $request->user()->can('delete', $p) && $p->status === ProcessoStatus::EmMontagem)
                ->pluck('id')
                ->values()
                ->all();
        }
        $mostrarSelecaoEmLote = $visualizacao === 'list' && count($idsSelecaoLotePagina) > 0;

        $empresaId = (int) $request->user()->empresa_id;

        $tiposProcessoModal = PlatformTipoProcesso::query()
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();
        $tiposProcessoModal->load(['documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', $empresaId)->orderBy('documento_processo.ordem')]);

        $clientesSuggestProcessoModal = ClienteCpfSuggest::collection(Cliente::query()->orderBy('nome')->get());

        $viewData = array_merge(
            compact(
                'processos',
                'processosGrid',
                'colunasGridResumo',
                'visualizacao',
                'busca',
                'statusFiltro',
                'filtrosAvancados',
                'clientesParaFiltroProcessos',
                'totalAtivos',
                'etapas',
                'podeAlterarStatus',
                'podeExcluirLote',
                'mostrarSelecaoEmLote',
                'idsSelecaoLotePagina',
                'idsExclusaoLotePagina',
                'tiposProcessoModal',
                'clientesSuggestProcessoModal',
            ),
            $this->dadosSelectsProcesso($tiposProcessoModal),
        );

        if ($request->wantsJson()
            || $request->expectsJson()
            || str_contains((string) $request->header('Accept', ''), 'application/json')) {
            $total = $visualizacao === 'grid'
                ? (int) (($processosTodos ?? collect())->count() ?? 0)
                : (int) ($processos?->total() ?? 0);

            $temFiltros = ($busca !== '')
                || (is_string($statusFiltro) && $statusFiltro !== '')
                || (($filtrosAvancados['avancados_ativos'] ?? 0) > 0);

            $countText = $temFiltros
                ? trans_choice('{0} Nenhum resultado|{1} :count resultado|[2,*] :count resultados', $total, ['count' => $total])
                : trans_choice('{0} Nenhum processo ativo|{1} :count processo ativo|[2,*] :count processos ativos', (int) $totalAtivos, ['count' => $totalAtivos]);

            $tagsHtml = view('processos.partials.index-tags', [
                'busca' => $busca,
                'statusFiltro' => $statusFiltro,
                'filtrosAvancados' => $filtrosAvancados,
                'tiposProcessoModal' => $tiposProcessoModal,
                'clientesSuggestProcessoModal' => $clientesSuggestProcessoModal,
            ])->render();

            $listHtml = view('processos.partials.index-list', $viewData)->render();
            $paginationHtml = ($visualizacao === 'list' && $processos && $processos->hasPages())
                ? (string) $processos->links()
                : '';

            return response()->json([
                'count_text' => $countText,
                'avancados_ativos' => (int) ($filtrosAvancados['avancados_ativos'] ?? 0),
                'tags_html' => $tagsHtml,
                'list_html' => $listHtml,
                'pagination_html' => $paginationHtml,
            ]);
        }

        return view('processos.index', $viewData);
    }

    public function kanban(Request $request): View
    {
        $data = $this->kanbanBoardData($request);

        return view('processos.kanban', $data);
    }

    /** @return array{colunas: array, processos: Collection, podeMoverKanban: bool} */
    private function kanbanBoardData(Request $request): array
    {
        $this->authorize('viewAny', Processo::class);

        $empresaId = (int) $request->user()->empresa_id;

        $colunas = ProcessoStatus::kanbanOrder();

        $porColuna = collect();
        foreach ($colunas as $st) {
            $porColuna[$st->value] = Processo::query()
                ->where('status', $st)
                ->with(['cliente', 'tipoProcesso'])
                ->withCount([
                    'documentosChecklist as nx_docs_pendentes_count' => function ($q) {
                        $q->where(function ($q) {
                            $q->where('status', ProcessoDocumentoStatus::Pendente)
                                ->orWhere(function ($q) {
                                    $q->where('status', ProcessoDocumentoStatus::Enviado)
                                        ->whereDoesntHave('anexos')
                                        ->where('preenchido_via_modelo', false)
                                        ->where('declaracao_residencia_2g', false)
                                        ->where('declaracao_anexo_5h', false)
                                        ->where('declaracao_anexo_5d', false)
                                        ->where('declaracao_anexo_3d', false);
                                });
                        });
                    },
                ])
                ->orderByDesc('updated_at')
                ->limit(50)
                ->get();
        }

        return [
            'colunas' => $colunas,
            'processos' => $porColuna,
            'podeMoverKanban' => $request->user()->hasPermission('processos.alterar_status'),
        ];
    }

    public function show(Request $request, Processo $processo): View
    {
        $this->authorize('view', $processo);

        $empresaShow = $request->user()->empresa;
        if ($empresaShow) {
            $this->empresaProcessosDefaults->garantirTemplateBasico($empresaShow);
        }

        $processo->load([
            'cliente.embarcacoes',
            'cliente.anexos',
            'cliente.habilitacoes.anexos',
            'embarcacao.anexos',
            'tipoProcesso.documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $request->user()->empresa_id)->orderBy('documento_processo.ordem'),
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
            'postIts.user:id,name',
        ]);

        $this->checklistFichaSync->sync($processo);
        $this->chaAtestadoDispensaSync->sync($processo);
        $processo->unsetRelation('documentosChecklist');
        $processo->load([
            'documentosChecklist.documentoTipo',
            'documentosChecklist.anexos',
        ]);

        $progresso = $this->progresso->calcular($processo);
        $bloqueio = $this->statusService->motivoBloqueio($processo);

        $postItsInicial = $processo->postIts
            ->map(fn (ProcessoPostIt $p) => $this->serializarPostIt($p))
            ->values()
            ->all();

        $nxPostItsCfg = [
            'items' => $postItsInicial,
            'canEdit' => $request->user()->can('updateDocumento', $processo),
            'urls' => [
                'store' => route('processos.post-its.store', $processo),
                'update' => route('processos.post-its.update', [$processo, '__POST_IT__']),
                'destroy' => route('processos.post-its.destroy', [$processo, '__POST_IT__']),
            ],
            'msgConfirmDelete' => __('Remover esta nota?'),
            'msgError' => __('Não foi possível guardar. Tente de novo.'),
        ];

        return view('processos.show', [
            'processo' => $processo,
            'progresso' => $progresso,
            'motivoBloqueio' => $bloqueio,
            'nxPostItsCfg' => $nxPostItsCfg,
        ]);
    }

    public function updateStatus(UpdateProcessoStatusRequest $request, Processo $processo): RedirectResponse|JsonResponse
    {
        $this->authorize('updateStatus', $processo);

        $processo->loadMissing('tipoProcesso');

        $novo = ProcessoStatus::from($request->validated('status'));

        $confirmarCiencia = $request->boolean('confirmar_ciencia_pendencias_documentais');

        if (! $processo->aceitaDestinoStatus($novo)) {
            $msg = ProcessoStatus::mensagemTipoNaoAceitaAguardandoProva();

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors(['status' => $msg]);
        }

        if (! $this->statusService->podeAlterarStatus($processo, $novo, $confirmarCiencia)) {
            $msg = $this->statusService->motivoBloqueio($processo) ?? 'Não é possível alterar o status.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors(['status' => $msg]);
        }

        $processo->update(['status' => $novo]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status atualizado.',
                'status' => $novo->value,
            ]);
        }

        return back()->with('status', 'Status atualizado.');
    }

    public function storeAnexos(StoreMultiplosAnexosRequest $request, Processo $processo, ProcessoDocumento $documento): RedirectResponse|JsonResponse
    {
        $this->authorize('updateDocumento', $processo);

        if ((int) $documento->processo_id !== (int) $processo->id) {
            abort(404);
        }

        $n = $this->anexoService->armazenarVarios(
            $processo,
            $documento,
            $request->file('arquivos', []),
        );

        if ($n === 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Nenhum arquivo válido foi enviado.'),
                    'errors' => ['arquivos' => [__('Nenhum arquivo válido foi enviado.')]],
                ], 422);
            }

            return back()->withErrors(['arquivos' => 'Nenhum arquivo válido foi enviado.']);
        }

        $documento->refresh();
        $documento->load(['anexos', 'documentoTipo']);
        $processo->refresh();

        if ($request->wantsJson()) {
            return response()->json(
                $this->jsonRespostaDocumentoChecklistAposDispensa(
                    $processo,
                    $documento,
                    $n === 1 ? __('1 arquivo enviado.') : __(':count arquivos enviados.', ['count' => $n]),
                ),
            );
        }

        $this->checklistFichaSync->sync($processo);
        $this->chaAtestadoDispensaSync->sync($processo);

        return back()->with('status', $n === 1 ? '1 arquivo enviado.' : "{$n} arquivos enviados.");
    }

    public function destroyAnexo(Request $request, ProcessoDocumentoAnexo $anexo): RedirectResponse|JsonResponse
    {
        $anexo->loadMissing('processoDocumento.processo');
        $documento = $anexo->processoDocumento;
        $processo = $documento?->processo;
        abort_unless($documento && $processo, 404);

        $this->authorize('updateDocumento', $processo);

        $this->anexoService->remover($anexo);

        $documento->refresh();
        $documento->load(['anexos', 'documentoTipo']);
        $processo->refresh();

        if ($request->wantsJson()) {
            return response()->json(
                $this->jsonRespostaDocumentoChecklistAposDispensa($processo, $documento, __('Anexo removido.')),
            );
        }

        $this->checklistFichaSync->sync($processo);
        $this->chaAtestadoDispensaSync->sync($processo);

        return back()->with('status', 'Anexo removido.');
    }

    /**
     * @param  Collection<int, PlatformTipoProcesso>  $tipos
     * @return array{
     *     servicosPorCategoriaJson: Js,
     *     categoriasProcesso: list<TipoProcessoCategoria>,
     *     categoriaProcessoOld: ?string
     * }
     */
    private function dadosSelectsProcesso(Collection $tipos): array
    {
        $servicosPorCategoria = collect(TipoProcessoCategoria::cases())
            ->mapWithKeys(function (TipoProcessoCategoria $c) use ($tipos) {
                $tiposFiltrados = $tipos
                    ->filter(fn (PlatformTipoProcesso $t) => $t->categoria === $c);

                // Para categoria "embarcação", oculta serviços conforme catálogo (lista fornecida pelo cliente).
                if ($c === TipoProcessoCategoria::Embarcacao) {
                    $ocultar = collect(EmbarcacaoTipoServicoCatalogo::listaOrdenada())
                        ->pluck('slug')
                        ->map(fn ($s) => (string) $s)
                        ->filter()
                        ->all();

                    $tiposFiltrados = $tiposFiltrados
                        ->filter(fn (PlatformTipoProcesso $t) => ! in_array((string) ($t->slug ?? ''), $ocultar, true));
                }

                return [
                    $c->value => $tiposFiltrados
                        ->sortBy(fn (PlatformTipoProcesso $t) => [(int) ($t->ordem ?? 0), (string) ($t->nome ?? '')])
                        ->map(fn (PlatformTipoProcesso $t) => ['id' => $t->id, 'nome' => $t->nome])
                        ->values()
                        ->all(),
                ];
            });

        $categoriaOld = null;
        $tid = old('platform_tipo_processo_id');
        if ($tid !== null && $tid !== '') {
            $categoriaOld = $tipos->firstWhere('id', (int) $tid)?->categoria;
            $categoriaOld = $categoriaOld instanceof TipoProcessoCategoria
                ? $categoriaOld->value
                : (is_string($categoriaOld) && $categoriaOld !== '' ? $categoriaOld : null);
        }

        return [
            'servicosPorCategoriaJson' => Js::from($servicosPorCategoria),
            'categoriasProcesso' => TipoProcessoCategoria::cases(),
            'categoriaProcessoOld' => $categoriaOld,
        ];
    }

    public function updateDocumento(UpdateProcessoDocumentoRequest $request, Processo $processo, ProcessoDocumento $documento): RedirectResponse|JsonResponse
    {
        $this->authorize('updateDocumento', $processo);

        if ((int) $documento->processo_id !== (int) $processo->id) {
            abort(404);
        }

        $documento->loadMissing('documentoTipo');

        $validated = $request->validated();
        $status = ProcessoDocumentoStatus::from($validated['status']);
        $codigoTipo = (string) ($documento->documentoTipo?->codigo ?? '');
        $isResidencia = $codigoTipo === Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP;
        $isAnexo5h = Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigoTipo);
        $isAnexo5d = Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigoTipo);
        $isAnexo3d = Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigoTipo);
        $temModelo = ChecklistDocumentoModelo::tipoTemModelo($documento->documentoTipo);

        $data = [
            'status' => $status,
        ];
        if (array_key_exists('data_validade_documento', $validated)) {
            $data['data_validade_documento'] = $validated['data_validade_documento'];
        }

        if ($status === ProcessoDocumentoStatus::Pendente) {
            $data['satisfeito_via_ficha_embarcacao'] = false;
        }

        if ($temModelo && ($status === ProcessoDocumentoStatus::Pendente || $status === ProcessoDocumentoStatus::Fisico)) {
            $data['preenchido_via_modelo'] = false;
        }

        if ($isResidencia && ($status === ProcessoDocumentoStatus::Pendente || $status === ProcessoDocumentoStatus::Fisico)) {
            $data['declaracao_residencia_2g'] = false;
        }

        if ($isAnexo5h && ($status === ProcessoDocumentoStatus::Pendente || $status === ProcessoDocumentoStatus::Fisico)) {
            $data['declaracao_anexo_5h'] = false;
        }

        if ($isAnexo5d && ($status === ProcessoDocumentoStatus::Pendente || $status === ProcessoDocumentoStatus::Fisico)) {
            $data['declaracao_anexo_5d'] = false;
        }

        if ($isAnexo3d && ($status === ProcessoDocumentoStatus::Pendente || $status === ProcessoDocumentoStatus::Fisico)) {
            $data['declaracao_anexo_3d'] = false;
        }

        if ($status === ProcessoDocumentoStatus::Enviado) {
            if ($temModelo && array_key_exists('preenchido_via_modelo', $validated)) {
                $data['preenchido_via_modelo'] = (bool) $validated['preenchido_via_modelo'];
            }

            if ($isResidencia && array_key_exists('declaracao_residencia_2g', $validated)) {
                $data['declaracao_residencia_2g'] = (bool) $validated['declaracao_residencia_2g'];
            }

            if ($isAnexo5h && array_key_exists('declaracao_anexo_5h', $validated)) {
                $data['declaracao_anexo_5h'] = (bool) $validated['declaracao_anexo_5h'];
            }

            if ($isAnexo5d && array_key_exists('declaracao_anexo_5d', $validated)) {
                $data['declaracao_anexo_5d'] = (bool) $validated['declaracao_anexo_5d'];
            }

            if ($isAnexo3d && array_key_exists('declaracao_anexo_3d', $validated)) {
                $data['declaracao_anexo_3d'] = (bool) $validated['declaracao_anexo_3d'];
            }

            if ($temModelo) {
                $touched = array_key_exists('preenchido_via_modelo', $data)
                    || ($isResidencia && array_key_exists('declaracao_residencia_2g', $data))
                    || ($isAnexo5h && array_key_exists('declaracao_anexo_5h', $data))
                    || ($isAnexo5d && array_key_exists('declaracao_anexo_5d', $data))
                    || ($isAnexo3d && array_key_exists('declaracao_anexo_3d', $data));

                if ($touched) {
                    $effective = (bool) ($data['preenchido_via_modelo'] ?? false)
                        || ($isResidencia && (bool) ($data['declaracao_residencia_2g'] ?? false))
                        || ($isAnexo5h && (bool) ($data['declaracao_anexo_5h'] ?? false))
                        || ($isAnexo5d && (bool) ($data['declaracao_anexo_5d'] ?? false))
                        || ($isAnexo3d && (bool) ($data['declaracao_anexo_3d'] ?? false));
                    $data['preenchido_via_modelo'] = $effective;
                    if ($isResidencia) {
                        $data['declaracao_residencia_2g'] = $effective;
                    }
                    if ($isAnexo5h) {
                        $data['declaracao_anexo_5h'] = $effective;
                    }
                    if ($isAnexo5d) {
                        $data['declaracao_anexo_5d'] = $effective;
                    }
                    if ($isAnexo3d) {
                        $data['declaracao_anexo_3d'] = $effective;
                    }
                }
            }
        }

        $documento->update($data);

        $documento->refresh();
        $documento->load(['anexos', 'documentoTipo']);
        $processo->refresh();

        if ($request->wantsJson()) {
            return response()->json(
                $this->jsonRespostaDocumentoChecklistAposDispensa($processo, $documento, __('Documento atualizado.')),
            );
        }

        $this->checklistFichaSync->sync($processo);
        $this->chaAtestadoDispensaSync->sync($processo);

        return back()->with('status', 'Documento atualizado.');
    }
}
