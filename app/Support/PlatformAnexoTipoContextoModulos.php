<?php

namespace App\Support;

/**
 * Módulos onde um tipo de anexo global pode ser referenciado (uploads com platform_anexo_tipo_id).
 */
final class PlatformAnexoTipoContextoModulos
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::labels());
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'clientes' => __('Clientes — anexos na ficha de cliente'),
            'embarcacoes' => __('Embarcações — anexos da embarcação'),
            'habilitacoes' => __('Habilitações — anexos da habilitação'),
        ];
    }

    /**
     * @param  array{cliente?: int, embarcacao?: int, habilitacao?: int}  $counts
     * @return list<string>
     */
    public static function keysFromUsoCounts(array $counts): array
    {
        $out = [];
        if (($counts['cliente'] ?? 0) > 0) {
            $out[] = 'clientes';
        }
        if (($counts['embarcacao'] ?? 0) > 0) {
            $out[] = 'embarcacoes';
        }
        if (($counts['habilitacao'] ?? 0) > 0) {
            $out[] = 'habilitacoes';
        }

        return $out;
    }

    /**
     * @param  list<string>|null  $keys
     */
    public static function resumo(?array $keys): string
    {
        if ($keys === null || $keys === []) {
            return '—';
        }

        $labels = self::labels();
        $parts = [];
        foreach ($keys as $k) {
            if (isset($labels[$k])) {
                $parts[] = $labels[$k];
            }
        }

        return $parts === [] ? '—' : implode(' · ', $parts);
    }
}
