<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Key-value site settings with a forever cache that is busted on write.
 * Stored values override the code DEFAULTS, so the site always has values
 * even before an admin saves anything.
 */
class Settings
{
    private const CACHE_KEY = 'app.settings';

    public const DEFAULTS = [
        // Umum
        'site_name' => 'Wisata Kota Padang',
        'contact_email' => 'info@wisatapadang.my.id',
        'contact_phone' => '',
        'social_instagram' => '@wisatakotapadang',
        // Konten Beranda
        'hero_title' => 'Jelajahi keindahan Kota Padang',
        'hero_subtitle' => 'Temukan tempat wisata, kuliner, dan budaya terbaik — disesuaikan dengan preferensimu.',
        'about_text' => 'Satu pintu informasi wisata, kuliner, dan budaya Kota Padang.',
        'featured_slugs' => [], // empty = otomatis (paling banyak diulas)
        // Budget (Opsi A: label/ambang saja — tidak mengubah enum/logika)
        'budget_gratis' => 'Tidak dipungut biaya masuk',
        'budget_murah' => 'Di bawah Rp50.000 per orang',
        'budget_sedang' => 'Sekitar Rp50.000 - Rp150.000 per orang',
        'budget_premium' => 'Di atas Rp150.000 per orang',
        // Tampilan
        'per_page' => 12,
        'default_sort' => 'populer',
        // AI Concierge — kosong = pakai default dari config/.env
        'concierge_model' => '',
    ];

    /** @return array<string, mixed> defaults merged with stored overrides */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = Setting::query()->pluck('value', 'key')->toArray();

            return array_merge(self::DEFAULTS, $stored);
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    /** @param array<string, mixed> $pairs */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        self::forget();
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
