<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProcessoStatus;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Processo;
use App\Support\TenantEmpresaContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioController extends Controller
{
    public function index(): View
    {
        return view('admin.relatorios.index');
    }

    public function processosPorStatus(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);

        $totais = Processo::query()
            ->where('empresa_id', $empresaId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($n) => (int) $n);

        $linhas = collect(ProcessoStatus::cases())->map(function (ProcessoStatus $s) use ($totais) {
            return [
                'status' => $s,
                'label' => $s->label(),
                'total' => (int) ($totais[$s->value] ?? 0),
            ];
        });

        return view('admin.relatorios.processos-status', compact('linhas'));
    }

    public function processosPorPeriodo(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);
        $parse = function ($raw, string $fallbackIso): string {
            $v = is_string($raw) ? trim($raw) : '';
            if ($v === '') {
                return $fallbackIso;
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                } catch (\Throwable) {
                    return $fallbackIso;
                }
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return $v;
            }

            return $fallbackIso;
        };

        $inicioIso = $parse($request->query('inicio'), now()->subMonths(3)->format('Y-m-d'));
        $fimIso = $parse($request->query('fim'), now()->format('Y-m-d'));
        $inicio = \Illuminate\Support\Carbon::parse($inicioIso)->format('d/m/Y');
        $fim = \Illuminate\Support\Carbon::parse($fimIso)->format('d/m/Y');

        $processos = Processo::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicioIso.' 00:00:00', $fimIso.' 23:59:59'])
            ->with(['cliente:id,nome', 'tipoProcesso:id,nome'])
            ->orderByDesc('created_at')
            ->paginate(40)
            ->withQueryString();

        return view('admin.relatorios.processos-periodo', compact('processos', 'inicio', 'fim'));
    }

    public function clientesPorPeriodo(Request $request): View
    {
        $empresaId = TenantEmpresaContext::empresaId($request);
        $parse = function ($raw, string $fallbackIso): string {
            $v = is_string($raw) ? trim($raw) : '';
            if ($v === '') {
                return $fallbackIso;
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                } catch (\Throwable) {
                    return $fallbackIso;
                }
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return $v;
            }

            return $fallbackIso;
        };

        $inicioIso = $parse($request->query('inicio'), now()->subMonths(3)->format('Y-m-d'));
        $fimIso = $parse($request->query('fim'), now()->format('Y-m-d'));
        $inicio = \Illuminate\Support\Carbon::parse($inicioIso)->format('d/m/Y');
        $fim = \Illuminate\Support\Carbon::parse($fimIso)->format('d/m/Y');

        $clientes = Cliente::query()
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicioIso.' 00:00:00', $fimIso.' 23:59:59'])
            ->orderByDesc('created_at')
            ->paginate(40)
            ->withQueryString();

        return view('admin.relatorios.clientes-periodo', compact('clientes', 'inicio', 'fim'));
    }

    public function exportProcessosCsv(Request $request): StreamedResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);
        $parse = function ($raw, string $fallbackIso): string {
            $v = is_string($raw) ? trim($raw) : '';
            if ($v === '') {
                return $fallbackIso;
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                } catch (\Throwable) {
                    return $fallbackIso;
                }
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return $v;
            }

            return $fallbackIso;
        };

        $inicio = $parse($request->query('inicio'), now()->subMonths(3)->format('Y-m-d'));
        $fim = $parse($request->query('fim'), now()->format('Y-m-d'));

        $filename = 'processos-'.$inicio.'-'.$fim.'.csv';

        return response()->streamDownload(function () use ($empresaId, $inicio, $fim) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, '%c%c%c', 0xEF, 0xBB, 0xBF);
            fputcsv($handle, ['id', 'criado_em', 'status', 'cliente', 'tipo_processo']);

            Processo::query()
                ->where('empresa_id', $empresaId)
                ->whereBetween('created_at', [$inicio.' 00:00:00', $fim.' 23:59:59'])
                ->with(['cliente:id,nome', 'tipoProcesso:id,nome'])
                ->orderBy('id')
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $p) {
                        fputcsv($handle, [
                            $p->id,
                            $p->created_at?->format('Y-m-d H:i:s'),
                            $p->status->value,
                            $p->cliente?->nome ?? '',
                            $p->tipoProcesso?->nome ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportClientesCsv(Request $request): StreamedResponse
    {
        $empresaId = TenantEmpresaContext::empresaId($request);
        $parse = function ($raw, string $fallbackIso): string {
            $v = is_string($raw) ? trim($raw) : '';
            if ($v === '') {
                return $fallbackIso;
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $v)->format('Y-m-d');
                } catch (\Throwable) {
                    return $fallbackIso;
                }
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return $v;
            }

            return $fallbackIso;
        };

        $inicio = $parse($request->query('inicio'), now()->subMonths(3)->format('Y-m-d'));
        $fim = $parse($request->query('fim'), now()->format('Y-m-d'));

        $filename = 'clientes-'.$inicio.'-'.$fim.'.csv';

        return response()->streamDownload(function () use ($empresaId, $inicio, $fim) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, '%c%c%c', 0xEF, 0xBB, 0xBF);
            fputcsv($handle, ['id', 'criado_em', 'nome', 'cpf', 'email']);

            Cliente::query()
                ->where('empresa_id', $empresaId)
                ->whereBetween('created_at', [$inicio.' 00:00:00', $fim.' 23:59:59'])
                ->orderBy('id')
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $c) {
                        fputcsv($handle, [
                            $c->id,
                            $c->created_at?->format('Y-m-d H:i:s'),
                            $c->nome,
                            $c->cpf ?? '',
                            $c->email ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
