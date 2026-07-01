<?php

namespace App\Http\Controllers;

use App\Enums\Zone;
use App\Services\Search\DestinationSearch;
use App\Services\Search\FilterOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NearbyController extends Controller
{
    // 0 = "Semua" (tanpa batas jarak, diurutkan dari terdekat).
    private const RADII = [5, 10, 25, 50, 100];

    public function index(Request $request, DestinationSearch $search, FilterOptions $options): View
    {
        $lat = $this->coordinate($request->input('lat'), 90);
        $lng = $this->coordinate($request->input('lng'), 180);

        // Default 0 (Semua) supaya pengunjung di luar Padang pun tetap dapat hasil.
        $radius = (int) $request->input('radius', 0);
        if (! in_array($radius, array_merge([0], self::RADII), true)) {
            $radius = 0;
        }

        $category = $request->filled('category') ? $request->input('category') : null;

        $hasLocation = $lat !== null && $lng !== null;
        $results = $hasLocation
            ? $search->nearby($lat, $lng, $radius, $category)
            : new Collection();

        return view('public.nearby', [
            'hasLocation' => $hasLocation,
            'results' => $results,
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
            'radii' => self::RADII,
            'category' => $category,
            'categories' => $options->categories(),
            'zones' => Zone::options(),
        ]);
    }

    /** Validate a coordinate within ±$max, else null. */
    private function coordinate(mixed $value, float $max): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $value = (float) $value;

        return abs($value) <= $max ? $value : null;
    }
}
