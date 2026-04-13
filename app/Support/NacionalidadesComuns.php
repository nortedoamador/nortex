<?php

namespace App\Support;

final class NacionalidadesComuns
{
    /**
     * @return array<string, string> valor => rótulo (iguais para facilitar exibição)
     */
    public static function options(): array
    {
        $n = [
            'Brasileira',
            'Argentina',
            'Boliviana',
            'Chilena',
            'Colombiana',
            'Francesa',
            'Italiana',
            'Japonesa',
            'Norte-americana',
            'Paraguaia',
            'Peruana',
            'Portuguesa',
            'Uruguaia',
            'Venezuelana',
            'Alemã',
            'Espanhola',
            'Chinesa',
            'Coreana',
            'Indiana',
            'Russa',
            'Britânica',
            'Canadense',
            'Mexicana',
            'Outra',
        ];

        return array_combine($n, $n);
    }

    /**
     * @return list<string>
     */
    public static function valoresPermitidos(): array
    {
        return array_keys(self::options());
    }
}
