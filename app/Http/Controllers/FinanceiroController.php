<?php

namespace App\Http\Controllers;

use App\Models\FinanceiroAdminDiretoLancamento;
use App\Models\FinanceiroAulaLancamento;
use App\Models\FinanceiroDespesaLancamento;
use App\Models\FinanceiroLoteEngenharia;
use App\Models\FinanceiroLoteEngenhariaItem;
use App\Models\FinanceiroLoteParceria;
use App\Models\FinanceiroLoteParceriaItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceiroController extends Controller
{
    public function index(Request $request): View
    {
        return view('financeiro.index');
    }

    public function apiResumo(Request $request): JsonResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['nullable', 'string'],
        ]);

        $ano = (int) $validated['ano'];
        $mesRaw = $validated['mes'] ?? 'todos';
        $mes = $mesRaw === 'todos' ? null : (int) $mesRaw;
        if ($mes !== null && ($mes < 1 || $mes > 12)) {
            $mes = null;
        }

        $filtroData = function ($q, string $col) use ($ano, $mes) {
            $q->whereYear($col, $ano);
            if ($mes !== null) {
                $q->whereMonth($col, $mes);
            }
        };

        $aulas = FinanceiroAulaLancamento::query()
            ->where('empresa_id', $empresaId)
            ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
            ->selectRaw('COUNT(*) as qtd, COALESCE(SUM(receita),0) as receita, COALESCE(SUM(lucro),0) as lucro, COALESCE(SUM(qtd_alunos),0) as alunos')
            ->first();

        $admin = FinanceiroAdminDiretoLancamento::query()
            ->where('empresa_id', $empresaId)
            ->tap(fn ($q) => $filtroData($q, 'data_servico'))
            ->selectRaw('COUNT(*) as qtd, COALESCE(SUM(receita),0) as receita, COALESCE(SUM(lucro),0) as lucro, COALESCE(SUM(CASE WHEN status_pagamento = \"Em aberto\" THEN receita ELSE 0 END),0) as aberto, COALESCE(SUM(CASE WHEN nota_emitida = 0 THEN receita ELSE 0 END),0) as nf_pendente, COALESCE(SUM(CASE WHEN nota_emitida = 1 THEN receita ELSE 0 END),0) as nf_emitida')
            ->first();

        $despesas = FinanceiroDespesaLancamento::query()
            ->where('empresa_id', $empresaId)
            ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
            ->selectRaw('COUNT(*) as qtd, COALESCE(SUM(valor),0) as total')
            ->first();

        $parcerias = FinanceiroLoteParceriaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
            ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
            ->selectRaw('COUNT(*) as qtd, COALESCE(SUM(receita),0) as receita, COALESCE(SUM(lucro),0) as lucro, COALESCE(SUM(CASE WHEN nota_emitida = 0 THEN receita ELSE 0 END),0) as nf_pendente, COALESCE(SUM(CASE WHEN nota_emitida = 1 THEN receita ELSE 0 END),0) as nf_emitida')
            ->first();

        $engenharia = FinanceiroLoteEngenhariaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
            ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
            ->selectRaw('COUNT(*) as qtd, COALESCE(SUM(receita),0) as receita, COALESCE(SUM(lucro),0) as lucro, COALESCE(SUM(CASE WHEN nota_emitida = 0 THEN receita ELSE 0 END),0) as nf_pendente, COALESCE(SUM(CASE WHEN nota_emitida = 1 THEN receita ELSE 0 END),0) as nf_emitida')
            ->first();

        $notasPendentesValor = (float) (($admin->nf_pendente ?? 0) + ($parcerias->nf_pendente ?? 0) + ($engenharia->nf_pendente ?? 0));
        $notasEmitidasValor = (float) (($admin->nf_emitida ?? 0) + ($parcerias->nf_emitida ?? 0) + ($engenharia->nf_emitida ?? 0));
        $qtdNotasPendentes = (int) (
            FinanceiroAdminDiretoLancamento::query()
                ->where('empresa_id', $empresaId)
                ->tap(fn ($q) => $filtroData($q, 'data_servico'))
                ->where('nota_emitida', false)
                ->count()
            +
            FinanceiroLoteParceriaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
                ->where('nota_emitida', false)
                ->count()
            + FinanceiroLoteEngenhariaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
                ->where('nota_emitida', false)
                ->count()
        );
        $qtdNotasEmitidas = (int) (
            FinanceiroAdminDiretoLancamento::query()
                ->where('empresa_id', $empresaId)
                ->tap(fn ($q) => $filtroData($q, 'data_servico'))
                ->where('nota_emitida', true)
                ->count()
            +
            FinanceiroLoteParceriaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
                ->where('nota_emitida', true)
                ->count()
            + FinanceiroLoteEngenhariaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->tap(fn ($q) => $filtroData($q, 'data_lancamento'))
                ->where('nota_emitida', true)
                ->count()
        );

        return response()->json([
            'periodo' => ['ano' => $ano, 'mes' => $mesRaw],
            'aulas' => [
                'qtd' => (int) ($aulas->qtd ?? 0),
                'alunos' => (int) ($aulas->alunos ?? 0),
                'receita' => (float) ($aulas->receita ?? 0),
                'lucro' => (float) ($aulas->lucro ?? 0),
            ],
            'admin_direto' => [
                'qtd' => (int) ($admin->qtd ?? 0),
                'receita' => (float) ($admin->receita ?? 0),
                'lucro' => (float) ($admin->lucro ?? 0),
                'aberto' => (float) ($admin->aberto ?? 0),
            ],
            'parcerias' => [
                'qtd' => (int) ($parcerias->qtd ?? 0),
                'receita' => (float) ($parcerias->receita ?? 0),
                'lucro' => (float) ($parcerias->lucro ?? 0),
            ],
            'engenharia' => [
                'qtd' => (int) ($engenharia->qtd ?? 0),
                'receita' => (float) ($engenharia->receita ?? 0),
                'lucro' => (float) ($engenharia->lucro ?? 0),
            ],
            'despesas' => [
                'qtd' => (int) ($despesas->qtd ?? 0),
                'total' => (float) ($despesas->total ?? 0),
            ],
            'notas' => [
                'pendentes_qtd' => $qtdNotasPendentes,
                'pendentes_valor' => $notasPendentesValor,
                'emitidas_qtd' => $qtdNotasEmitidas,
                'emitidas_valor' => $notasEmitidasValor,
            ],
        ]);
    }

    public function apiGraficoCaixa(Request $request): JsonResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'ano' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);
        $ano = (int) $validated['ano'];

        $lucros = array_fill(0, 12, 0.0);
        $despesas = array_fill(0, 12, 0.0);

        $sumByMonth = function (string $table, string $dateCol, string $sumCol, callable $addFn) use ($empresaId, $ano) {
            DB::table($table)
                ->where('empresa_id', $empresaId)
                ->whereYear($dateCol, $ano)
                ->selectRaw("MONTH($dateCol) as m, COALESCE(SUM($sumCol),0) as s")
                ->groupByRaw("MONTH($dateCol)")
                ->get()
                ->each(function ($row) use ($addFn) {
                    $m = (int) $row->m;
                    $s = (float) $row->s;
                    if ($m >= 1 && $m <= 12) {
                        $addFn($m - 1, $s);
                    }
                });
        };

        $sumByMonth('financeiro_aula_lancamentos', 'data_lancamento', 'lucro', function (int $idx, float $s) use (&$lucros) {
            $lucros[$idx] += $s;
        });
        $sumByMonth('financeiro_admin_direto_lancamentos', 'data_servico', 'lucro', function (int $idx, float $s) use (&$lucros) {
            $lucros[$idx] += $s;
        });

        FinanceiroLoteParceriaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereYear('data_lancamento', $ano)
            ->selectRaw('MONTH(data_lancamento) as m, COALESCE(SUM(lucro),0) as s')
            ->groupByRaw('MONTH(data_lancamento)')
            ->get()
            ->each(function ($row) use (&$lucros) {
                $m = (int) $row->m;
                if ($m >= 1 && $m <= 12) {
                    $lucros[$m - 1] += (float) $row->s;
                }
            });

        FinanceiroLoteEngenhariaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereYear('data_lancamento', $ano)
            ->selectRaw('MONTH(data_lancamento) as m, COALESCE(SUM(lucro),0) as s')
            ->groupByRaw('MONTH(data_lancamento)')
            ->get()
            ->each(function ($row) use (&$lucros) {
                $m = (int) $row->m;
                if ($m >= 1 && $m <= 12) {
                    $lucros[$m - 1] += (float) $row->s;
                }
            });

        $sumByMonth('financeiro_despesa_lancamentos', 'data_lancamento', 'valor', function (int $idx, float $s) use (&$lucros, &$despesas) {
            $lucros[$idx] -= $s;
            $despesas[$idx] += $s;
        });

        return response()->json([
            'ano' => $ano,
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            'lucro_liquido' => array_map(fn ($v) => round((float) $v, 2), $lucros),
            'despesas' => array_map(fn ($v) => round((float) $v, 2), $despesas),
        ]);
    }

    public function apiGraficoServicos(Request $request): JsonResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'ano' => ['required', 'string'],
            'mes' => ['nullable', 'string'],
        ]);

        $anoRaw = $validated['ano'];
        $mesRaw = $validated['mes'] ?? 'todos';
        $ano = $anoRaw === 'todos' ? null : (int) $anoRaw;
        $mes = $mesRaw === 'todos' ? null : (int) $mesRaw;
        if ($ano !== null && ($ano < 2000 || $ano > 2100)) {
            $ano = null;
        }
        if ($mes !== null && ($mes < 1 || $mes > 12)) {
            $mes = null;
        }

        $contagem = [];

        $add = function (string $servico, int $n = 1) use (&$contagem) {
            $k = trim($servico);
            if ($k === '') {
                $k = 'Outros';
            }
            $contagem[$k] = ($contagem[$k] ?? 0) + $n;
        };

        $qAdmin = FinanceiroAdminDiretoLancamento::query()
            ->where('empresa_id', $empresaId);
        if ($ano !== null) {
            $qAdmin->whereYear('data_servico', $ano);
        }
        if ($mes !== null) {
            $qAdmin->whereMonth('data_servico', $mes);
        }
        $qAdmin->selectRaw('servico_tipo, COUNT(*) as n')
            ->groupBy('servico_tipo')
            ->get()
            ->each(fn ($r) => $add((string) $r->servico_tipo, (int) $r->n));

        $qParc = FinanceiroLoteParceriaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId));
        if ($ano !== null) {
            $qParc->whereYear('data_lancamento', $ano);
        }
        if ($mes !== null) {
            $qParc->whereMonth('data_lancamento', $mes);
        }
        $qParc->selectRaw('servico_tipo, COUNT(*) as n')
            ->groupBy('servico_tipo')
            ->get()
            ->each(fn ($r) => $add((string) $r->servico_tipo, (int) $r->n));

        $qEng = FinanceiroLoteEngenhariaItem::query()
            ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId));
        if ($ano !== null) {
            $qEng->whereYear('data_lancamento', $ano);
        }
        if ($mes !== null) {
            $qEng->whereMonth('data_lancamento', $mes);
        }
        $qEng->selectRaw('servico_tipo, COUNT(*) as n')
            ->groupBy('servico_tipo')
            ->get()
            ->each(fn ($r) => $add((string) $r->servico_tipo, (int) $r->n));

        arsort($contagem);

        return response()->json([
            'ano' => $anoRaw,
            'mes' => $mesRaw,
            'labels' => array_keys($contagem),
            'values' => array_values($contagem),
        ]);
    }

    public function apiLista(Request $request, string $modulo): JsonResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'ano' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'sort' => ['nullable', 'string', 'max:32'],
            'dir' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $ano = isset($validated['ano']) ? (int) $validated['ano'] : null;
        $mesRaw = $validated['mes'] ?? null;
        $mes = ($mesRaw === null || $mesRaw === 'todos') ? null : (int) $mesRaw;
        if ($mes !== null && ($mes < 1 || $mes > 12)) {
            $mes = null;
        }
        $limit = (int) ($validated['limit'] ?? 50);
        $page = (int) ($validated['page'] ?? 1);
        $sort = isset($validated['sort']) ? trim((string) $validated['sort']) : null;
        $dir = strtolower((string) ($validated['dir'] ?? 'desc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }

        $applyDateFilter = function ($query, string $col) use ($ano, $mes) {
            if ($ano !== null) {
                $query->whereYear($col, $ano);
            }
            if ($mes !== null) {
                $query->whereMonth($col, $mes);
            }
        };

        $like = $q !== '' ? '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%' : null;

        if ($modulo === 'aulas') {
            $query = FinanceiroAulaLancamento::query()
                ->where('empresa_id', $empresaId);
            $applyDateFilter($query, 'data_lancamento');
            if ($like) {
                // aulas não tem texto; filtra por data (string) e qtd_alunos
                $query->where(function ($qq) use ($like) {
                    $qq->where('data_lancamento', 'like', $like)
                        ->orWhere('qtd_alunos', 'like', $like);
                });
            }

            $this->applyFinanceiroListaOrder($query, 'aulas', $sort, $dir);
            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'modulo' => $modulo,
                'items' => $paginator->getCollection()->map(fn (FinanceiroAulaLancamento $r) => [
                    'id' => (int) $r->id,
                    'data_lancamento' => $r->data_lancamento?->format('Y-m-d'),
                    'data_pagamento' => $r->data_pagamento?->format('Y-m-d'),
                    'qtd_alunos' => (int) $r->qtd_alunos,
                    'receita' => (float) $r->receita,
                    'custo_barco' => (float) $r->custo_barco,
                    'custo_combustivel' => (float) $r->custo_combustivel,
                    'custo_cafe' => (float) $r->custo_cafe,
                    'custo_ingresso' => (float) $r->custo_ingresso,
                    'taxa_marinha' => (float) $r->taxa_marinha,
                    'custo_total' => (float) $r->custo_total,
                    'lucro' => (float) $r->lucro,
                ])->all(),
                'meta' => $this->paginationMeta($paginator),
            ]);
        }

        if ($modulo === 'admin_direto') {
            $query = FinanceiroAdminDiretoLancamento::query()
                ->where('empresa_id', $empresaId);
            $applyDateFilter($query, 'data_servico');
            if ($like) {
                $query->where(function ($qq) use ($like) {
                    $qq->where('cliente_nome', 'like', $like)
                        ->orWhere('servico_tipo', 'like', $like)
                        ->orWhere('status_pagamento', 'like', $like);
                });
            }

            $this->applyFinanceiroListaOrder($query, 'admin_direto', $sort, $dir);
            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'modulo' => $modulo,
                'items' => $paginator->getCollection()->map(fn (FinanceiroAdminDiretoLancamento $r) => [
                    'id' => (int) $r->id,
                    'data_servico' => $r->data_servico?->format('Y-m-d'),
                    'data_pagamento' => $r->data_pagamento?->format('Y-m-d'),
                    'cliente_nome' => (string) $r->cliente_nome,
                    'servico_tipo' => (string) $r->servico_tipo,
                    'status_pagamento' => (string) $r->status_pagamento,
                    'receita' => (float) $r->receita,
                    'taxa_marinha' => (float) $r->taxa_marinha,
                    'custo_envio' => (float) $r->custo_envio,
                    'custo_total' => (float) $r->custo_total,
                    'lucro' => (float) $r->lucro,
                    'tem_comprovante' => (bool) ($r->comprovante_path),
                    'nota_emitida' => (bool) $r->nota_emitida,
                    'comprovante_url' => $r->comprovante_path
                        ? URL::signedRoute('financeiro.anexo.admin_direto', ['lancamento' => $r])
                        : null,
                ])->all(),
                'meta' => $this->paginationMeta($paginator),
            ]);
        }

        if ($modulo === 'despesas') {
            $query = FinanceiroDespesaLancamento::query()
                ->where('empresa_id', $empresaId);
            $applyDateFilter($query, 'data_lancamento');
            if ($like) {
                $query->where('descricao', 'like', $like);
            }

            $this->applyFinanceiroListaOrder($query, 'despesas', $sort, $dir);
            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'modulo' => $modulo,
                'items' => $paginator->getCollection()->map(fn (FinanceiroDespesaLancamento $r) => [
                    'id' => (int) $r->id,
                    'data_lancamento' => $r->data_lancamento?->format('Y-m-d'),
                    'data_pagamento' => $r->data_pagamento?->format('Y-m-d'),
                    'descricao' => (string) $r->descricao,
                    'valor' => (float) $r->valor,
                    'tem_nota' => (bool) ($r->nota_path),
                    'nota_url' => $r->nota_path
                        ? URL::signedRoute('financeiro.anexo.despesa', ['lancamento' => $r])
                        : null,
                    'fixa_grupo_id' => $r->fixa_grupo_id ? (string) $r->fixa_grupo_id : null,
                ])->all(),
                'meta' => $this->paginationMeta($paginator),
            ]);
        }

        if ($modulo === 'parcerias') {
            $query = FinanceiroLoteParceria::query()
                ->where('empresa_id', $empresaId);
            if ($ano !== null) {
                $query->where('mes_referencia', 'like', $ano.'-%');
            }
            if ($mes !== null) {
                $query->where('mes_referencia', sprintf('%04d-%02d', $ano ?? (int) date('Y'), $mes));
            }
            if ($like) {
                $query->where('empresa_parceira', 'like', $like);
            }

            $query->withCount('items')->with('items');
            $this->applyFinanceiroListaOrder($query, 'parcerias', $sort, $dir);
            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'modulo' => $modulo,
                'items' => $paginator->getCollection()->map(fn (FinanceiroLoteParceria $r) => [
                    'id' => (int) $r->id,
                    'mes_referencia' => (string) $r->mes_referencia,
                    'empresa_parceira' => (string) $r->empresa_parceira,
                    'status_pagamento' => (string) $r->status_pagamento,
                    'items_count' => (int) ($r->items_count ?? 0),
                    'receita_total' => (float) $r->items->sum('receita'),
                    'lucro_total' => (float) $r->items->sum('lucro'),
                    'tem_comprovante' => (bool) ($r->comprovante_path),
                    'comprovante_url' => $r->comprovante_path
                        ? URL::signedRoute('financeiro.anexo.lote_parceria', ['lote' => $r])
                        : null,
                    'servicos' => $r->items->map(fn (FinanceiroLoteParceriaItem $item) => [
                        'id' => (int) $item->id,
                        'data_lancamento' => $item->data_lancamento?->format('Y-m-d'),
                        'data_pagamento' => $item->data_pagamento?->format('Y-m-d'),
                        'cliente_nome' => (string) $item->cliente_nome,
                        'servico_tipo' => (string) $item->servico_tipo,
                        'receita' => (float) $item->receita,
                        'taxa_marinha' => (float) $item->taxa_marinha,
                        'custo_envio' => (float) $item->custo_envio,
                        'custo_total' => (float) $item->custo_total,
                        'lucro' => (float) $item->lucro,
                        'nota_emitida' => (bool) $item->nota_emitida,
                    ])->values()->all(),
                ])->all(),
                'meta' => $this->paginationMeta($paginator),
            ]);
        }

        if ($modulo === 'engenharia') {
            $query = FinanceiroLoteEngenharia::query()
                ->where('empresa_id', $empresaId);
            if ($ano !== null) {
                $query->where('mes_referencia', 'like', $ano.'-%');
            }
            if ($mes !== null) {
                $query->where('mes_referencia', sprintf('%04d-%02d', $ano ?? (int) date('Y'), $mes));
            }
            if ($like) {
                $query->where('empresa_parceira', 'like', $like);
            }

            $query->withCount('items')->with('items');
            $this->applyFinanceiroListaOrder($query, 'engenharia', $sort, $dir);
            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'modulo' => $modulo,
                'items' => $paginator->getCollection()->map(fn (FinanceiroLoteEngenharia $r) => [
                    'id' => (int) $r->id,
                    'mes_referencia' => (string) $r->mes_referencia,
                    'empresa_parceira' => (string) $r->empresa_parceira,
                    'status_pagamento' => (string) $r->status_pagamento,
                    'items_count' => (int) ($r->items_count ?? 0),
                    'receita_total' => (float) $r->items->sum('receita'),
                    'lucro_total' => (float) $r->items->sum('lucro'),
                    'tem_comprovante' => (bool) ($r->comprovante_path),
                    'comprovante_url' => $r->comprovante_path
                        ? URL::signedRoute('financeiro.anexo.lote_engenharia', ['lote' => $r])
                        : null,
                    'servicos' => $r->items->map(fn (FinanceiroLoteEngenhariaItem $item) => [
                        'id' => (int) $item->id,
                        'data_lancamento' => $item->data_lancamento?->format('Y-m-d'),
                        'data_pagamento' => $item->data_pagamento?->format('Y-m-d'),
                        'cliente_nome' => (string) $item->cliente_nome,
                        'servico_tipo' => (string) $item->servico_tipo,
                        'receita' => (float) $item->receita,
                        'custos_extras' => (float) $item->custos_extras,
                        'custo_total' => (float) $item->custo_total,
                        'lucro' => (float) $item->lucro,
                        'nota_emitida' => (bool) $item->nota_emitida,
                    ])->values()->all(),
                ])->all(),
                'meta' => $this->paginationMeta($paginator),
            ]);
        }

        abort(404);
    }

    public function apiNotas(Request $request): JsonResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'ano' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'mes' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'sort' => ['nullable', 'string', 'in:data,receita,modulo,cliente,servico'],
            'dir' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $ano = isset($validated['ano']) ? (int) $validated['ano'] : null;
        $mesRaw = $validated['mes'] ?? null;
        $mes = ($mesRaw === null || $mesRaw === 'todos') ? null : (int) $mesRaw;
        $status = $validated['status'] ?? 'todos';
        $q = trim((string) ($validated['q'] ?? ''));
        $like = $q !== '' ? mb_strtolower($q) : '';
        $limit = (int) ($validated['limit'] ?? 25);
        $page = (int) ($validated['page'] ?? 1);
        $sort = (string) ($validated['sort'] ?? 'data');
        $dir = strtolower((string) ($validated['dir'] ?? 'desc'));
        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }
        $allowedSort = ['data', 'receita', 'modulo', 'cliente', 'servico'];
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'data';
        }

        $rows = collect();

        $admin = FinanceiroAdminDiretoLancamento::query()
            ->where('empresa_id', $empresaId)
            ->when($ano !== null, fn ($qq) => $qq->whereYear('data_servico', $ano))
            ->when($mes !== null && $mes >= 1 && $mes <= 12, fn ($qq) => $qq->whereMonth('data_servico', $mes))
            ->get()
            ->map(fn (FinanceiroAdminDiretoLancamento $r) => [
                'modulo' => 'Admin Direto',
                'record_type' => 'admin_direto',
                'record_id' => (int) $r->id,
                'parent_id' => null,
                'data' => $r->data_servico?->format('Y-m-d'),
                'cliente_nome' => (string) $r->cliente_nome,
                'servico_tipo' => (string) $r->servico_tipo,
                'receita' => (float) $r->receita,
                'nota_emitida' => (bool) $r->nota_emitida,
            ]);

        $parcerias = FinanceiroLoteParceriaItem::query()
            ->whereHas('lote', fn ($qq) => $qq->where('empresa_id', $empresaId))
            ->when($ano !== null, fn ($qq) => $qq->whereYear('data_lancamento', $ano))
            ->when($mes !== null && $mes >= 1 && $mes <= 12, fn ($qq) => $qq->whereMonth('data_lancamento', $mes))
            ->with('lote:id,empresa_parceira')
            ->get()
            ->map(fn (FinanceiroLoteParceriaItem $r) => [
                'modulo' => 'Parcerias B2B',
                'record_type' => 'parceria_item',
                'record_id' => (int) $r->id,
                'parent_id' => (int) $r->lote_id,
                'data' => $r->data_lancamento?->format('Y-m-d'),
                'cliente_nome' => (string) $r->cliente_nome,
                'servico_tipo' => (string) $r->servico_tipo,
                'receita' => (float) $r->receita,
                'nota_emitida' => (bool) $r->nota_emitida,
                'empresa_parceira' => (string) ($r->lote?->empresa_parceira ?? ''),
            ]);

        $engenharia = FinanceiroLoteEngenhariaItem::query()
            ->whereHas('lote', fn ($qq) => $qq->where('empresa_id', $empresaId))
            ->when($ano !== null, fn ($qq) => $qq->whereYear('data_lancamento', $ano))
            ->when($mes !== null && $mes >= 1 && $mes <= 12, fn ($qq) => $qq->whereMonth('data_lancamento', $mes))
            ->with('lote:id,empresa_parceira')
            ->get()
            ->map(fn (FinanceiroLoteEngenhariaItem $r) => [
                'modulo' => 'Engenharia Naval',
                'record_type' => 'engenharia_item',
                'record_id' => (int) $r->id,
                'parent_id' => (int) $r->lote_id,
                'data' => $r->data_lancamento?->format('Y-m-d'),
                'cliente_nome' => (string) $r->cliente_nome,
                'servico_tipo' => (string) $r->servico_tipo,
                'receita' => (float) $r->receita,
                'nota_emitida' => (bool) $r->nota_emitida,
                'empresa_parceira' => (string) ($r->lote?->empresa_parceira ?? ''),
            ]);

        $rows = $rows->concat($admin)->concat($parcerias)->concat($engenharia);

        if ($status !== 'todos') {
            $bool = $status === 'true';
            $rows = $rows->where('nota_emitida', $bool);
        }

        if ($like !== '') {
            $rows = $rows->filter(function (array $row) use ($like) {
                return str_contains(mb_strtolower(($row['cliente_nome'] ?? '').' '.($row['servico_tipo'] ?? '').' '.($row['empresa_parceira'] ?? '')), $like);
            });
        }

        $total = $rows->count();
        $mul = $dir === 'desc' ? -1 : 1;
        $sorted = $rows->sort(function (array $a, array $b) use ($sort, $mul) {
            $va = $this->notasSortComparable($a, $sort);
            $vb = $this->notasSortComparable($b, $sort);
            if ($va === $vb) {
                return ($a['record_id'] ?? 0) <=> ($b['record_id'] ?? 0);
            }
            if (is_float($va) && is_float($vb)) {
                $cmp = $va <=> $vb;
            } else {
                $cmp = strcmp((string) $va, (string) $vb);
            }

            return $mul * $cmp;
        })->values();

        $items = $sorted
            ->slice(($page - 1) * $limit, $limit)
            ->values();

        return response()->json([
            'items' => $items->all(),
            'meta' => $this->paginationMetaFromValues($total, $page, $limit, $items->count()),
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function applyFinanceiroListaOrder($query, string $modulo, ?string $sort, string $dir): void
    {
        $maps = [
            'aulas' => [
                'data_lancamento' => 'data_lancamento',
                'receita' => 'receita',
                'lucro' => 'lucro',
                'custo_total' => 'custo_total',
                'qtd_alunos' => 'qtd_alunos',
            ],
            'admin_direto' => [
                'data_servico' => 'data_servico',
                'data_pagamento' => 'data_pagamento',
                'cliente_nome' => 'cliente_nome',
                'servico_tipo' => 'servico_tipo',
                'status_pagamento' => 'status_pagamento',
                'receita' => 'receita',
                'lucro' => 'lucro',
                'custo_total' => 'custo_total',
            ],
            'despesas' => [
                'data_lancamento' => 'data_lancamento',
                'valor' => 'valor',
                'descricao' => 'descricao',
            ],
            'parcerias' => [
                'mes_referencia' => 'mes_referencia',
                'empresa_parceira' => 'empresa_parceira',
                'status_pagamento' => 'status_pagamento',
            ],
            'engenharia' => [
                'mes_referencia' => 'mes_referencia',
                'empresa_parceira' => 'empresa_parceira',
                'status_pagamento' => 'status_pagamento',
            ],
        ];

        $defaults = [
            'aulas' => 'data_lancamento',
            'admin_direto' => 'data_servico',
            'despesas' => 'data_lancamento',
            'parcerias' => 'mes_referencia',
            'engenharia' => 'mes_referencia',
        ];

        $columnMap = $maps[$modulo] ?? [];
        $defaultCol = $defaults[$modulo] ?? 'id';

        $col = ($sort !== null && $sort !== '' && array_key_exists($sort, $columnMap))
            ? $columnMap[$sort]
            : $defaultCol;

        $query->orderBy($col, $dir)->orderBy('id', $dir);
    }

    private function notasSortComparable(array $row, string $sort): float|string
    {
        return match ($sort) {
            'receita' => (float) ($row['receita'] ?? 0),
            'modulo' => mb_strtolower((string) ($row['modulo'] ?? '')),
            'cliente' => mb_strtolower((string) (trim((string) ($row['cliente_nome'] ?? '')) !== ''
                ? $row['cliente_nome']
                : ($row['empresa_parceira'] ?? ''))),
            'servico' => mb_strtolower((string) ($row['servico_tipo'] ?? '')),
            default => (string) ($row['data'] ?? ''),
        };
    }

    private function paginationMeta($paginator): array
    {
        return [
            'page' => (int) $paginator->currentPage(),
            'per_page' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'last_page' => (int) $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more' => (bool) $paginator->hasMorePages(),
        ];
    }

    private function paginationMetaFromValues(int $total, int $page, int $perPage, int $count): array
    {
        $lastPage = max(1, (int) ceil($total / max(1, $perPage)));
        $from = $total === 0 ? null : (($page - 1) * $perPage) + 1;
        $to = $total === 0 ? null : $from + $count - 1;

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
            'has_more' => $page < $lastPage,
        ];
    }

    public function exportAulasCsv(Request $request): StreamedResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $filename = 'aulas.csv';

        return Response::streamDownload(function () use ($empresaId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['DataLancamento', 'DataPagamento', 'Alunos', 'Receita', 'Custos', 'Lucro'], ';');
            FinanceiroAulaLancamento::query()
                ->where('empresa_id', $empresaId)
                ->orderByDesc('data_lancamento')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            optional($r->data_lancamento)->format('Y-m-d'),
                            optional($r->data_pagamento)->format('Y-m-d'),
                            (int) $r->qtd_alunos,
                            (string) $r->receita,
                            (string) $r->custo_total,
                            (string) $r->lucro,
                        ], ';');
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportAdminDiretoCsv(Request $request): StreamedResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $filename = 'admin_direto.csv';

        return Response::streamDownload(function () use ($empresaId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['DataServico', 'DataPagamento', 'Cliente', 'Servico', 'Status', 'Receita', 'Custos', 'Lucro'], ';');
            FinanceiroAdminDiretoLancamento::query()
                ->where('empresa_id', $empresaId)
                ->orderByDesc('data_servico')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            optional($r->data_servico)->format('Y-m-d'),
                            optional($r->data_pagamento)->format('Y-m-d'),
                            (string) $r->cliente_nome,
                            (string) $r->servico_tipo,
                            (string) $r->status_pagamento,
                            (string) $r->receita,
                            (string) $r->custo_total,
                            (string) $r->lucro,
                        ], ';');
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportDespesasCsv(Request $request): StreamedResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $filename = 'despesas.csv';

        return Response::streamDownload(function () use ($empresaId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['DataLancamento', 'DataPagamento', 'Descricao', 'Valor'], ';');
            FinanceiroDespesaLancamento::query()
                ->where('empresa_id', $empresaId)
                ->orderByDesc('data_lancamento')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            optional($r->data_lancamento)->format('Y-m-d'),
                            optional($r->data_pagamento)->format('Y-m-d'),
                            (string) $r->descricao,
                            (string) $r->valor,
                        ], ';');
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportParceriasCsv(Request $request): StreamedResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $filename = 'parcerias_b2b.csv';

        return Response::streamDownload(function () use ($empresaId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['MesReferencia', 'EmpresaParceira', 'StatusLote', 'DataLancamento', 'DataPagamento', 'Cliente', 'Servico', 'Receita', 'Custos', 'Lucro', 'NotaEmitida'], ';');

            FinanceiroLoteParceria::query()
                ->where('empresa_id', $empresaId)
                ->orderByDesc('mes_referencia')
                ->with('items')
                ->chunk(100, function ($lotes) use ($out) {
                    foreach ($lotes as $lote) {
                        $items = $lote->items ?? collect();
                        if ($items->isEmpty()) {
                            fputcsv($out, [
                                (string) $lote->mes_referencia,
                                (string) $lote->empresa_parceira,
                                (string) $lote->status_pagamento,
                                '', '', '', '', '', '', '', '',
                            ], ';');
                            continue;
                        }
                        foreach ($items as $r) {
                            fputcsv($out, [
                                (string) $lote->mes_referencia,
                                (string) $lote->empresa_parceira,
                                (string) $lote->status_pagamento,
                                optional($r->data_lancamento)->format('Y-m-d'),
                                optional($r->data_pagamento)->format('Y-m-d'),
                                (string) $r->cliente_nome,
                                (string) $r->servico_tipo,
                                (string) $r->receita,
                                (string) $r->custo_total,
                                (string) $r->lucro,
                                $r->nota_emitida ? '1' : '0',
                            ], ';');
                        }
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportEngenhariaCsv(Request $request): StreamedResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $filename = 'engenharia.csv';

        return Response::streamDownload(function () use ($empresaId) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['MesReferencia', 'EmpresaParceira', 'StatusLote', 'DataLancamento', 'DataPagamento', 'Cliente', 'Servico', 'Receita', 'Custos', 'Lucro', 'NotaEmitida'], ';');

            FinanceiroLoteEngenharia::query()
                ->where('empresa_id', $empresaId)
                ->orderByDesc('mes_referencia')
                ->with('items')
                ->chunk(100, function ($lotes) use ($out) {
                    foreach ($lotes as $lote) {
                        $items = $lote->items ?? collect();
                        if ($items->isEmpty()) {
                            fputcsv($out, [
                                (string) $lote->mes_referencia,
                                (string) $lote->empresa_parceira,
                                (string) $lote->status_pagamento,
                                '', '', '', '', '', '', '', '',
                            ], ';');
                            continue;
                        }
                        foreach ($items as $r) {
                            fputcsv($out, [
                                (string) $lote->mes_referencia,
                                (string) $lote->empresa_parceira,
                                (string) $lote->status_pagamento,
                                optional($r->data_lancamento)->format('Y-m-d'),
                                optional($r->data_pagamento)->format('Y-m-d'),
                                (string) $r->cliente_nome,
                                (string) $r->servico_tipo,
                                (string) $r->receita,
                                (string) $r->custo_total,
                                (string) $r->lucro,
                                $r->nota_emitida ? '1' : '0',
                            ], ';');
                        }
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function uploadAdminDiretoComprovante(Request $request, FinanceiroAdminDiretoLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'arquivo' => ['required', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $file = $validated['arquivo'];
        $path = $file->storePublicly("financeiro/{$empresaId}/admin-direto");
        $lancamento->update(['comprovante_path' => $path]);

        return back()->with('status', __('Comprovante anexado.'));
    }

    public function uploadDespesaNota(Request $request, FinanceiroDespesaLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'arquivo' => ['required', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $file = $validated['arquivo'];
        $path = $file->storePublicly("financeiro/{$empresaId}/despesas");
        $lancamento->update(['nota_path' => $path]);

        return back()->with('status', __('Nota anexada.'));
    }

    public function uploadLoteParceriaComprovante(Request $request, FinanceiroLoteParceria $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'arquivo' => ['required', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $file = $validated['arquivo'];
        $path = $file->storePublicly("financeiro/{$empresaId}/parcerias");
        $lote->update(['comprovante_path' => $path]);

        return back()->with('status', __('Comprovante anexado.'));
    }

    public function uploadLoteEngenhariaComprovante(Request $request, FinanceiroLoteEngenharia $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'arquivo' => ['required', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $file = $validated['arquivo'];
        $path = $file->storePublicly("financeiro/{$empresaId}/engenharia");
        $lote->update(['comprovante_path' => $path]);

        return back()->with('status', __('Comprovante anexado.'));
    }

    public function downloadAdminDiretoComprovante(Request $request, FinanceiroAdminDiretoLancamento $lancamento)
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);
        abort_unless($lancamento->comprovante_path, 404);

        return Storage::download($lancamento->comprovante_path);
    }

    public function downloadDespesaNota(Request $request, FinanceiroDespesaLancamento $lancamento)
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);
        abort_unless($lancamento->nota_path, 404);

        return Storage::download($lancamento->nota_path);
    }

    public function downloadLoteParceriaComprovante(Request $request, FinanceiroLoteParceria $lote)
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);
        abort_unless($lote->comprovante_path, 404);

        return Storage::download($lote->comprovante_path);
    }

    public function downloadLoteEngenhariaComprovante(Request $request, FinanceiroLoteEngenharia $lote)
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);
        abort_unless($lote->comprovante_path, 404);

        return Storage::download($lote->comprovante_path);
    }

    public function storeAula(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'qtd_alunos' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'receita' => ['required', 'numeric', 'min:0'],
            'custo_barco' => ['nullable', 'numeric', 'min:0'],
            'custo_combustivel' => ['nullable', 'numeric', 'min:0'],
            'custo_cafe' => ['nullable', 'numeric', 'min:0'],
            'custo_ingresso' => ['nullable', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
        ]);

        $custos = (float) ($validated['custo_barco'] ?? 0)
            + (float) ($validated['custo_combustivel'] ?? 0)
            + (float) ($validated['custo_cafe'] ?? 0)
            + (float) ($validated['custo_ingresso'] ?? 0)
            + (float) ($validated['taxa_marinha'] ?? 0);
        $receita = (float) $validated['receita'];

        FinanceiroAulaLancamento::query()->create([
            'empresa_id' => $empresaId,
            'user_id' => (int) $request->user()->id,
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'qtd_alunos' => (int) ($validated['qtd_alunos'] ?? 0),
            'receita' => $receita,
            'custo_barco' => (float) ($validated['custo_barco'] ?? 0),
            'custo_combustivel' => (float) ($validated['custo_combustivel'] ?? 0),
            'custo_cafe' => (float) ($validated['custo_cafe'] ?? 0),
            'custo_ingresso' => (float) ($validated['custo_ingresso'] ?? 0),
            'taxa_marinha' => (float) ($validated['taxa_marinha'] ?? 0),
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
        ]);

        return back()->with('status', __('Aula lançada.'));
    }

    public function destroyAula(Request $request, FinanceiroAulaLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);
        $lancamento->delete();

        return back()->with('status', __('Aula removida.'));
    }

    public function updateAula(Request $request, FinanceiroAulaLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'qtd_alunos' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'receita' => ['required', 'numeric', 'min:0'],
            'custo_barco' => ['nullable', 'numeric', 'min:0'],
            'custo_combustivel' => ['nullable', 'numeric', 'min:0'],
            'custo_cafe' => ['nullable', 'numeric', 'min:0'],
            'custo_ingresso' => ['nullable', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
        ]);

        $custos = (float) ($validated['custo_barco'] ?? 0)
            + (float) ($validated['custo_combustivel'] ?? 0)
            + (float) ($validated['custo_cafe'] ?? 0)
            + (float) ($validated['custo_ingresso'] ?? 0)
            + (float) ($validated['taxa_marinha'] ?? 0);
        $receita = (float) $validated['receita'];

        $lancamento->update([
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'qtd_alunos' => (int) ($validated['qtd_alunos'] ?? 0),
            'receita' => $receita,
            'custo_barco' => (float) ($validated['custo_barco'] ?? 0),
            'custo_combustivel' => (float) ($validated['custo_combustivel'] ?? 0),
            'custo_cafe' => (float) ($validated['custo_cafe'] ?? 0),
            'custo_ingresso' => (float) ($validated['custo_ingresso'] ?? 0),
            'taxa_marinha' => (float) ($validated['taxa_marinha'] ?? 0),
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
        ]);

        return back()->with('status', __('Aula atualizada.'));
    }

    public function storeAdminDireto(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'data_servico' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['required', 'string', 'in:Pago,Em aberto'],
            'receita' => ['required', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
            'custo_envio' => ['nullable', 'numeric', 'min:0'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $taxa = (float) ($validated['taxa_marinha'] ?? 0);
        $envio = (float) ($validated['custo_envio'] ?? 0);
        $custos = $taxa + $envio;
        $receita = (float) $validated['receita'];

        $path = null;
        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/admin-direto");
        }

        FinanceiroAdminDiretoLancamento::query()->create([
            'empresa_id' => $empresaId,
            'user_id' => (int) $request->user()->id,
            'data_servico' => $validated['data_servico'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'status_pagamento' => $validated['status_pagamento'],
            'receita' => $receita,
            'taxa_marinha' => $taxa,
            'custo_envio' => $envio,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Serviço lançado.'));
    }

    public function destroyAdminDireto(Request $request, FinanceiroAdminDiretoLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);
        if ($lancamento->comprovante_path) {
            Storage::delete($lancamento->comprovante_path);
        }
        $lancamento->delete();

        return back()->with('status', __('Serviço removido.'));
    }

    public function updateAdminDireto(Request $request, FinanceiroAdminDiretoLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'data_servico' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['required', 'string', 'in:Pago,Em aberto'],
            'receita' => ['required', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
            'custo_envio' => ['nullable', 'numeric', 'min:0'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $taxa = (float) ($validated['taxa_marinha'] ?? 0);
        $envio = (float) ($validated['custo_envio'] ?? 0);
        $custos = $taxa + $envio;
        $receita = (float) $validated['receita'];

        $path = $lancamento->comprovante_path;
        if ($request->hasFile('comprovante')) {
            if ($path) {
                Storage::delete($path);
            }
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/admin-direto");
        }

        $lancamento->update([
            'data_servico' => $validated['data_servico'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'status_pagamento' => $validated['status_pagamento'],
            'receita' => $receita,
            'taxa_marinha' => $taxa,
            'custo_envio' => $envio,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Serviço atualizado.'));
    }

    public function storeDespesa(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'fixa' => ['nullable', 'boolean'],
            'nota' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $isFixa = (bool) ($validated['fixa'] ?? false);
        $valor = (float) $validated['valor'];
        $notaPath = null;
        if ($request->hasFile('nota')) {
            $notaPath = $request->file('nota')->storePublicly("financeiro/{$empresaId}/despesas");
        }

        if (! $isFixa) {
            FinanceiroDespesaLancamento::query()->create([
                'empresa_id' => $empresaId,
                'user_id' => (int) $request->user()->id,
                'data_lancamento' => $validated['data_lancamento'],
                'data_pagamento' => $validated['data_pagamento'] ?? null,
                'descricao' => $validated['descricao'],
                'valor' => $valor,
                'fixa_grupo_id' => null,
                'nota_path' => $notaPath,
            ]);

            return back()->with('status', __('Despesa lançada.'));
        }

        $grupo = (string) Str::uuid();
        $base = \Carbon\Carbon::parse($validated['data_lancamento']);
        for ($i = 0; $i < 12; $i++) {
            $d = (clone $base)->addMonthsNoOverflow($i);
            FinanceiroDespesaLancamento::query()->create([
                'empresa_id' => $empresaId,
                'user_id' => (int) $request->user()->id,
                'data_lancamento' => $d->format('Y-m-d'),
                'data_pagamento' => $i === 0 ? ($validated['data_pagamento'] ?? null) : null,
                'descricao' => $validated['descricao'],
                'valor' => $valor,
                'fixa_grupo_id' => $grupo,
                'nota_path' => $notaPath,
            ]);
        }

        return back()->with('status', __('Despesa fixa lançada (12 meses).'));
    }

    public function destroyDespesa(Request $request, FinanceiroDespesaLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);
        if ($lancamento->nota_path) {
            Storage::delete($lancamento->nota_path);
        }
        $lancamento->delete();

        return back()->with('status', __('Despesa removida.'));
    }

    public function updateDespesa(Request $request, FinanceiroDespesaLancamento $lancamento): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lancamento->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'nota' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $notaPath = $lancamento->nota_path;
        if ($request->hasFile('nota')) {
            if ($notaPath) {
                Storage::delete($notaPath);
            }
            $notaPath = $request->file('nota')->storePublicly("financeiro/{$empresaId}/despesas");
        }

        $lancamento->update([
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'descricao' => $validated['descricao'],
            'valor' => (float) $validated['valor'],
            'nota_path' => $notaPath,
        ]);

        return back()->with('status', __('Despesa atualizada.'));
    }

    public function storeLoteParceria(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'mes_referencia' => ['required', 'date_format:Y-m'],
            'empresa_parceira' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['nullable', 'string', 'in:Pago,Em aberto'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $path = null;
        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/parcerias");
        }

        FinanceiroLoteParceria::query()->create([
            'empresa_id' => $empresaId,
            'user_id' => (int) $request->user()->id,
            'mes_referencia' => $validated['mes_referencia'],
            'empresa_parceira' => $validated['empresa_parceira'],
            'status_pagamento' => $validated['status_pagamento'] ?? 'Em aberto',
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Lote de parceria criado.'));
    }

    public function destroyLoteParceria(Request $request, FinanceiroLoteParceria $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);
        if ($lote->comprovante_path) {
            Storage::delete($lote->comprovante_path);
        }
        $lote->delete();

        return back()->with('status', __('Lote de parceria removido.'));
    }

    public function updateLoteParceria(Request $request, FinanceiroLoteParceria $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'mes_referencia' => ['required', 'date_format:Y-m'],
            'empresa_parceira' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['nullable', 'string', 'in:Pago,Em aberto'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $path = $lote->comprovante_path;
        if ($request->hasFile('comprovante')) {
            if ($path) {
                Storage::delete($path);
            }
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/parcerias");
        }

        $lote->update([
            'mes_referencia' => $validated['mes_referencia'],
            'empresa_parceira' => $validated['empresa_parceira'],
            'status_pagamento' => $validated['status_pagamento'] ?? 'Em aberto',
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Lote de parceria atualizado.'));
    }

    public function storeItemLoteParceria(Request $request, FinanceiroLoteParceria $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'receita' => ['required', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
            'custo_envio' => ['nullable', 'numeric', 'min:0'],
        ]);

        $taxa = (float) ($validated['taxa_marinha'] ?? 0);
        $envio = (float) ($validated['custo_envio'] ?? 0);
        $custos = $taxa + $envio;
        $receita = (float) $validated['receita'];

        FinanceiroLoteParceriaItem::query()->create([
            'lote_id' => (int) $lote->id,
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'receita' => $receita,
            'taxa_marinha' => $taxa,
            'custo_envio' => $envio,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
            'nota_emitida' => false,
        ]);

        return back()->with('status', __('Serviço incluído no lote.'));
    }

    public function destroyItemLoteParceria(Request $request, FinanceiroLoteParceriaItem $item): RedirectResponse
    {
        $item->loadMissing('lote');
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) ($item->lote?->empresa_id ?? 0) === $empresaId, 404);
        $item->delete();

        return back()->with('status', __('Serviço do lote removido.'));
    }

    public function updateItemLoteParceria(Request $request, FinanceiroLoteParceriaItem $item): RedirectResponse
    {
        $item->loadMissing('lote');
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) ($item->lote?->empresa_id ?? 0) === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'receita' => ['required', 'numeric', 'min:0'],
            'taxa_marinha' => ['nullable', 'numeric', 'min:0'],
            'custo_envio' => ['nullable', 'numeric', 'min:0'],
        ]);

        $taxa = (float) ($validated['taxa_marinha'] ?? 0);
        $envio = (float) ($validated['custo_envio'] ?? 0);
        $custos = $taxa + $envio;
        $receita = (float) $validated['receita'];

        $item->update([
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'receita' => $receita,
            'taxa_marinha' => $taxa,
            'custo_envio' => $envio,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
        ]);

        return back()->with('status', __('Serviço do lote atualizado.'));
    }

    public function storeLoteEngenharia(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'mes_referencia' => ['required', 'date_format:Y-m'],
            'empresa_parceira' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['nullable', 'string', 'in:Pago,Em aberto'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $path = null;
        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/engenharia");
        }

        FinanceiroLoteEngenharia::query()->create([
            'empresa_id' => $empresaId,
            'user_id' => (int) $request->user()->id,
            'mes_referencia' => $validated['mes_referencia'],
            'empresa_parceira' => $validated['empresa_parceira'],
            'status_pagamento' => $validated['status_pagamento'] ?? 'Em aberto',
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Lote de engenharia criado.'));
    }

    public function destroyLoteEngenharia(Request $request, FinanceiroLoteEngenharia $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);
        if ($lote->comprovante_path) {
            Storage::delete($lote->comprovante_path);
        }
        $lote->delete();

        return back()->with('status', __('Lote de engenharia removido.'));
    }

    public function updateLoteEngenharia(Request $request, FinanceiroLoteEngenharia $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'mes_referencia' => ['required', 'date_format:Y-m'],
            'empresa_parceira' => ['required', 'string', 'max:255'],
            'status_pagamento' => ['nullable', 'string', 'in:Pago,Em aberto'],
            'comprovante' => ['nullable', 'file', 'max:'.(int) config('uploads.max_kb', 3072), 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
        ]);

        $path = $lote->comprovante_path;
        if ($request->hasFile('comprovante')) {
            if ($path) {
                Storage::delete($path);
            }
            $path = $request->file('comprovante')->storePublicly("financeiro/{$empresaId}/engenharia");
        }

        $lote->update([
            'mes_referencia' => $validated['mes_referencia'],
            'empresa_parceira' => $validated['empresa_parceira'],
            'status_pagamento' => $validated['status_pagamento'] ?? 'Em aberto',
            'comprovante_path' => $path,
        ]);

        return back()->with('status', __('Lote de engenharia atualizado.'));
    }

    public function storeItemLoteEngenharia(Request $request, FinanceiroLoteEngenharia $lote): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) $lote->empresa_id === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'receita' => ['required', 'numeric', 'min:0'],
            'custos_extras' => ['nullable', 'numeric', 'min:0'],
        ]);

        $custos = (float) ($validated['custos_extras'] ?? 0);
        $receita = (float) $validated['receita'];

        FinanceiroLoteEngenhariaItem::query()->create([
            'lote_id' => (int) $lote->id,
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'receita' => $receita,
            'custos_extras' => $custos,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
            'nota_emitida' => false,
        ]);

        return back()->with('status', __('Projeto incluído no lote.'));
    }

    public function destroyItemLoteEngenharia(Request $request, FinanceiroLoteEngenhariaItem $item): RedirectResponse
    {
        $item->loadMissing('lote');
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) ($item->lote?->empresa_id ?? 0) === $empresaId, 404);
        $item->delete();

        return back()->with('status', __('Projeto do lote removido.'));
    }

    public function updateItemLoteEngenharia(Request $request, FinanceiroLoteEngenhariaItem $item): RedirectResponse
    {
        $item->loadMissing('lote');
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0 && (int) ($item->lote?->empresa_id ?? 0) === $empresaId, 404);

        $validated = $request->validate([
            'data_lancamento' => ['required', 'date'],
            'data_pagamento' => ['nullable', 'date'],
            'cliente_nome' => ['required', 'string', 'max:255'],
            'servico_tipo' => ['required', 'string', 'max:255'],
            'receita' => ['required', 'numeric', 'min:0'],
            'custos_extras' => ['nullable', 'numeric', 'min:0'],
        ]);

        $custos = (float) ($validated['custos_extras'] ?? 0);
        $receita = (float) $validated['receita'];

        $item->update([
            'data_lancamento' => $validated['data_lancamento'],
            'data_pagamento' => $validated['data_pagamento'] ?? null,
            'cliente_nome' => $validated['cliente_nome'],
            'servico_tipo' => $validated['servico_tipo'],
            'receita' => $receita,
            'custos_extras' => $custos,
            'custo_total' => $custos,
            'lucro' => $receita - $custos,
        ]);

        return back()->with('status', __('Projeto do lote atualizado.'));
    }

    public function emitirNota(Request $request): RedirectResponse
    {
        $empresaId = (int) ($request->user()->empresa_id ?? 0);
        abort_unless($empresaId > 0, 403);

        $validated = $request->validate([
            'record_type' => ['required', 'string', 'in:admin_direto,parceria_item,engenharia_item'],
            'record_id' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['record_type'] === 'admin_direto') {
            $record = FinanceiroAdminDiretoLancamento::query()
                ->where('empresa_id', $empresaId)
                ->findOrFail($validated['record_id']);
            $record->update(['nota_emitida' => true]);
        } elseif ($validated['record_type'] === 'parceria_item') {
            $record = FinanceiroLoteParceriaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->findOrFail($validated['record_id']);
            $record->update(['nota_emitida' => true]);
        } else {
            $record = FinanceiroLoteEngenhariaItem::query()
                ->whereHas('lote', fn ($q) => $q->where('empresa_id', $empresaId))
                ->findOrFail($validated['record_id']);
            $record->update(['nota_emitida' => true]);
        }

        return back()->with('status', __('Nota marcada como emitida.'));
    }
}

