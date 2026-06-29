<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PriceRange;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'settings' => Settings::all(),
            'destinations' => Destination::orderBy('name')->get(['id', 'name', 'slug']),
            'priceRanges' => PriceRange::cases(),
            'sortOptions' => [
                'populer' => 'Paling Populer',
                'rating' => 'Rating Tertinggi',
                'az' => 'Nama A-Z',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'social_instagram' => ['nullable', 'string', 'max:100'],
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_subtitle' => ['nullable', 'string', 'max:300'],
            'about_text' => ['nullable', 'string', 'max:1000'],
            'featured_slugs' => ['nullable', 'array', 'max:6'],
            'featured_slugs.*' => ['string', 'exists:destinations,slug'],
            'budget_gratis' => ['nullable', 'string', 'max:150'],
            'budget_murah' => ['nullable', 'string', 'max:150'],
            'budget_sedang' => ['nullable', 'string', 'max:150'],
            'budget_premium' => ['nullable', 'string', 'max:150'],
            'per_page' => ['required', 'integer', 'min:4', 'max:48'],
            'default_sort' => ['required', Rule::in(['populer', 'rating', 'az'])],
        ]);

        $data['featured_slugs'] = $data['featured_slugs'] ?? [];
        $data['per_page'] = (int) $data['per_page'];

        Settings::setMany($data);

        return back()->with('status', 'Pengaturan disimpan.');
    }
}
