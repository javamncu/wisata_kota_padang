<?php

namespace App\Services\Concierge;

use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\WaktuIdeal;
use App\Enums\Zone;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Search\SearchCriteria;
use Illuminate\Support\Collection;

/**
 * The controlled vocabulary the LLM must map a free-text question onto, plus
 * the backend validation that turns the LLM's JSON into a Fase 1
 * SearchCriteria. Anything the LLM returns that isn't in these allow-lists is
 * dropped — this is the guardrail that keeps the AI grounded in our data.
 */
class Vocabulary
{
    /** @var Collection<string,string> slug => name */
    public readonly Collection $categories;

    /** @var Collection<string,string> slug => name */
    public readonly Collection $tags;

    public function __construct()
    {
        $this->categories = Category::query()->active()->pluck('name', 'slug');
        $this->tags = Tag::query()->pluck('name', 'slug');
    }

    /**
     * Validate the LLM's raw filter object and build a SearchCriteria.
     * Only known-good values survive.
     */
    public function toCriteria(array $raw): SearchCriteria
    {
        return new SearchCriteria(
            keyword: $this->cleanKeyword($raw['keyword'] ?? null),
            category: $this->categories->keys()->contains($raw['category'] ?? null) ? $raw['category'] : null,
            zones: $this->only($raw['zone'] ?? null, Zone::values()),
            priceRanges: $this->only($raw['price_range'] ?? null, PriceRange::values()),
            indoorOutdoor: $this->only($raw['indoor_outdoor'] ?? null, IndoorOutdoor::values()),
            durations: $this->only($raw['duration'] ?? null, Duration::values()),
            cocokUntuk: $this->onlyMany($raw['cocok_untuk'] ?? [], CocokUntuk::values()),
            waktuIdeal: $this->onlyMany($raw['waktu_ideal'] ?? [], WaktuIdeal::values()),
            tags: $this->onlyMany($raw['tags'] ?? [], $this->tags->keys()->all()),
        );
    }

    /** Human-readable allow-list block injected into the LLM prompt. */
    public function forPrompt(): string
    {
        $tagsByType = Tag::query()->orderBy('name')->get()->groupBy(fn (Tag $t) => $t->type->value);

        $lines = [];
        $lines[] = 'category (pilih satu slug): '.$this->categories->keys()->implode(', ');
        $lines[] = 'zone: '.implode(', ', Zone::values());
        $lines[] = 'price_range: '.implode(', ', PriceRange::values());
        $lines[] = 'indoor_outdoor: '.implode(', ', IndoorOutdoor::values());
        $lines[] = 'duration: '.implode(', ', Duration::values());
        $lines[] = 'cocok_untuk (boleh beberapa): '.implode(', ', CocokUntuk::values());
        $lines[] = 'waktu_ideal (boleh beberapa): '.implode(', ', WaktuIdeal::values());

        foreach (['suasana', 'aktivitas', 'fasilitas'] as $type) {
            $slugs = ($tagsByType->get($type) ?? collect())->pluck('slug')->implode(', ');
            $lines[] = "tags ({$type}): {$slugs}";
        }

        return implode("\n", $lines);
    }

    private function cleanKeyword(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 100);
    }

    /** Single scalar → [value] if it's in the allow-list, else []. */
    private function only(mixed $value, array $allowed): array
    {
        return is_string($value) && in_array($value, $allowed, true) ? [$value] : [];
    }

    /** Array → only the values present in the allow-list. */
    private function onlyMany(mixed $values, array $allowed): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_intersect(
            array_filter($values, 'is_string'),
            $allowed,
        ));
    }
}
