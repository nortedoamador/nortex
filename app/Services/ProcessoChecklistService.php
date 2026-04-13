<?php

namespace App\Services;

use App\Enums\ProcessoDocumentoStatus;
use App\Models\PlatformTipoProcesso;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\TipoProcesso;
use App\Support\ProcessoChecklistEstadoInicial;

/**
 * Gera linhas do checklist a partir das regras do tipo de processo.
 */
class ProcessoChecklistService
{
    public function gerarParaProcesso(Processo $processo): void
    {
        $processo->loadMissing(['tipoProcesso', 'tipoProcessoTenant']);

        $tipo = $processo->tipoProcesso;
        if ($tipo) {
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $processo->empresa_id)->orderBy('documento_processo.ordem'),
            ]);
        } else {
            $tipo = $processo->tipoProcessoTenant;
            if (! $tipo) {
                return;
            }
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
            ]);
        }

        foreach ($tipo->documentoRegras as $docTipo) {
            $inicial = ProcessoChecklistEstadoInicial::resolver($docTipo);
            $statusInicial = $inicial['status'];
            $preenchidoModelo = $inicial['preenchido_via_modelo'];

            $create = ['status' => $statusInicial];
            if ($preenchidoModelo) {
                $create['preenchido_via_modelo'] = true;
            }

            ProcessoDocumento::withoutGlobalScopes()->firstOrCreate(
                [
                    'processo_id' => $processo->id,
                    'documento_tipo_id' => $docTipo->id,
                ],
                $create,
            );
        }
    }

    /**
     * Cria linhas em falta conforme regras atuais e remove linhas órfãs só quando ainda estão
     * «Pendente» e sem anexos (evita apagar histórico).
     */
    public function sincronizarProcessoComRegrasAtuais(Processo $processo): void
    {
        $this->gerarParaProcesso($processo);

        $processo->loadMissing(['tipoProcesso', 'tipoProcessoTenant']);

        $tipo = $processo->tipoProcesso ?? $processo->tipoProcessoTenant;
        if ($tipo === null) {
            return;
        }

        if ($processo->tipoProcesso) {
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->wherePivot('empresa_id', (int) $processo->empresa_id)->orderBy('documento_processo.ordem'),
            ]);
        } else {
            $tipo->loadMissing([
                'documentoRegras' => fn ($q) => $q->orderBy('documento_processo.ordem'),
            ]);
        }

        $idsRegra = $tipo->documentoRegras->pluck('id')->map(fn ($id) => (int) $id)->all();

        $processo->unsetRelation('documentosChecklist');
        $processo->load([
            'documentosChecklist' => fn ($q) => $q->withCount('anexos'),
        ]);

        foreach ($processo->documentosChecklist as $linha) {
            if (in_array((int) $linha->documento_tipo_id, $idsRegra, true)) {
                continue;
            }
            if ((int) ($linha->anexos_count ?? 0) !== 0) {
                continue;
            }
            if ($linha->status !== ProcessoDocumentoStatus::Pendente) {
                continue;
            }

            ProcessoDocumento::withoutGlobalScopes()->whereKey($linha->id)->delete();
        }
    }

    /**
     * Após alterar o checklist no cadastro do tipo (tenant), aplica as regras a todos os processos dessa empresa
     * vinculados ao mesmo slug de tipo de plataforma ou ao {@see TipoProcesso} tenant.
     *
     * @return int quantidade de processos percorridos
     */
    public function sincronizarChecklistsAposAlterarRegrasTipo(TipoProcesso $tipoTenant): int
    {
        $empresaId = (int) $tipoTenant->empresa_id;
        $platformId = PlatformTipoProcesso::query()
            ->where('slug', $tipoTenant->slug)
            ->value('id');

        $q = Processo::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($qq) use ($platformId, $tipoTenant) {
                $qq->where('tipo_processo_id', $tipoTenant->id);
                if ($platformId) {
                    $qq->orWhere('platform_tipo_processo_id', (int) $platformId);
                }
            });

        $n = 0;
        $q->orderBy('id')->chunkById(100, function ($processos) use (&$n) {
            foreach ($processos as $processo) {
                $this->sincronizarProcessoComRegrasAtuais($processo);
                $n++;
            }
        });

        return $n;
    }
}
