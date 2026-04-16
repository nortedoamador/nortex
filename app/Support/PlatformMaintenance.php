<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class PlatformMaintenance
{
    public const CACHE_KEY = 'platform.maintenance.enabled';

    public static function enabled(): bool
    {
        return (bool) Cache::get(self::CACHE_KEY, false);
    }

    public static function enable(): void
    {
        Cache::forever(self::CACHE_KEY, true);
    }

    public static function disable(): void
    {
        Cache::forever(self::CACHE_KEY, false);
    }
}
