<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class PlatformMaintenance
{
    public const CACHE_KEY = 'platform.maintenance.enabled';

    public static function enabled(): bool
    {
        // Use file cache for this flag so the app can boot even if DB is down.
        $store = Cache::store('file');
        return (bool) $store->get(self::CACHE_KEY, false);
    }

    public static function enable(): void
    {
        Cache::store('file')->forever(self::CACHE_KEY, true);
    }

    public static function disable(): void
    {
        Cache::store('file')->forever(self::CACHE_KEY, false);
    }
}
