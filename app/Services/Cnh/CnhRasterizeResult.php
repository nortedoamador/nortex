<?php

namespace App\Services\Cnh;

/**
 * Resultado da rasterização da 1.ª página do PDF (Imagick + Ghostscript).
 */
final class CnhRasterizeResult
{
    public function __construct(
        public readonly ?string $pngPath,
        public readonly ?string $errorMessage = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->pngPath !== null
            && $this->pngPath !== ''
            && is_readable($this->pngPath);
    }
}
