<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['category', 'images'])
            ->orderByDesc('favorites.created_at')
            ->paginate(12);

        return view('user.favorites', compact('favorites'));
    }

    public function toggle(Request $request, Destination $destination): RedirectResponse
    {
        $result = $request->user()->favorites()->toggle($destination->id);

        $added = $result['attached'] !== [];

        return back()->with('status', $added
            ? 'Ditambahkan ke favorit.'
            : 'Dihapus dari favorit.');
    }
}
