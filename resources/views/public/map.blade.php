<x-public-layout title="Peta Interaktif — Wisata Kota Padang">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Peta Interaktif</h1>
                <p class="mt-1 text-gray-500">{{ $markers->count() }} destinasi tersebar di Kota Padang.</p>
            </div>
            <div class="flex gap-3">
                <select id="filter-category" class="rounded-xl border-gray-200 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select id="filter-zone" class="rounded-xl border-gray-200 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua zona</option>
                    @foreach ($zones as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="map" class="h-[70vh] w-full overflow-hidden rounded-2xl border border-gray-200 z-0"></div>
    </div>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            const markersData = @json($markers);

            document.addEventListener('DOMContentLoaded', function () {
                const map = L.map('map').setView([-0.9495, 100.3543], 12);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap', maxZoom: 19,
                }).addTo(map);

                const layer = L.layerGroup().addTo(map);

                function render() {
                    const cat = document.getElementById('filter-category').value;
                    const zone = document.getElementById('filter-zone').value;
                    layer.clearLayers();

                    markersData
                        .filter(m => (!cat || m.categorySlug === cat) && (!zone || m.zone === zone))
                        .forEach(m => {
                            const rating = m.rating ? `⭐ ${m.rating.toFixed(1)}` : 'Belum ada ulasan';
                            L.marker([m.lat, m.lng]).addTo(layer).bindPopup(
                                `<div style="min-width:160px">
                                    <strong>${m.name}</strong><br>
                                    <span style="color:#6b7280;font-size:12px">${m.category} · ${m.zoneLabel}</span><br>
                                    <span style="font-size:12px">${rating}</span><br>
                                    <a href="${m.url}" style="color:#047857;font-weight:600;font-size:13px">Lihat detail →</a>
                                </div>`
                            );
                        });
                }

                document.getElementById('filter-category').addEventListener('change', render);
                document.getElementById('filter-zone').addEventListener('change', render);
                render();
            });
        </script>
    @endpush
</x-public-layout>
