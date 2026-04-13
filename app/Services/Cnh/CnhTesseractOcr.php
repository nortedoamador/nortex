<?php

namespace App\Services\Cnh;

use Illuminate\Support\Facades\Log;

/**
 * OCR opcional via executável Tesseract no servidor.
 */
final class CnhTesseractOcr
{
    public function extractText(string $absoluteImagePath): ?string
    {
        $run = CnhShellRunner::run(
            operation: 'tesseract',
            configKey: 'cnh.tesseract_path',
            envKey: 'CNH_TESSERACT_PATH',
            absoluteImagePath: $absoluteImagePath,
            argsBeforeImage: '',
            argsAfterImage: 'stdout -l por',
            timeoutSeconds: 120,
        );

        if ($run->diagnostic !== null) {
            return null;
        }

        $text = $run->outputTrimmed();
        if ($run->exitCode !== 0) {
            Log::warning('cnh.cli.tesseract.non_zero_exit', [
                'exit_code' => $run->exitCode,
                'command' => $run->command,
                'duration_ms' => $run->durationMs,
                'output_preview' => mb_substr(preg_replace('/\s+/', ' ', $text) ?? $text, 0, 200),
            ]);
        }

        if ($text === '') {
            return null;
        }

        return $text;
    }
}
