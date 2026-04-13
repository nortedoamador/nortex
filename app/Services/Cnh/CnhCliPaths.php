<?php

namespace App\Services\Cnh;

/**
 * Caminhos para CLI no Windows: barras normais evitam escapes frágeis no .env e nos comandos.
 */
final class CnhCliPaths
{
    public static function normalize(string $path): string
    {
        $path = trim($path);

        return str_replace('\\', '/', $path);
    }

    public static function resolveBinaryPath(string $fromConfig, string $envKey): string
    {
        $raw = trim($fromConfig);
        if ($raw === '') {
            $raw = trim((string) env($envKey, ''));
        }

        return self::normalize($raw);
    }
}
