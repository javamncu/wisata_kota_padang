<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\Concierge\ConciergeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ConciergeController extends Controller
{
    /** Clickable example prompts shown on the chat page. */
    private const EXAMPLES = [
        'Kuliner enak dekat pantai untuk malam hari',
        'Wisata keluarga seharian yang ramah anak',
        'Tempat instagramable yang gratis',
        'Wisata religi di pusat kota',
    ];

    public function index(): View
    {
        return view('public.concierge', ['examples' => self::EXAMPLES]);
    }

    public function ask(Request $request, ConciergeService $concierge): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        if ($limit = $this->rateLimited($request)) {
            return $limit;
        }

        $result = $concierge->answer($request->input('message'));

        return response()->json([
            'reply' => $result['reply'],
            'off_topic' => $result['off_topic'],
            'destinations' => $result['destinations']->map(fn (Destination $d) => [
                'name' => $d->name,
                'url' => route('destinations.show', $d),
                'image' => $d->coverUrl(),
                'category' => $d->category->name,
                'city' => $d->city?->label(),
                'price' => $d->price_range->label(),
                'rating' => $d->rating_cache !== null ? (float) $d->rating_cache : null,
                'reviews' => $d->review_count_cache,
            ])->values(),
        ]);
    }

    /**
     * Cost-control rate limiting: admins unlimited, users looser than guests.
     * Returns a 429 JsonResponse when the cap is hit, else null.
     */
    private function rateLimited(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return null;
        }

        $max = (int) config($user
            ? 'concierge.rate_limits.user'
            : 'concierge.rate_limits.guest');
        $decay = (int) config('concierge.rate_limits.decay_seconds', 86400);
        $key = 'concierge:'.($user?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $hours = (int) ceil(RateLimiter::availableIn($key) / 3600);

            return response()->json([
                'reply' => 'Kamu sudah mencapai batas chat hari ini. '
                    .($user ? '' : 'Login untuk batas yang lebih besar. ')
                    ."Coba lagi dalam ±{$hours} jam.",
                'off_topic' => false,
                'destinations' => [],
                'limited' => true,
            ], 429);
        }

        RateLimiter::hit($key, $decay);

        return null;
    }
}
