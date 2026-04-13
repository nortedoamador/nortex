<?php

namespace App\Services\Cnh;

use Illuminate\Support\Facades\Log;

/**
 * Leitura de QR Code via executável zbarimg (ZBar).
 */
final class CnhQrZbarReader
{
    public function decode(string $absoluteImagePath): ?string
    {
        $run = CnhShellRunner::run(
            operation: 'zbar',
            configKey: 'cnh.zbarimg_path',
            envKey: 'CNH_ZBARIMG_PATH',
            absoluteImagePath: $absoluteImagePath,
            argsBeforeImage: '--quiet --raw',
            argsAfterImage: '',
            timeoutSeconds: 60,
        );

        if ($run->diagnostic !== null) {
            return null;
        }

        $out = $run->outputTrimmed();
        if ($out === '') {
            if ($run->exitCode !== 0) {
                Log::info('cnh.cli.zbar.no_payload', [
                    'exit_code' => $run->exitCode,
                    'command' => $run->command,
                    'duration_ms' => $run->durationMs,
                ]);
            }

            return null;
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $out) ?: [])));
        if ($lines === []) {
            return null;
        }

        foreach ($lines as $line) {
            if (str_starts_with($line, '{')) {
                return $line;
            }
        }

        if (preg_match('/\{[\s\S]*\}/', $out, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j) && $j !== []) {
                return $m[0];
            }
        }

        return null;
    }
}
