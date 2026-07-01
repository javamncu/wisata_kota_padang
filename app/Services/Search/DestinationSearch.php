<?php

namespace App\Services\Search;

use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Turns a SearchCriteria into a query over active destinations.
 *
 * This is the single search/scoring engine the PRD describes: keyword and
 * filters are plain query clauses; the quiz (see QuizScoringService) layers
 * rule-based scoring on top of the same data.
 */
class DestinationSearch
{
    /**
     * Build (but don't execute) the filtered query.
     */
    public function query(SearchCriteria $c): Builder
    {
        $query = Destination::query()
            ->active()
            ->with(['category', 'images', 'tags']);

        $this->applyKeyword($query, $c);
        $this->applyExclude($query, $c);
        $this->applyCategory($query, $c);
        $this->applyCity($query, $c);
        $this->applyEnumFilters($query, $c);
        $this->applyJsonFilters($query, $c);
        $this->applyTags($query, $c);
        $this->applySort($query, $c);

        return $query;
    }

    public function paginate(SearchCriteria $c, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query($c)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Active destinations near ($lat, $lng), nearest first. Each result carries
     * a `distance_m` attribute (metres). Reuses the same destinations data as
     * Explore — this is "Explore, sorted by distance".
     *
     * $radiusKm <= 0 means no distance limit (show all, sorted by distance) so
     * visitors outside the city still get results with their distance shown.
     */
    public function nearby(float $lat, float $lng, int $radiusKm, ?string $categorySlug = null): Collection
    {
        $query = Destination::query()
            ->active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($categorySlug, fn (Builder $q) => $q->whereHas(
                'category', fn (Builder $c) => $c->where('slug', $categorySlug)
            ))
            ->select('destinations.*')
            ->selectRaw(
                'ST_Distance_Sphere(POINT(?, ?), POINT(longitude, latitude)) AS distance_m',
                [$lng, $lat],
            )
            ->orderBy('distance_m')
            ->with(['category', 'images']);

        if ($radiusKm > 0) {
            $query->having('distance_m', '<=', $radiusKm * 1000);
        }

        return $query->get();
    }

    private function applyKeyword(Builder $query, SearchCriteria $c): void
    {
        if ($c->keyword === null) {
            return;
        }

        $like = '%'.$c->keyword.'%';

        $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('description_short', 'like', $like)
                ->orWhere('description_long', 'like', $like);
        });
    }

    /**
     * Exclusion: drop destinations matching any "don't want" term (e.g. the
     * user said "jangan sate"). A destination is removed if the term appears in
     * its name or descriptions — applied as an AND of NOTs across terms.
     */
    private function applyExclude(Builder $query, SearchCriteria $c): void
    {
        foreach ($c->excludeKeywords as $term) {
            $like = '%'.$term.'%';

            $query->whereNot(function (Builder $q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('description_short', 'like', $like)
                    ->orWhere('description_long', 'like', $like);
            });
        }
    }

    private function applyCategory(Builder $query, SearchCriteria $c): void
    {
        if ($c->category === null) {
            return;
        }

        $query->whereHas('category', fn (Builder $q) => $q->where('slug', $c->category));
    }

    private function applyCity(Builder $query, SearchCriteria $c): void
    {
        if ($c->city === null) {
            return;
        }

        $query->where('city', $c->city);
    }

    private function applyEnumFilters(Builder $query, SearchCriteria $c): void
    {
        if ($c->zones !== []) {
            $query->whereIn('zone', $c->zones);
        }

        if ($c->priceRanges !== []) {
            $query->whereIn('price_range', $c->priceRanges);
        }

        if ($c->indoorOutdoor !== []) {
            $query->whereIn('indoor_outdoor', $c->indoorOutdoor);
        }

        if ($c->durations !== []) {
            $query->whereIn('duration', $c->durations);
        }
    }

    /**
     * JSON multi-value facets: a destination matches if it contains ANY of
     * the selected values (OR within the facet).
     */
    private function applyJsonFilters(Builder $query, SearchCriteria $c): void
    {
        if ($c->cocokUntuk !== []) {
            $query->where(function (Builder $q) use ($c) {
                foreach ($c->cocokUntuk as $value) {
                    $q->orWhereJsonContains('cocok_untuk', $value);
                }
            });
        }

        if ($c->waktuIdeal !== []) {
            $query->where(function (Builder $q) use ($c) {
                foreach ($c->waktuIdeal as $value) {
                    $q->orWhereJsonContains('waktu_ideal', $value);
                }
            });
        }
    }

    /**
     * Tags filter: destination must have at least one of the selected tags.
     */
    private function applyTags(Builder $query, SearchCriteria $c): void
    {
        if ($c->tags === []) {
            return;
        }

        $query->whereHas('tags', fn (Builder $q) => $q->whereIn('slug', $c->tags));
    }

    private function applySort(Builder $query, SearchCriteria $c): void
    {
        match ($c->sort) {
            'rating' => $query->orderByDesc('rating_cache')
                ->orderByDesc('review_count_cache'),
            'az' => $query->orderBy('name'),
            default => $query->orderByDesc('review_count_cache')
                ->orderByDesc('rating_cache')
                ->orderBy('name'),
        };
    }
}
