<?php

namespace App\Services;

use App\Enums\ProcessoStatus;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardProvasMarinhaService
{
    /**
     * Processos em «Aguardando prova» com data da prova (ou sem data), para o cartão do dashboard.
     *
     * @return list<array{
     *     titulo: string,
     *     meta: string,
     *     href: string,
     *     atrasado: bool,
     *     sem_data: bool
     * }>
     */
    public function itens(User $user, int $limite = 15): array
    {
        if (! $user->hasPermission('processos.view')) {
            return [];
        }

        $colecao = Processo::query()
            ->where('status', ProcessoStatus::AguardandoProva)
            ->with(['cliente:id,nome', 'tipoProcesso:id,nome'])
            ->limit(80)
            ->get();

        $hoje = now()->startOfDay();

        $ordenado = $colecao->sort(function (Processo $a, Processo $b) use ($hoje): int {
            $da = $a->marinha_prova_data;
            $db = $b->marinha_prova_data;

            if ($da === null && $db === null) {
                return $a->id <=> $b->id;
            }
            if ($da === null) {
                return 1;
            }
            if ($db === null) {
                return -1;
            }

            $aDay = $da instanceof Carbon ? $da->copy()->startOfDay() : Carbon::parse($da)->startOfDay();
            $bDay = $db instanceof Carbon ? $db->copy()->startOfDay() : Carbon::parse($db)->startOfDay();

            $aFuturo = $aDay->greaterThanOrEqualTo($hoje);
            $bFuturo = $bDay->greaterThanOrEqualTo($hoje);

            if ($aFuturo !== $bFuturo) {
                return $aFuturo ? -1 : 1;
            }

            if ($aFuturo) {
                return $aDay <=> $bDay;
            }

            return $bDay <=> $aDay;
        })->values();

        $out = [];
        foreach ($ordenado->take($limite) as $processo) {
            $out[] = $this->mapear($processo, $hoje);
        }

        return $out;
    }

    /**
     * @return array{
     *     titulo: string,
     *     meta: string,
     *     href: string,
     *     atrasado: bool,
     *     sem_data: bool
     * }
     */
    private function mapear(Processo $processo, Carbon $hoje): array
    {
        $cliente = $processo->cliente?->nome;
        $titulo = filled($cliente) ? (string) $cliente : __('Cliente não definido');
        $tipo = $processo->tipoProcesso?->nome ?? __('Processo');

        $d = $processo->marinha_prova_data;
        $semData = $d === null;
        $atrasado = false;
        $metaLinha = __('Data por definir na ficha');

        if (! $semData) {
            $dia = $d instanceof Carbon ? $d->copy()->startOfDay() : Carbon::parse($d)->startOfDay();
            $metaLinha = $dia->translatedFormat('d M Y').' · '.$tipo;
            $atrasado = $dia->lessThan($hoje);
        } else {
            $metaLinha = $tipo.' · '.__('Data por definir na ficha');
        }

        $href = ($processo->cliente !== null)
            ? route('clientes.show', $processo->cliente)
            : route('processos.show', $processo);

        return [
            'titulo' => $titulo,
            'meta' => $metaLinha,
            'href' => $href,
            'atrasado' => $atrasado,
            'sem_data' => $semData,
        ];
    }
}
