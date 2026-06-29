<x-public-layout title="Rekomendasi untukmu — Wisata Kota Padang">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Rekomendasi untukmu</h1>
                <p class="mt-1 text-gray-500">Diurutkan dari yang paling cocok dengan preferensimu.</p>
            </div>
            <a href="{{ route('quiz.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Ulangi kuis</a>
        </div>

        @if ($recommendations->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                <p class="text-gray-500">Belum ada destinasi yang cocok dengan preferensi itu.</p>
                <a href="{{ route('explore') }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">Jelajahi semua destinasi &rarr;</a>
            </div>
        @else
            <div class="mt-8 space-y-4">
                @foreach ($recommendations as $i => $row)
                    @php $d = $row['destination']; $cover = $d->coverUrl(); @endphp
                    <a href="{{ route('destinations.show', $d) }}"
                       class="group flex flex-col sm:flex-row overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                        <div class="relative h-40 sm:h-auto sm:w-56 flex-shrink-0 bg-gradient-to-br from-emerald-400 to-teal-600">
                            @if ($cover)
                                <img src="{{ $cover }}" alt="{{ $d->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center p-3 text-center">
                                    <span class="font-semibold text-white/90">{{ $d->name }}</span>
                                </div>
                            @endif
                            <span class="absolute left-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm font-bold text-emerald-700 shadow">{{ $i + 1 }}</span>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-emerald-700">{{ $d->category->name }}</span>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Skor {{ $row['score'] }}</span>
                            </div>
                            <h3 class="mt-1 text-lg font-semibold text-gray-900 group-hover:text-emerald-700">{{ $d->name }}</h3>
                            <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ $d->description_short }}</p>

                            @if (! empty($row['reasons']))
                                <div class="mt-3">
                                    <p class="text-xs font-medium text-gray-400">Kenapa direkomendasikan:</p>
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($row['reasons'] as $reason)
                                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs text-emerald-700 ring-1 ring-emerald-100">✓ {{ $reason }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-public-layout>
