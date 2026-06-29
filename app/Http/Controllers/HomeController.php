<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount(['destinations' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        $featuredSlugs = (array) setting('featured_slugs', []);

        if ($featuredSlugs !== []) {
            // Admin-curated featured destinations (preserve chosen order).
            $featured = Destination::query()
                ->active()
                ->with(['category', 'images'])
                ->whereIn('slug', $featuredSlugs)
                ->get()
                ->sortBy(fn (Destination $d) => array_search($d->slug, $featuredSlugs))
                ->values();
        } else {
            // Fallback: most-reviewed / highest-rated.
            $featured = Destination::query()
                ->active()
                ->with(['category', 'images'])
                ->orderByDesc('review_count_cache')
                ->orderByDesc('rating_cache')
                ->orderBy('name')
                ->take(6)
                ->get();
        }

        return view('public.home', compact('categories', 'featured'));
    }

    public function about(): View
    {
        return view('public.about');
    }
}
