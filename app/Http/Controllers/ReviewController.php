<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function mine(Request $request): View
    {
        $reviews = $request->user()
            ->reviews()
            ->with('destination')
            ->latest()
            ->paginate(10);

        return view('user.reviews', compact('reviews'));
    }

    public function store(Request $request, Destination $destination): RedirectResponse
    {
        $this->authorize('create', Review::class);

        $data = $this->validateReview($request);

        // One review per user per destination — create or overwrite the draft.
        $destination->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [...$data, 'status' => ReviewStatus::Pending],
        );

        return redirect()
            ->route('destinations.show', $destination)
            ->with('status', 'Review terkirim dan menunggu moderasi admin.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $data = $this->validateReview($request);

        // Edited reviews go back to moderation.
        $review->update([...$data, 'status' => ReviewStatus::Pending]);

        return redirect()
            ->route('destinations.show', $review->destination)
            ->with('status', 'Review diperbarui dan menunggu moderasi ulang.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $destination = $review->destination;
        $review->delete();

        return redirect()
            ->route('destinations.show', $destination)
            ->with('status', 'Review dihapus.');
    }

    /**
     * @return array{rating: int, comment: ?string}
     */
    private function validateReview(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
