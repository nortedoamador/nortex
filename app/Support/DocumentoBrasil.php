<?php

namespace App\Support;

final class DocumentoBrasil
{
    public static function apenasDigitos(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    public static function formatarCpf(string $digitos): string
    {
        $d = substr(self::apenasDigitos($digitos), 0, 11);
        if (strlen($d) !== 11) {
            return $digitos;
        }

        return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
    }

    public static function formatarCnpj(string $digitos): string
    {
        $d = substr(self::apenasDigitos($digitos), 0, 14);
        if (strlen($d) !== 14) {
            return $digitos;
        }

        return substr($d, 0, 2).'.'.substr($d, 2, 3).'.'.substr($d, 5, 3).'/'.substr($d, 8, 4).'-'.substr($d, 12, 2);
    }

    public static function cpfValido(string $cpf): bool
    {
        $cpf = self::apenasDigitos($cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$t] !== $d) {
                return false;
            }
        }

        return true;
    }

    public static function cnpjValido(string $cnpj): bool
    {
        $cnpj = self::apenasDigitos($cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        for ($i = 0, $n = 0, $s = 5; $i < 12; $i++) {
            $n += (int) $cnpj[$i] * $s--;
            if ($s < 2) {
                $s = 9;
            }
        }
        $d1 = ($n % 11) < 2 ? 0 : 11 - ($n % 11);
        if ((int) $cnpj[12] !== $d1) {
            return false;
        }

        for ($i = 0, $n = 0, $s = 6; $i < 13; $i++) {
            $n += (int) $cnpj[$i] * $s--;
            if ($s < 2) {
                $s = 9;
            }
        }
        $d2 = ($n % 11) < 2 ? 0 : 11 - ($n % 11);

        return (int) $cnpj[13] === $d2;
    }
}
