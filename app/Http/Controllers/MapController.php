<?php

namespace App\Http\Controllers;

use App\Enums\Zone;
use App\Models\Category;
use App\Models\Destination;
use Illuminate\Contracts\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $markers = Destination::query()
            ->active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('category')
            ->get()
            ->map(fn (Destination $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'lat' => (float) $d->latitude,
                'lng' => (float) $d->longitude,
                'category' => $d->category->name,
                'categorySlug' => $d->category->slug,
                'zone' => $d->zone->value,
                'zoneLabel' => $d->zone->label(),
                'rating' => $d->rating_cache !== null ? (float) $d->rating_cache : null,
                'url' => route('destinations.show', $d),
            ])
            ->values();

        return view('public.map', [
            'markers' => $markers,
            'categories' => Category::query()->active()->orderBy('name')->get(),
            'zones' => Zone::options(),
        ]);
    }
}
