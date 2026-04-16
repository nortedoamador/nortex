<?php

namespace App\Services;

use App\Enums\ProcessoStatus;
use App\Enums\ProcessoDocumentoStatus;
use App\Models\Embarcacao;
use App\Models\Habilitacao;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use Carbon\Carbon;

final class DashboardAlertasService
{
    /**
     * Contagens para o painel «Alertas» no dashboard (cartões-resumo).
     *
     * @return array{
     *     em_exigencia: int,
     *     habilitacoes_vencendo: int,
     *     habilitacoes_vencidas: int,
     *     tie_vencendo: int,
     *     docs_pendentes: int
     * }
     */
    public function resumo(int $empresaId): array
    {
        $hoje = Carbon::today();
        $limite = $hoje->copy()->addDays(30);

        $nExigencia = Processo::query()
            ->where('empresa_id', $empresaId)
            ->where('status', ProcessoStatus::EmExigencia)
            ->count();

        $nHabVencendo = Habilitacao::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('data_validade')
            ->whereBetween('data_validade', [$hoje, $limite])
            ->count();

        $nHabVencida = Habilitacao::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('data_validade')
            ->where('data_validade', '<', $hoje)
            ->count();

        $nTieVencendo = Embarcacao::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('inscricao_data_vencimento')
            ->whereBetween('inscricao_data_vencimento', [$hoje, $limite])
            ->count();

        $nDocs = ProcessoDocumento::query()
            ->whereHas('processo', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereNotNull('data_validade_documento')
            ->whereBetween('data_validade_documento', [$hoje, $limite])
            ->count();

        $nDocsPendentes = ProcessoDocumento::query()
            ->whereHas('processo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                    ->whereNotIn('status', [ProcessoStatus::Concluido, ProcessoStatus::Indeferido]);
            })
            ->where(function ($q) {
                $q->where('status', ProcessoDocumentoStatus::Pendente)
                    ->orWhere(function ($q) {
                        $q->where('status', ProcessoDocumentoStatus::Enviado)
                            ->whereDoesntHave('anexos')
                            ->where(function ($q) {
                                $q->where('preenchido_via_modelo', false)
                                    ->where('declaracao_residencia_2g', false)
                                    ->where('declaracao_anexo_5h', false)
                                    ->where('declaracao_anexo_5d', false)
                                    ->where('declaracao_anexo_3d', false);
                            });
                    });
            })
            ->distinct('processo_id')
            ->count('processo_id');

        return [
            'em_exigencia' => $nExigencia,
            'habilitacoes_vencendo' => $nHabVencendo,
            'habilitacoes_vencidas' => $nHabVencida,
            'tie_vencendo' => $nTieVencendo,
            'docs_pendentes' => $nDocsPendentes,
        ];
    }

    /**
     * @return list<array{tipo: string, titulo: string, detalhe: string, href?: string, severidade: string}>
     */
    public function coletar(int $empresaId): array
    {
        $alertas = [];

        $hoje = Carbon::today();
        $limite = $hoje->copy()->addDays(30);

        $chaVencendo = Habilitacao::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('data_validade')
            ->whereBetween('data_validade', [$hoje, $limite])
            ->with('cliente:id,nome')
            ->orderBy('data_validade')
            ->limit(8)
            ->get();

        foreach ($chaVencendo as $h) {
            $alertas[] = [
                'tipo' => 'cha_validade',
                'titulo' => __('CHA a vencer'),
                'detalhe' => ($h->cliente?->nome ?? __('Cliente')).' — '.__('validade :d', ['d' => $h->data_validade?->format('d/m/Y') ?? '—']),
                'href' => $h->cliente ? route('clientes.show', $h->cliente) : null,
                'severidade' => 'amber',
            ];
        }

        $chaVencida = Habilitacao::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('data_validade')
            ->where('data_validade', '<', $hoje)
            ->with('cliente:id,nome')
            ->orderByDesc('data_validade')
            ->limit(5)
            ->get();

        foreach ($chaVencida as $h) {
            $alertas[] = [
                'tipo' => 'cha_vencida',
                'titulo' => __('CHA vencida'),
                'detalhe' => ($h->cliente?->nome ?? __('Cliente')).' — '.__('desde :d', ['d' => $h->data_validade?->format('d/m/Y') ?? '—']),
                'href' => $h->cliente ? route('clientes.show', $h->cliente) : null,
                'severidade' => 'red',
            ];
        }

        $docsChecklist = ProcessoDocumento::query()
            ->whereHas('processo', fn ($q) => $q->where('empresa_id', $empresaId))
            ->whereNotNull('data_validade_documento')
            ->whereBetween('data_validade_documento', [$hoje, $limite])
            ->with(['processo:id,cliente_id', 'documentoTipo:id,nome'])
            ->orderBy('data_validade_documento')
            ->limit(8)
            ->get();

        foreach ($docsChecklist as $d) {
            $p = $d->processo;
            $alertas[] = [
                'tipo' => 'doc_checklist',
                'titulo' => __('Validade de documento no processo'),
                'detalhe' => ($d->documentoTipo?->nome ?? __('Documento')).' — '.__('processo #:id', ['id' => $p?->id ?? '—']).' — '.__('até :d', ['d' => $d->data_validade_documento?->format('d/m/Y') ?? '—']),
                'href' => $p ? route('processos.show', $p) : null,
                'severidade' => 'amber',
            ];
        }

        $parados = Processo::query()
            ->where('empresa_id', $empresaId)
            ->where('status', ProcessoStatus::EmMontagem)
            ->where('updated_at', '<', now()->subDays(14))
            ->orderBy('updated_at')
            ->limit(6)
            ->get(['id', 'updated_at', 'cliente_id']);

        foreach ($parados as $p) {
            $alertas[] = [
                'tipo' => 'processo_parado',
                'titulo' => __('Processo parado em montagem'),
                'detalhe' => __('Processo #:id sem atualização há mais de 14 dias.', ['id' => $p->id]),
                'href' => route('processos.show', $p),
                'severidade' => 'slate',
            ];
        }

        return $alertas;
    }
}
