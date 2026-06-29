<?php

namespace App\Services\Concierge;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lists the Gemini models this API key can actually use (live ListModels API,
 * cached), and probes a model's current availability.
 */
class GeminiModels
{
    private const CACHE_KEY = 'concierge:models';

    /** @return string[] gemini model ids that support generateContent */
    public static function available(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
            $key = config('concierge.gemini.key');
            $endpoint = rtrim(config('concierge.gemini.endpoint'), '/');

            if (! $key) {
                return config('concierge.models_fallback');
            }

            try {
                $res = Http::timeout(15)
                    ->withQueryParameters(['key' => $key, 'pageSize' => 200])
                    ->acceptJson()
                    ->get($endpoint);

                if ($res->failed()) {
                    return config('concierge.models_fallback');
                }

                $models = collect($res->json('models', []))
                    ->filter(fn ($m) => in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true))
                    ->map(fn ($m) => str_replace('models/', '', $m['name'] ?? ''))
                    ->filter(fn ($id) => str_starts_with($id, 'gemini'))
                    ->values()
                    ->all();

                return $models ?: config('concierge.models_fallback');
            } catch (\Throwable $e) {
                Log::warning('Gemini ListModels failed', ['message' => $e->getMessage()]);

                return config('concierge.models_fallback');
            }
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Probe a model with a tiny request (consumes 1 call against quota).
     *
     * @return array{ok: bool, status: int, message: string}
     */
    public static function probe(string $model): array
    {
        $key = config('concierge.gemini.key');
        $endpoint = rtrim(config('concierge.gemini.endpoint'), '/');

        if (! $key) {
            return ['ok' => false, 'status' => 0, 'message' => 'API key belum diset.'];
        }

        try {
            $res = Http::timeout(15)
                ->withQueryParameters(['key' => $key])
                ->acceptJson()
                ->post("{$endpoint}/{$model}:generateContent", [
                    'contents' => [['role' => 'user', 'parts' => [['text' => 'ok']]]],
                ]);

            ConciergeUsage::increment($model);

            if ($res->successful()) {
                return ['ok' => true, 'status' => 200, 'transient' => false, 'message' => 'Tersedia'];
            }

            if ($res->status() === 429) {
                return self::interpretRateLimit($res->json());
            }

            if (in_array($res->status(), [500, 502, 503], true)) {
                return ['ok' => false, 'status' => $res->status(), 'transient' => true, 'message' => 'Server Gemini sibuk, coba lagi'];
            }

            return [
                'ok' => false,
                'status' => $res->status(),
                'transient' => false,
                'message' => (string) data_get($res->json(), 'error.status', 'Gagal'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 0, 'transient' => false, 'message' => 'Error koneksi'];
        }
    }

    /**
     * Distinguish a per-minute throttle (transient — recovers in seconds) from
     * a per-day quota exhaustion (gone until reset).
     *
     * @return array{ok: bool, status: int, transient: bool, message: string}
     */
    private static function interpretRateLimit(?array $json): array
    {
        $details = collect(data_get($json, 'error.details', []));

        $quotaIds = $details
            ->where('@type', 'type.googleapis.com/google.rpc.QuotaFailure')
            ->flatMap(fn ($d) => collect($d['violations'] ?? [])->pluck('quotaId'))
            ->filter()
            ->implode(' ');

        $retry = data_get(
            $details->firstWhere('@type', 'type.googleapis.com/google.rpc.RetryInfo'),
            'retryDelay'
        );

        if (str_contains($quotaIds, 'PerDay')) {
            return ['ok' => false, 'status' => 429, 'transient' => false, 'message' => 'Kuota harian habis'];
        }

        // Per-minute requests/tokens — temporary.
        $suffix = $retry ? " (coba lagi ~{$retry})" : ' (coba lagi sebentar)';

        return ['ok' => false, 'status' => 429, 'transient' => true, 'message' => 'Limit per-menit'.$suffix];
    }
}
