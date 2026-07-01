@php
    $fmt = function ($m) {
        if ($m < 1000) {
            return round($m).' m';
        }
        $km = $m / 1000;
        return number_format($km, $km >= 100 ? 0 : 1, ',', '.').' km';
    };

    $markers = $results->map(fn ($d) => [
        'name' => $d->name,
        'lat' => (float) $d->latitude,
        'lng' => (float) $d->longitude,
        'category' => $d->category->name,
        'distance' => $fmt($d->distance_m),
        'url' => route('destinations.show', $d),
    ])->values();
@endphp

<x-public-layout title="Wisata di Sekitarku — Wisata Kota Padang">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900">Wisata di Sekitarku</h1>
        <p class="mt-1 text-gray-500">Temukan destinasi terdekat dari lokasimu sekarang.</p>

        @unless ($hasLocation)
            {{-- State A: minta izin lokasi --}}
            <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl">📍</div>
                <h2 class="mt-4 text-lg font-semibold text-gray-900">Izinkan akses lokasi</h2>
                <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">
                    Kami memakai lokasimu hanya untuk mengurutkan destinasi terdekat — <strong>tidak disimpan</strong>.
                </p>
                <button type="button" onclick="requestLocation(this)"
                        class="mt-5 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                    Gunakan lokasi saya
                </button>
                <p id="geo-error" class="mt-3 hidden text-sm text-red-600">
                    Tidak bisa mengakses lokasi. Pilih area secara manual di bawah.
                </p>

                <div class="mt-8 border-t border-gray-100 pt-6">
                    <p class="text-sm font-medium text-gray-700">Atau pilih area secara manual:</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        @foreach ($zones as $value => $label)
                            <a href="{{ route('explore', ['zone' => [$value]]) }}" class="chip">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- State B: hasil terdekat --}}
            <form method="GET" action="{{ route('nearby.index') }}" id="filter-form" class="mt-6">
                <input type="hidden" name="lat" value="{{ $lat }}">
                <input type="hidden" name="lng" value="{{ $lng }}">

                <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
                     x-data="{ view: 'grid' }">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-700">Radius:</span>
                        @foreach ($radii as $r)
                            <button type="submit" name="radius" value="{{ $r }}"
                                    class="rounded-full px-3 py-1 text-sm font-medium {{ $radius === $r ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $r }} km
                            </button>
                        @endforeach
                        <button type="submit" name="radius" value="0"
                                class="rounded-full px-3 py-1 text-sm font-medium {{ $radius === 0 ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Semua
                        </button>
                    </div>

                    <select name="category" onchange="document.getElementById('filter-form').submit()"
                            class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->slug }}" @selected($category === $cat->slug)>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" onclick="requestLocation(this)" class="text-sm text-gray-500 hover:text-emerald-700">↻ Perbarui lokasi</button>
                        <div class="flex rounded-lg border border-gray-200 p-0.5">
                            <button type="button" @click="view = 'grid'" :class="view === 'grid' ? 'bg-emerald-600 text-white' : 'text-gray-600'" class="rounded-md px-3 py-1 text-sm font-medium">Grid</button>
                            <button type="button" @click="view = 'map'" :class="view === 'map' ? 'bg-emerald-600 text-white' : 'text-gray-600'" class="rounded-md px-3 py-1 text-sm font-medium">Peta</button>
                        </div>
                    </div>

                    {{-- Results --}}
                    <div class="w-full">
                        <p class="mb-4 mt-2 text-sm text-gray-500">
                            @if ($radius > 0)
                                {{ $results->count() }} destinasi dalam radius {{ $radius }} km
                            @else
                                {{ $results->count() }} destinasi — diurutkan dari yang terdekat dengan posisimu
                            @endif
                        </p>

                        @if ($results->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-500">
                                Tidak ada destinasi dalam {{ $radius }} km. Coba perbesar radius atau pilih <strong>Semua</strong>.
                            </div>
                        @else
                            {{-- Grid --}}
                            <div x-show="view === 'grid'" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                @foreach ($results as $d)
                                    <x-destination-card :destination="$d" :distance="$fmt($d->distance_m)" />
                                @endforeach
                            </div>
                            {{-- Map --}}
                            <div x-show="view === 'map'" x-cloak>
                                <div id="map" class="h-[65vh] w-full overflow-hidden rounded-xl border border-gray-200 z-0"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        @endunless
    </div>

    @push('scripts')
        <script>
            function requestLocation(btn) {
                const errEl = document.getElementById('geo-error');
                if (!navigator.geolocation) { errEl && errEl.classList.remove('hidden'); return; }
                if (btn) { btn.disabled = true; btn.textContent = 'Mencari lokasi…'; }
                navigator.geolocation.getCurrentPosition(
                    (p) => {
                        const u = new URL(window.location);
                        u.searchParams.set('lat', p.coords.latitude.toFixed(6));
                        u.searchParams.set('lng', p.coords.longitude.toFixed(6));
                        window.location = u;
                    },
                    () => {
                        if (btn) { btn.disabled = false; btn.textContent = 'Gunakan lokasi saya'; }
                        errEl && errEl.classList.remove('hidden');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        </script>

        @if ($hasLocation && $results->isNotEmpty())
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                const userLat = {{ $lat }}, userLng = {{ $lng }};
                const nearby = @json($markers);

                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.getElementById('map');
                    if (!el) return;
                    const map = L.map('map').setView([userLat, userLng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);

                    L.circleMarker([userLat, userLng], { radius: 8, color: '#2563eb', fillColor: '#3b82f6', fillOpacity: 0.9 })
                        .addTo(map).bindPopup('Lokasi kamu');

                    const points = [[userLat, userLng]];
                    nearby.forEach(m => {
                        L.marker([m.lat, m.lng]).addTo(map).bindPopup(
                            `<strong>${m.name}</strong><br><span style="color:#6b7280;font-size:12px">${m.category} · ${m.distance}</span><br><a href="${m.url}" style="color:#047857;font-weight:600;font-size:13px">Lihat detail →</a>`
                        );
                        points.push([m.lat, m.lng]);
                    });

                    // Auto-zoom agar posisi user + destinasi tercakup.
                    if (points.length > 1) {
                        map.fitBounds(points, { padding: [40, 40], maxZoom: 15 });
                    }
                });
            </script>
        @endif
    @endpush

    @if ($hasLocation)
        @push('head')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />@endpush
    @endif
</x-public-layout>
