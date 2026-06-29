<?php

namespace App\Services\Concierge;

use Illuminate\Support\Facades\Cache;

/**
 * Per-model, per-day counter of Gemini calls this app has made. Google does
 * not expose remaining free-tier quota via API, so this local count (plus the
 * live status probe) is how the admin gauges usage.
 */
class ConciergeUsage
{
    public static function used(string $model): int
    {
        return (int) Cache::get(self::key($model), 0);
    }

    public static function increment(string $model): void
    {
        $key = self::key($model);
        Cache::add($key, 0, now()->endOfDay());
        Cache::increment($key);
    }

    /**
     * @param  string[]  $models
     * @return array<string, int> model => calls used today
     */
    public static function usedMany(array $models): array
    {
        $out = [];
        foreach ($models as $model) {
            $out[$model] = self::used($model);
        }

        return $out;
    }

    private static function key(string $model): string
    {
        return 'concierge:usage:'.$model.':'.now()->toDateString();
    }
}
