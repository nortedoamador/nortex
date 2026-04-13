<?php

namespace App\Support;

use App\Services\CirProcessosTemplateService;
use App\Services\EmbarcacaoProcessosTemplateService;
use App\Services\HabilitacaoChaProcessosTemplateService;
use App\Services\TieProcessosTemplateService;

/**
 * Mapa estável de slugs de tipo de processo → ficheiro template PHP e ordem sugerida de revisão humana.
 *
 * @see \App\Services\EmpresaProcessosDefaultsService::garantirTemplateBasico
 */
final class ChecklistTemplateSourcesRegistry
{
    /** Onda 1: CHA (poucos serviços). */
    public const WAVE_CHA = 1;

    /** Onda 2: CIR. */
    public const WAVE_CIR = 2;

    /** Onda 3: Embarcação (agrupamento distinto do TIE no código). */
    public const WAVE_EMBARCACAO = 3;

    /** Onda 4: TIE (maior volume). */
    public const WAVE_TIE = 4;

    /**
     * Ordem de revisão recomendada (valor menor = primeiro).
     *
     * @var list<array{wave: int, key: string, service_class: class-string, slugs: list<string>}>
     */
    public const REVIEW_GROUPS = [
        [
            'wave' => self::WAVE_CHA,
            'key' => 'cha',
            'service_class' => HabilitacaoChaProcessosTemplateService::class,
            'slugs' => [
                'cha-extravio-roubo-furto-dano',
                'cha-inscricao-arrais-amador',
                'cha-inscricao-arrais-amador-mestre-amador',
                'cha-renovacao',
                'cha-equivalencia-profissional',
                'cha-agregacao-motonauta',
            ],
        ],
        [
            'wave' => self::WAVE_CIR,
            'key' => 'cir',
            'service_class' => CirProcessosTemplateService::class,
            'slugs' => [
                'cir-2via-extravio-brasileiro',
                'cir-revalidacao-termino-espaco-brasileiro',
                'cir-2via-extravio-estrangeiro',
                'cir-revalidacao-termino-espaco-estrangeiro',
            ],
        ],
        [
            'wave' => self::WAVE_EMBARCACAO,
            'key' => 'embarcacao',
            'service_class' => EmbarcacaoProcessosTemplateService::class,
            'slugs' => [
                'inscricao-embarcacao',
                'renovacao-tie-tiem',
                'segunda-via-tie-tiem',
                'transferencia-proprietario',
                'transferencia-jurisdicao-embarcacao',
            ],
        ],
        [
            'wave' => self::WAVE_TIE,
            'key' => 'tie',
            'service_class' => TieProcessosTemplateService::class,
            'slugs' => [
                'tie-inscricao-embarcacao-ate-12m',
                'tie-inscricao-moto-aquatica',
                'tie-renovacao-moto-aquatica',
                'tie-inscricao-mar-aberto-ab100',
                'tie-inscricao-navegacao-interior-ab100',
                'tie-renovacao-tie',
                'tie-alteracao-dados-embarcacao',
                'tie-cancelamento-embarcacao',
                'tie-registro-onus-averbacoes',
                'tie-transferencia-jurisdicao',
                'tie-transferencia-propriedade-esporte-recreio',
                'tie-transferencia-propriedade-mar-aberto',
                'tie-transferencia-propriedade-navegacao-interior',
                'tie-cancelamento-embarcacao-tm',
                'tie-cancelamento-onus-tm',
                'tie-tm-registro-grande-porte-roteiro-i',
                'tie-tm-registro-navegacao-interior',
                'tie-tm-registro-mar-aberto',
                'tie-tm-registro-onus-averbacoes',
                'tie-tm-registro-grande-porte-roteiro-ii',
                'tie-tm-registro-interior-ampliado',
                'tie-tm-registro-mar-aberto-ampliado',
                'tie-tm-transferencia-jurisdicao',
                'tie-tm-transferencia-propriedade-er',
                'tie-tm-transferencia-propriedade-mar-aberto',
                'tie-tm-transferencia-propriedade-interior',
            ],
        ],
    ];

    /**
     * @return array{wave: int, key: string, service_class: class-string}|null
     */
    public static function metaPorSlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        foreach (self::REVIEW_GROUPS as $group) {
            if (in_array($slug, $group['slugs'], true)) {
                return [
                    'wave' => $group['wave'],
                    'key' => $group['key'],
                    'service_class' => $group['service_class'],
                ];
            }
        }

        return null;
    }

    /**
     * Slugs conhecidos no registo (útil para detetar tipos na BD sem template PHP listado).
     *
     * @return list<string>
     */
    public static function todosSlugsRegistados(): array
    {
        $out = [];
        foreach (self::REVIEW_GROUPS as $group) {
            foreach ($group['slugs'] as $s) {
                $out[] = $s;
            }
        }

        return $out;
    }
}
