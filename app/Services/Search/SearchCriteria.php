<?php

namespace App\Services\Search;

use App\Enums\City;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\Zone;
use Illuminate\Http\Request;

/**
 * Normalised, validated representation of an Explore search request.
 *
 * One object is read by DestinationSearch to build the query. Multi-select
 * facets are kept as arrays of valid enum backing values / slugs; anything
 * the request sends that isn't a known value is silently dropped.
 */
class SearchCriteria
{
    public const SORTS = ['populer', 'rating', 'az'];

    public function __construct(
        public readonly ?string $keyword = null,
        public readonly ?string $category = null,
        public readonly ?string $city = null,
        /** @var string[] */
        public readonly array $zones = [],
        /** @var string[] */
        public readonly array $priceRanges = [],
        /** @var string[] */
        public readonly array $indoorOutdoor = [],
        /** @var string[] */
        public readonly array $durations = [],
        /** @var string[] */
        public readonly array $cocokUntuk = [],
        /** @var string[] */
        public readonly array $waktuIdeal = [],
        /** @var string[] */
        public readonly array $tags = [],
        /** @var string[] Free-text terms to exclude (matched on name/description). */
        public readonly array $excludeKeywords = [],
        public readonly string $sort = 'populer',
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        // Default sort comes from site settings (falls back to 'populer').
        $defaultSort = setting('default_sort', 'populer');
        $defaultSort = in_array($defaultSort, self::SORTS, true) ? $defaultSort : 'populer';

        return new self(
            keyword: self::cleanString($request->input('q')),
            category: self::cleanString($request->input('category')),
            city: in_array($request->input('city'), City::values(), true)
                ? $request->input('city')
                : null,
            zones: self::onlyValid($request->input('zone'), Zone::values()),
            priceRanges: self::onlyValid($request->input('price'), PriceRange::values()),
            indoorOutdoor: self::onlyValid($request->input('io'), IndoorOutdoor::values()),
            durations: self::onlyValid($request->input('duration'), Duration::values()),
            cocokUntuk: self::onlyValid($request->input('cocok'), \App\Enums\CocokUntuk::values()),
            waktuIdeal: self::onlyValid($request->input('waktu'), \App\Enums\WaktuIdeal::values()),
            tags: self::toStringArray($request->input('tags')),
            excludeKeywords: self::toStringArray($request->input('exclude')),
            sort: in_array($request->input('sort'), self::SORTS, true)
                ? $request->input('sort')
                : $defaultSort,
        );
    }

    /** Whether any filter (beyond default sort) is active. */
    public function hasAnyFilter(): bool
    {
        return $this->keyword !== null
            || $this->category !== null
            || $this->city !== null
            || $this->zones !== []
            || $this->priceRanges !== []
            || $this->indoorOutdoor !== []
            || $this->durations !== []
            || $this->cocokUntuk !== []
            || $this->waktuIdeal !== []
            || $this->tags !== []
            || $this->excludeKeywords !== [];
    }

    private static function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /** Coerce request input to a flat array of non-empty strings. */
    private static function toStringArray(mixed $value): array
    {
        return collect(is_array($value) ? $value : [$value])
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->values()
            ->all();
    }

    /** Keep only values present in the given allow-list. */
    private static function onlyValid(mixed $value, array $allowed): array
    {
        return array_values(array_intersect(self::toStringArray($value), $allowed));
    }
}
