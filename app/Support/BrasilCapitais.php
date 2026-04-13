<?php

namespace App\Support;

final class BrasilCapitais
{
    /**
     * Capital (nome do município) por sigla da UF.
     *
     * @return array<string, string> UF => nome do município
     */
    public static function porUf(): array
    {
        return [
            'AC' => 'Rio Branco',
            'AL' => 'Maceió',
            'AP' => 'Macapá',
            'AM' => 'Manaus',
            'BA' => 'Salvador',
            'CE' => 'Fortaleza',
            'DF' => 'Brasília',
            'ES' => 'Vitória',
            'GO' => 'Goiânia',
            'MA' => 'São Luís',
            'MT' => 'Cuiabá',
            'MS' => 'Campo Grande',
            'MG' => 'Belo Horizonte',
            'PA' => 'Belém',
            'PB' => 'João Pessoa',
            'PR' => 'Curitiba',
            'PE' => 'Recife',
            'PI' => 'Teresina',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Natal',
            'RS' => 'Porto Alegre',
            'RO' => 'Porto Velho',
            'RR' => 'Boa Vista',
            'SC' => 'Florianópolis',
            'SP' => 'São Paulo',
            'SE' => 'Aracaju',
            'TO' => 'Palmas',
        ];
    }

    public static function capitalMunicipio(?string $uf): ?string
    {
        if ($uf === null || $uf === '') {
            return null;
        }

        $uf = strtoupper($uf);

        return self::porUf()[$uf] ?? null;
    }
}
