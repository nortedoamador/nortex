<?php

namespace App\Services\Cnh;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Executa zbarimg / tesseract com aspas nos caminhos, 2>&1 e métricas para Windows (cmd).
 */
final class CnhShellRunner
{
    public static function run(
        string $operation,
        string $configKey,
        string $envKey,
        string $absoluteImagePath,
        string $argsBeforeImage,
        string $argsAfterImage,
        int $timeoutSeconds = 120,
    ): CnhCliRunResult {
        $binary = CnhCliPaths::resolveBinaryPath((string) config($configKey, ''), $envKey);
        $image = CnhCliPaths::normalize($absoluteImagePath);

        if ($binary === '') {
            $diag = "Binário não configurado. Defina {$envKey} no .env (ex.: C:/caminho/zbarimg.exe).";
            Log::error("cnh.cli.{$operation}.binary_missing", [
                'config_key' => $configKey,
                'env_key' => $envKey,
                'diagnostic' => $diag,
            ]);

            return new CnhCliRunResult(
                command: '(não executado)',
                durationMs: 0,
                exitCode: -1,
                combinedOutput: '',
                binaryConfigured: false,
                binaryExists: false,
                diagnostic: $diag,
            );
        }

        if (! is_file($binary)) {
            $diag = "Executável não encontrado no disco: {$binary}";
            Log::error("cnh.cli.{$operation}.binary_not_found", [
                'binary_path' => $binary,
                'diagnostic' => $diag,
            ]);

            return new CnhCliRunResult(
                command: '(não executado)',
                durationMs: 0,
                exitCode: -1,
                combinedOutput: '',
                binaryConfigured: true,
                binaryExists: false,
                diagnostic: $diag,
            );
        }

        if (! is_readable($image)) {
            $diag = 'Imagem não legível: '.$image;
            Log::warning("cnh.cli.{$operation}.image_unreadable", ['image_path' => $image]);

            return new CnhCliRunResult(
                command: '(não executado)',
                durationMs: 0,
                exitCode: -1,
                combinedOutput: '',
                binaryConfigured: true,
                binaryExists: true,
                diagnostic: $diag,
            );
        }

        $qBin = self::quotePathForCmd($binary);
        $qImg = self::quotePathForCmd($image);
        $before = trim($argsBeforeImage);
        $after = trim($argsAfterImage);

        $segments = array_filter([$qBin, $before !== '' ? $before : null, $qImg, $after !== '' ? $after : null]);
        $command = implode(' ', $segments).' 2>&1';

        $t0 = microtime(true);
        try {
            $process = Process::fromShellCommandline($command);
            $process->setTimeout($timeoutSeconds);
            $process->run();
        } catch (\Throwable $e) {
            $ms = (microtime(true) - $t0) * 1000;
            $diag = 'Falha ao executar processo: '.$e->getMessage();
            Log::error("cnh.cli.{$operation}.process_exception", [
                'command' => $command,
                'duration_ms' => round($ms, 2),
                'exception' => $e->getMessage(),
            ]);

            return new CnhCliRunResult(
                command: $command,
                durationMs: round($ms, 2),
                exitCode: -1,
                combinedOutput: '',
                binaryConfigured: true,
                binaryExists: true,
                diagnostic: $diag,
            );
        }

        $ms = (microtime(true) - $t0) * 1000;
        $exitCode = $process->getExitCode() ?? -1;
        $out = $process->getOutput();
        if ($process->getErrorOutput() !== '') {
            $out .= $process->getErrorOutput();
        }

        Log::info("cnh.cli.{$operation}.done", [
            'binary_path' => $binary,
            'image_path' => $image,
            'command' => $command,
            'duration_ms' => round($ms, 2),
            'exit_code' => $exitCode,
            'output_length' => strlen($out),
            'output_preview' => self::previewForLog($out),
        ]);

        return new CnhCliRunResult(
            command: $command,
            durationMs: round($ms, 2),
            exitCode: $exitCode,
            combinedOutput: $out,
            binaryConfigured: true,
            binaryExists: true,
        );
    }

    private static function quotePathForCmd(string $path): string
    {
        $path = CnhCliPaths::normalize($path);
        $path = str_replace('"', '""', $path);

        return '"'.$path.'"';
    }

    private static function previewForLog(string $out): string
    {
        $out = trim($out);
        if ($out === '') {
            return '';
        }

        $oneLine = preg_replace('/\s+/', ' ', $out) ?? $out;

        return mb_substr($oneLine, 0, 400);
    }
}
