<?php

namespace App\Support;

/**
 * UF sugerida pelo nono dígito do CPF (regra histórica da Receita Federal).
 * Quando o dígito corresponde a vários estados, retorna um representante da região.
 */
final class CpfUfEmitente
{
    /**
     * @return non-empty-string|null Sigla UF (2 letras) ou null se inválido / exterior (9).
     */
    public static function ufSugerida(?string $cpf): ?string
    {
        $d = DocumentoBrasil::apenasDigitos((string) $cpf);
        if (strlen($d) !== 11 || ! DocumentoBrasil::cpfValido($d)) {
            return null;
        }

        $n = (int) $d[8];

        return match ($n) {
            0 => 'RS',
            1 => 'DF',
            2 => 'AM',
            3 => 'CE',
            4 => 'PE',
            5 => 'BA',
            6 => 'MG',
            7 => 'RJ',
            8 => 'SP',
            9 => null,
            default => null,
        };
    }
}
