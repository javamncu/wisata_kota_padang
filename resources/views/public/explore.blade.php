@php
    use App\Enums\TagType;

    $formAction = $lockedCategory
        ? route('categories.show', $lockedCategory)
        : route('explore');

    $query = request()->query();

    // Build a URL that removes one filter value (or a whole single facet).
    $remove = function (string $facet, $value = null) use ($query, $formAction) {
        $q = $query;
        unset($q['page']);
        if ($value === null) {
            unset($q[$facet]);
        } else {
            $q[$facet] = array_values(array_diff((array) ($q[$facet] ?? []), [$value]));
            if (empty($q[$facet])) {
                unset($q[$facet]);
            }
        }
        return $q ? $formAction.'?'.http_build_query($q) : $formAction;
    };

    // slug => name for tag chips
    $tagNames = $tagsByType->flatten()->mapWithKeys(fn ($t) => [$t->slug => $t->name]);
@endphp

<x-public-layout :title="($lockedCategory?->name ?? 'Explore').' — Wisata Kota Padang'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-6">
            @if ($lockedCategory)
                <nav class="text-sm text-gray-400 mb-1"><a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a> / Kategori</nav>
                <h1 class="text-2xl font-bold text-gray-900">{{ $lockedCategory->name }}</h1>
                @if ($lockedCategory->description)
                    <p class="mt-1 text-gray-500">{{ $lockedCategory->description }}</p>
                @endif
            @else
                <h1 class="text-2xl font-bold text-gray-900">Explore Destinasi</h1>
                <p class="mt-1 text-gray-500">Cari dan saring destinasi sesuai keinginanmu.</p>
            @endif
        </div>

        <form method="GET" action="{{ $formAction }}" x-data="{ filters: false }">
            {{-- Top bar: search + sort --}}
            <div class="flex flex-col sm:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    <input type="search" name="q" value="{{ $criteria->keyword }}" placeholder="Cari destinasi..."
                           class="w-full rounded-xl border-gray-200 py-3 pl-12 pr-4 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <button type="button" @click="filters = !filters"
                        class="lg:hidden rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700">
                    Filter
                </button>
                <select name="sort" onchange="this.form.submit()"
                        class="rounded-xl border-gray-200 py-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="populer" @selected($criteria->sort === 'populer')>Paling Populer</option>
                    <option value="rating" @selected($criteria->sort === 'rating')>Rating Tertinggi</option>
                    <option value="az" @selected($criteria->sort === 'az')>Nama A–Z</option>
                </select>
                <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Terapkan</button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
                {{-- Filters --}}
                <aside :class="filters ? 'block' : 'hidden'" class="lg:block">
                    <div class="space-y-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        @unless ($lockedCategory)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-2">Kategori</h3>
                                <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
                                    <input type="radio" name="category" value="" @checked($criteria->category === null) class="text-emerald-600 focus:ring-emerald-500">
                                    Semua
                                </label>
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
                                        <input type="radio" name="category" value="{{ $cat->slug }}" @checked($criteria->category === $cat->slug) class="text-emerald-600 focus:ring-emerald-500">
                                        {{ $cat->name }}
                                    </label>
                                @endforeach
                            </div>
                        @endunless

                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Kota</h3>
                            <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
                                <input type="radio" name="city" value="" @checked($criteria->city === null) class="text-emerald-600 focus:ring-emerald-500">
                                Semua kota
                            </label>
                            @foreach ($enums['city'] as $value => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
                                    <input type="radio" name="city" value="{{ $value }}" @checked($criteria->city === $value) class="text-emerald-600 focus:ring-emerald-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                        <x-filter-group title="Zona" name="zone" :options="$enums['zone']" :selected="$criteria->zones" />
                        <x-filter-group title="Budget" name="price" :options="$enums['price']" :selected="$criteria->priceRanges" />
                        <x-filter-group title="Indoor / Outdoor" name="io" :options="$enums['io']" :selected="$criteria->indoorOutdoor" />
                        <x-filter-group title="Durasi" name="duration" :options="$enums['duration']" :selected="$criteria->durations" />
                        <x-filter-group title="Cocok untuk" name="cocok" :options="$enums['cocok']" :selected="$criteria->cocokUntuk" />
                        <x-filter-group title="Waktu ideal" name="waktu" :options="$enums['waktu']" :selected="$criteria->waktuIdeal" />

                        @foreach (['suasana' => 'Suasana', 'aktivitas' => 'Aktivitas', 'fasilitas' => 'Fasilitas'] as $type => $label)
                            @php $group = $tagsByType->get($type); @endphp
                            @if ($group)
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 mb-2">{{ $label }}</h3>
                                    @foreach ($group as $tag)
                                        <label class="flex items-center gap-2 text-sm text-gray-600 py-0.5">
                                            <input type="checkbox" name="tags[]" value="{{ $tag->slug }}" @checked(in_array($tag->slug, $criteria->tags, true)) class="rounded text-emerald-600 focus:ring-emerald-500">
                                            {{ $tag->name }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </aside>

                {{-- Results --}}
                <div>
                    {{-- Active chips --}}
                    @if ($criteria->hasAnyFilter())
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            @if ($criteria->keyword)
                                <a href="{{ $remove('q') }}" class="chip">“{{ $criteria->keyword }}” &times;</a>
                            @endif
                            @if ($criteria->category && ! $lockedCategory)
                                <a href="{{ $remove('category') }}" class="chip">{{ $categories->firstWhere('slug', $criteria->category)?->name }} &times;</a>
                            @endif
                            @if ($criteria->city)
                                <a href="{{ $remove('city') }}" class="chip">{{ $enums['city'][$criteria->city] }} &times;</a>
                            @endif
                            @foreach ($criteria->zones as $v) <a href="{{ $remove('zone', $v) }}" class="chip">{{ $enums['zone'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->priceRanges as $v) <a href="{{ $remove('price', $v) }}" class="chip">{{ $enums['price'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->indoorOutdoor as $v) <a href="{{ $remove('io', $v) }}" class="chip">{{ $enums['io'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->durations as $v) <a href="{{ $remove('duration', $v) }}" class="chip">{{ $enums['duration'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->cocokUntuk as $v) <a href="{{ $remove('cocok', $v) }}" class="chip">{{ $enums['cocok'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->waktuIdeal as $v) <a href="{{ $remove('waktu', $v) }}" class="chip">{{ $enums['waktu'][$v] }} &times;</a> @endforeach
                            @foreach ($criteria->tags as $v) <a href="{{ $remove('tags', $v) }}" class="chip">{{ $tagNames[$v] ?? $v }} &times;</a> @endforeach
                            <a href="{{ $formAction }}" class="text-xs font-medium text-red-600 hover:underline ml-1">Reset semua</a>
                        </div>
                    @endif

                    <p class="mb-4 text-sm text-gray-500">{{ $destinations->total() }} destinasi ditemukan</p>

                    @if ($destinations->isEmpty())
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                            <p class="text-gray-500">Tidak ada destinasi yang cocok dengan filter ini.</p>
                            <a href="{{ $formAction }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">Reset filter</a>
                        </div>
                    @else
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($destinations as $destination)
                                <x-destination-card :destination="$destination" />
                            @endforeach
                        </div>
                        <div class="mt-8">{{ $destinations->links() }}</div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-public-layout>
