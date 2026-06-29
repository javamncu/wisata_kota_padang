<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', ReviewStatus::Pending->value);

        $reviews = Review::query()
            ->with(['user', 'destination'])
            ->when(in_array($status, ReviewStatus::values(), true),
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => ReviewStatus::options(),
            'currentStatus' => $status,
            'pendingCount' => Review::where('status', ReviewStatus::Pending)->count(),
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['status' => ReviewStatus::Published]);

        return back()->with('status', 'Review dipublikasikan.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review dihapus.');
    }
}
