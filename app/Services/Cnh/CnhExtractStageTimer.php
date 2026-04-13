<?php

namespace App\Services\Cnh;

/**
 * Mede tempos entre etapas e desde o início da extração (para logs).
 */
final class CnhExtractStageTimer
{
    private readonly float $start;

    private float $last;

    public function __construct()
    {
        $this->start = microtime(true);
        $this->last = $this->start;
    }

    /**
     * @return array{since_start_ms: float, stage_delta_ms: float}
     */
    public function mark(): array
    {
        $now = microtime(true);
        $sinceStart = ($now - $this->start) * 1000;
        $delta = ($now - $this->last) * 1000;
        $this->last = $now;

        return [
            'since_start_ms' => round($sinceStart, 2),
            'stage_delta_ms' => round($delta, 2),
        ];
    }

    public function totalMs(): float
    {
        return round((microtime(true) - $this->start) * 1000, 2);
    }
}
