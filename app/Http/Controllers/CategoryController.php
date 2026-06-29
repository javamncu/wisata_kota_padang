<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Search\DestinationSearch;
use App\Services\Search\FilterOptions;
use App\Services\Search\SearchCriteria;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * The category page is Explore pre-filtered to one category — it reuses
     * the same view, search engine and filter panel (with the category
     * selector hidden).
     */
    public function show(Request $request, Category $category, DestinationSearch $search, FilterOptions $options): View
    {
        abort_unless($category->is_active, 404);

        // Force the category facet to this category regardless of the query.
        $request->merge(['category' => $category->slug]);
        $criteria = SearchCriteria::fromRequest($request);

        $destinations = $search->paginate($criteria, (int) setting('per_page', 12));

        return view('public.explore', [
            'criteria' => $criteria,
            'destinations' => $destinations,
            'categories' => $options->categories(),
            'tagsByType' => $options->tagsByType(),
            'enums' => $options->enums(),
            'lockedCategory' => $category,
        ]);
    }
}
