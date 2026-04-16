<?php

namespace App\Support;

/**
 * Em cada aula, define em que atestado (ARA / MTA) os dados do instrutor entram.
 *
 * @phpstan-type ProgramaValue 'arrais'|'motonauta'|'ambos'
 */
final class AulaEscolaInstrutorProgramaAtestado
{
    public const ARRAIS = 'arrais';

    public const MOTONAUTA = 'motonauta';

    public const AMBOS = 'ambos';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::ARRAIS, self::MOTONAUTA, self::AMBOS];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ARRAIS => __('Arrais-Amador (ARA)'),
            self::MOTONAUTA => __('Motonauta (MTA)'),
            self::AMBOS => __('Ambos (ARA e MTA)'),
        ];
    }

    /** Instrutor aparece no atestado ARA (Anexo 5-E). */
    public static function apareceNoAra(?string $pivot): bool
    {
        $p = $pivot ?? self::AMBOS;

        return in_array($p, [self::ARRAIS, self::AMBOS], true);
    }

    /** Instrutor aparece no atestado MTA. */
    public static function apareceNoMta(?string $pivot): bool
    {
        $p = $pivot ?? self::AMBOS;

        return in_array($p, [self::MOTONAUTA, self::AMBOS], true);
    }

}
