<?php

namespace App\Services\Cnh;

/**
 * Resultado de execução de binário externo (zbar / tesseract).
 */
final class CnhCliRunResult
{
    public function __construct(
        public readonly string $command,
        public readonly float $durationMs,
        public readonly int $exitCode,
        public readonly string $combinedOutput,
        public readonly bool $binaryConfigured,
        public readonly bool $binaryExists,
        public readonly ?string $diagnostic = null,
    ) {}

    public function outputTrimmed(): string
    {
        return trim($this->combinedOutput);
    }
}
