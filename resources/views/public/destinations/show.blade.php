@php
    $cover = $destination->coverUrl();
    $hasCoords = $destination->latitude !== null && $destination->longitude !== null;
@endphp

<x-public-layout :title="$destination->name.' — Wisata Kota Padang'">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-3">
            <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a> /
            <a href="{{ route('categories.show', $destination->category) }}" class="hover:text-emerald-700">{{ $destination->category->name }}</a> /
            <span class="text-gray-600">{{ $destination->name }}</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="inline-block rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">{{ $destination->category->name }}</span>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ $destination->name }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-4">
                    <x-star-rating :rating="$destination->rating_cache" :count="$destination->review_count_cache" />
                    @if ($destination->city)
                        <a href="{{ route('explore', ['city' => $destination->city->value]) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-emerald-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $destination->city->label() }}
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $destination->zone->label() }}
                    </span>
                </div>
            </div>

            {{-- Favorite --}}
            <div>
                @auth
                    <form method="POST" action="{{ route('favorites.toggle', $destination) }}">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition
                            {{ $isFavorited ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-gray-200 bg-white text-gray-700 hover:border-rose-200 hover:text-rose-600' }}">
                            <svg class="h-5 w-5 {{ $isFavorited ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            {{ $isFavorited ? 'Tersimpan' : 'Simpan' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:border-rose-200 hover:text-rose-600">
                        <svg class="h-5 w-5 fill-none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Simpan
                    </a>
                @endauth
            </div>
        </div>

        {{-- Gallery --}}
        <div class="mt-6 overflow-hidden rounded-2xl">
            @if ($cover)
                <div x-data="{ active: '{{ $cover }}' }">
                    <img :src="active" alt="{{ $destination->name }}" class="aspect-[16/9] w-full object-cover">
                    @if ($destination->images->count() > 1)
                        <div class="mt-2 flex gap-2 overflow-x-auto">
                            @foreach ($destination->images as $img)
                                <button type="button" @click="active = '{{ $img->url }}'" class="h-20 w-28 flex-shrink-0 overflow-hidden rounded-lg ring-2 ring-transparent" :class="active === '{{ $img->url }}' ? 'ring-emerald-500' : ''">
                                    <img src="{{ $img->url }}" class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-br from-emerald-400 to-teal-600">
                    <span class="text-2xl font-bold text-white/90 drop-shadow">{{ $destination->name }}</span>
                </div>
            @endif
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
            {{-- Main --}}
            <div class="space-y-8">
                {{-- Attributes --}}
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-700">💰 {{ $destination->price_range->label() }}</span>
                    <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-700">🏠 {{ $destination->indoor_outdoor->label() }}</span>
                    <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-700">⏱️ {{ $destination->duration->label() }}</span>
                    @foreach ($destination->cocok_untuk as $c)
                        <span class="rounded-lg bg-emerald-50 px-3 py-1.5 text-sm text-emerald-700">👥 {{ $c->label() }}</span>
                    @endforeach
                    @foreach ($destination->waktu_ideal as $w)
                        <span class="rounded-lg bg-amber-50 px-3 py-1.5 text-sm text-amber-700">🕒 {{ $w->label() }}</span>
                    @endforeach
                </div>

                {{-- Description --}}
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Tentang</h2>
                    <p class="mt-2 whitespace-pre-line leading-relaxed text-gray-600">{{ $destination->description_long }}</p>
                </div>

                {{-- Tags --}}
                @if ($destination->tags->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Tag</h2>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($destination->tags as $tag)
                                <a href="{{ route('explore', ['tags' => [$tag->slug]]) }}" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-700">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Map --}}
                @if ($hasCoords)
                    <div>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Lokasi</h2>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $destination->latitude }},{{ $destination->longitude }}" target="_blank" rel="noopener"
                               class="text-sm font-medium text-emerald-700 hover:underline">Petunjuk arah &rarr;</a>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ $destination->address }}</p>
                        <div id="map" class="mt-3 h-72 w-full overflow-hidden rounded-xl border border-gray-200 z-0"></div>
                    </div>
                @endif

                {{-- Reviews --}}
                <div id="reviews">
                    <h2 class="text-lg font-semibold text-gray-900">Ulasan ({{ $destination->review_count_cache }})</h2>

                    {{-- Write / edit form --}}
                    @auth
                        <div class="mt-3 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                            @if ($myReview && $myReview->status->value === 'pending')
                                <p class="mb-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">Ulasanmu sedang menunggu moderasi admin.</p>
                            @endif
                            <h3 class="font-medium text-gray-900">{{ $myReview ? 'Edit ulasanmu' : 'Tulis ulasan' }}</h3>
                            <form method="POST"
                                  action="{{ $myReview ? route('reviews.update', $myReview) : route('reviews.store', $destination) }}"
                                  class="mt-3 space-y-3" x-data="{ rating: {{ old('rating', $myReview->rating ?? 0) }} }">
                                @csrf
                                @if ($myReview) @method('PATCH') @endif

                                <div class="flex items-center gap-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button" @click="rating = {{ $i }}" class="text-2xl" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'">★</button>
                                    @endfor
                                    <input type="hidden" name="rating" :value="rating">
                                </div>
                                @error('rating') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                                <textarea name="comment" rows="3" placeholder="Bagikan pengalamanmu..."
                                          class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('comment', $myReview->comment ?? '') }}</textarea>
                                @error('comment') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                                <div class="flex items-center gap-3">
                                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">{{ $myReview ? 'Perbarui' : 'Kirim ulasan' }}</button>
                                    @if ($myReview)
                                        <button type="submit" form="delete-review" class="text-sm font-medium text-red-600 hover:underline">Hapus ulasan</button>
                                    @endif
                                </div>
                            </form>
                            @if ($myReview)
                                <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" id="delete-review">
                                    @csrf @method('DELETE')
                                </form>
                            @endif
                        </div>
                    @else
                        <p class="mt-3 text-sm text-gray-500"><a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline">Masuk</a> untuk menulis ulasan.</p>
                    @endauth

                    {{-- Published reviews --}}
                    <div class="mt-5 space-y-4">
                        @forelse ($destination->publishedReviews as $review)
                            <div class="rounded-2xl border border-gray-100 bg-white p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">{{ $review->user->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $review->created_at->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="mt-1 flex text-amber-400">
                                    @for ($i = 1; $i <= 5; $i++)<span>{{ $i <= $review->rating ? '★' : '☆' }}</span>@endfor
                                </div>
                                @if ($review->comment)<p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>@endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada ulasan yang dipublikasikan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sidebar: quick info --}}
            <aside class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-gray-900">Informasi</h3>
                    <dl class="mt-3 space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-400">Jam buka</dt>
                            <dd class="text-gray-700">
                                @if (is_array($destination->opening_hours) && $destination->opening_hours)
                                    @foreach ($destination->opening_hours as $k => $v)
                                        <div>{{ ucfirst(str_replace('_', ' – ', $k)) }}: {{ $v }}</div>
                                    @endforeach
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-400">Harga</dt>
                            <dd class="text-gray-700">{{ $destination->price_info ?? $destination->price_range->label() }}</dd>
                            @php $budgetHint = setting('budget_'.$destination->price_range->value); @endphp
                            @if ($budgetHint)
                                <dd class="text-xs text-gray-400">{{ $destination->price_range->label() }}: {{ $budgetHint }}</dd>
                            @endif
                        </div>
                        <div><dt class="text-gray-400">Alamat</dt><dd class="text-gray-700">{{ $destination->address }}</dd></div>
                        @if ($destination->contact_phone)
                            <div><dt class="text-gray-400">Telepon</dt><dd class="text-gray-700">{{ $destination->contact_phone }}</dd></div>
                        @endif
                        @if ($destination->contact_instagram)
                            <div><dt class="text-gray-400">Instagram</dt><dd><a href="https://instagram.com/{{ ltrim($destination->contact_instagram, '@') }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">{{ $destination->contact_instagram }}</a></dd></div>
                        @endif
                        @if ($destination->contact_website)
                            <div><dt class="text-gray-400">Website</dt><dd><a href="{{ $destination->contact_website }}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline break-all">{{ $destination->contact_website }}</a></dd></div>
                        @endif
                    </dl>
                </div>
            </aside>
        </div>

        {{-- Similar --}}
        @if ($similar->isNotEmpty())
            <div class="mt-14">
                <h2 class="text-xl font-bold text-gray-900">Destinasi Serupa</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $item)
                        <x-destination-card :destination="$item" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($hasCoords)
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const map = L.map('map').setView([{{ $destination->latitude }}, {{ $destination->longitude }}], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap', maxZoom: 19,
                    }).addTo(map);
                    L.marker([{{ $destination->latitude }}, {{ $destination->longitude }}]).addTo(map)
                        .bindPopup(@json($destination->name));
                });
            </script>
        @endpush
    @endif
</x-public-layout>
