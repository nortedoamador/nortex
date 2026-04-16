<?php

namespace App\Services;

use App\Models\AulaNautica;
use App\Models\EmpresaCompromisso;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardAgendaService
{
    /**
     * Compromissos manuais (reunião, Marinha) e aulas náuticas futuras, ordenados por data.
     *
     * @return list<array{
     *     kind: string,
     *     badge: string,
     *     badge_tone: string,
     *     titulo: string,
     *     meta: string,
     *     href: ?string,
     *     sort_at: string
     * }>
     */
    public function proximosItens(User $user, int $limite = 12): array
    {
        $empresaId = (int) ($user->empresa_id ?? 0);
        if ($empresaId <= 0) {
            return [];
        }

        $inicio = now()->startOfDay();

        $itens = collect();

        EmpresaCompromisso::query()
            ->where('empresa_id', $empresaId)
            ->whereDate('data', '>=', $inicio)
            ->orderBy('data')
            ->orderBy('hora_inicio')
            ->limit(50)
            ->get()
            ->each(function (EmpresaCompromisso $c) use ($itens): void {
                $itens->push($this->mapearCompromisso($c));
            });

        if ($user->hasPermission('aulas.view')) {
            AulaNautica::query()
                ->where('empresa_id', $empresaId)
                ->whereDate('data_aula', '>=', $inicio)
                ->whereNotIn('status', ['rascunho', 'cancelada'])
                ->orderBy('data_aula')
                ->orderBy('hora_inicio')
                ->limit(50)
                ->get()
                ->each(function (AulaNautica $a) use ($itens): void {
                    $itens->push($this->mapearAula($a));
                });
        }

        return $itens
            ->sortBy('sort_at')
            ->take($limite)
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     kind: string,
     *     badge: string,
     *     badge_tone: string,
     *     titulo: string,
     *     meta: string,
     *     href: ?string,
     *     sort_at: string
     * }
     */
    private function mapearCompromisso(EmpresaCompromisso $c): array
    {
        $data = $c->data instanceof Carbon ? $c->data : Carbon::parse($c->data);
        [$badge, $tone] = match ($c->tipo) {
            'marinha_atendimento' => [__('Atendimento na Marinha'), 'violet'],
            default => [__('Reunião'), 'indigo'],
        };

        $horaIni = $this->formatarHora($c->hora_inicio);
        $horaFim = $this->formatarHora($c->hora_fim);
        $horaParte = match (true) {
            $horaIni !== null && $horaFim !== null => $horaIni.'–'.$horaFim,
            $horaIni !== null => $horaIni,
            default => null,
        };

        $partes = array_filter([
            $data->translatedFormat('d M Y'),
            $horaParte,
            filled($c->local) ? $c->local : null,
        ]);

        return [
            'kind' => 'compromisso',
            'badge' => $badge,
            'badge_tone' => $tone,
            'titulo' => $c->titulo,
            'meta' => implode(' · ', $partes),
            'href' => null,
            'sort_at' => $this->chaveOrdenacao($data, $c->hora_inicio),
        ];
    }

    /**
     * @return array{
     *     kind: string,
     *     badge: string,
     *     badge_tone: string,
     *     titulo: string,
     *     meta: string,
     *     href: ?string,
     *     sort_at: string
     * }
     */
    private function mapearAula(AulaNautica $a): array
    {
        $data = $a->data_aula instanceof Carbon ? $a->data_aula : Carbon::parse($a->data_aula);
        $tipoLabel = match ($a->tipo_aula) {
            'pratica' => __('Aula prática'),
            'teorica_pratica' => __('Aula teórica e prática'),
            default => __('Aula teórica'),
        };

        $horaIni = $this->formatarHora($a->hora_inicio);
        $horaFim = $this->formatarHora($a->hora_fim);
        $horaParte = match (true) {
            $horaIni !== null && $horaFim !== null => $horaIni.'–'.$horaFim,
            $horaIni !== null => $horaIni,
            default => null,
        };

        $partes = array_filter([
            $data->translatedFormat('d M Y'),
            $horaParte,
            filled($a->local) ? $a->local : null,
            __('Ofício :n', ['n' => $a->numero_oficio]),
        ]);

        return [
            'kind' => 'aula',
            'badge' => __('Aula'),
            'badge_tone' => 'emerald',
            'titulo' => $tipoLabel,
            'meta' => implode(' · ', $partes),
            'href' => route('aulas.show', $a),
            'sort_at' => $this->chaveOrdenacao($data, $a->hora_inicio),
        ];
    }

    private function chaveOrdenacao(Carbon $data, mixed $hora): string
    {
        $base = $data->format('Y-m-d');
        $h = $this->formatarHora($hora);

        return $h !== null ? $base.' '.$h : $base.' 00:00';
    }

    private function formatarHora(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $s = is_string($valor) ? trim($valor) : (string) $valor;
        if ($s === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $s, $m) === 1) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT).':'.$m[2];
        }

        return $s;
    }
}
