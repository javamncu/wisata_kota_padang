<?php

namespace App\Services\Search;

use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\WaktuIdeal;
use App\Enums\Zone;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Collection;

/**
 * Builds the option lists rendered in the filter panel / quiz forms.
 * Kept in one place so Explore, Category and Quiz stay in sync.
 */
class FilterOptions
{
    /** @return Collection<int, Category> */
    public function categories(): Collection
    {
        return Category::query()->active()->orderBy('name')->get();
    }

    /** Tags grouped by their type (suasana / aktivitas / fasilitas). */
    public function tagsByType(): Collection
    {
        return Tag::query()->orderBy('name')->get()->groupBy(fn (Tag $t) => $t->type->value);
    }

    /** value => label maps for every enum facet. */
    public function enums(): array
    {
        return [
            'zone' => Zone::options(),
            'price' => PriceRange::options(),
            'io' => IndoorOutdoor::options(),
            'duration' => Duration::options(),
            'cocok' => CocokUntuk::options(),
            'waktu' => WaktuIdeal::options(),
        ];
    }
}
