<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'destinations' => Destination::count(),
            'active' => Destination::where('status', Status::Aktif)->count(),
            'draft' => Destination::where('status', Status::Draft)->count(),
            'categories' => Category::count(),
            'tags' => Tag::count(),
            'users' => User::count(),
            'pendingReviews' => Review::where('status', ReviewStatus::Pending)->count(),
        ];

        $byCategory = Category::query()
            ->withCount('destinations')
            ->orderByDesc('destinations_count')
            ->get();

        $latestReviews = Review::query()
            ->with(['user', 'destination'])
            ->where('status', ReviewStatus::Pending)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'byCategory', 'latestReviews'));
    }
}
