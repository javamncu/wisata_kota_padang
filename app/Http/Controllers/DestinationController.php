<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Destination;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function show(Request $request, Destination $destination): View
    {
        // Drafts are hidden from the public (404, not 403 — don't leak that
        // they exist); admins can still preview them.
        if ($destination->status !== Status::Aktif && ! $request->user()?->isAdmin()) {
            abort(404);
        }

        $destination->load([
            'category',
            'images',
            'tags',
            'publishedReviews' => fn ($q) => $q->with('user')->latest(),
        ]);

        $similar = Destination::query()
            ->active()
            ->where('id', '!=', $destination->id)
            ->where('category_id', $destination->category_id)
            ->with(['category', 'images'])
            ->orderByDesc('rating_cache')
            ->orderByDesc('review_count_cache')
            ->take(4)
            ->get();

        $user = $request->user();

        $myReview = $user
            ? $destination->reviews()->where('user_id', $user->id)->first()
            : null;

        $isFavorited = $user
            ? $user->favorites()->whereKey($destination->id)->exists()
            : false;

        return view('public.destinations.show', compact(
            'destination',
            'similar',
            'myReview',
            'isFavorited',
        ));
    }
}
