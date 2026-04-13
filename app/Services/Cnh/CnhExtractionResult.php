<?php

namespace App\Services\Cnh;

final class CnhExtractionResult
{
    /**
     * @param  array<string, string|int|null>|null  $data
     */
    public function __construct(
        public bool $ok,
        public ?array $data = null,
        public string $source = 'none',
        public ?string $message = null,
    ) {}
}
