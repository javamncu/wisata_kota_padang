<?php

namespace App\Http\Controllers;

use App\Services\Search\DestinationSearch;
use App\Services\Search\FilterOptions;
use App\Services\Search\SearchCriteria;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index(Request $request, DestinationSearch $search, FilterOptions $options): View
    {
        $criteria = SearchCriteria::fromRequest($request);
        $destinations = $search->paginate($criteria, (int) setting('per_page', 12));

        return view('public.explore', [
            'criteria' => $criteria,
            'destinations' => $destinations,
            'categories' => $options->categories(),
            'tagsByType' => $options->tagsByType(),
            'enums' => $options->enums(),
            'lockedCategory' => null,
        ]);
    }
}
