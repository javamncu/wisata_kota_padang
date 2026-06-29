@php
    $icons = [
        'mountain' => '🏞️', 'landmark' => '🏛️', 'mosque' => '🕌',
        'utensils' => '🍜', 'shopping-bag' => '🛍️', 'ferris-wheel' => '🎡',
    ];
@endphp

<x-public-layout title="Wisata Kota Padang — Temukan destinasi terbaik">
    {{-- Hero --}}
    <section class="relative overflow-hidden text-white">
        <img src="{{ asset('images/hero.png') }}" alt="Pemandangan Kota Padang"
             class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-bold leading-tight">{{ setting('hero_title') }}</h1>
                <p class="mt-4 text-lg text-emerald-50">{{ setting('hero_subtitle') }}</p>

                <form action="{{ route('explore') }}" method="GET" class="mt-8 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <input type="search" name="q" placeholder="Cari pantai, masjid, kuliner..."
                               class="w-full rounded-xl border-0 py-3.5 pl-12 pr-4 text-gray-900 shadow-sm focus:ring-2 focus:ring-white">
                    </div>
                    <button type="submit" class="rounded-xl bg-gray-900 px-6 py-3.5 font-semibold text-white hover:bg-gray-800">
                        Cari
                    </button>
                </form>

                <div class="mt-5 flex flex-wrap items-center gap-3 text-sm">
                    <span class="text-emerald-100">Belum tahu mau ke mana?</span>
                    <a href="{{ route('quiz.index') }}" class="inline-flex items-center gap-1 rounded-full bg-white/15 px-4 py-1.5 font-medium text-white ring-1 ring-white/30 hover:bg-white/25">
                        ✨ Kuis preferensi
                    </a>
                    <a href="{{ route('concierge.index') }}" class="inline-flex items-center gap-1 rounded-full bg-white px-4 py-1.5 font-semibold text-emerald-700 hover:bg-emerald-50">
                        💬 Tanya AI Concierge
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Category shortcuts --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="flex flex-col items-center gap-2 rounded-xl bg-white border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-emerald-200 transition text-center">
                    <span class="text-3xl">{{ $icons[$category->icon] ?? '📍' }}</span>
                    <span class="text-sm font-medium text-gray-700 leading-tight">{{ $category->name }}</span>
                    <span class="text-xs text-gray-400">{{ $category->destinations_count }} tempat</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Destinasi Populer</h2>
                <p class="mt-1 text-sm text-gray-500">Tempat yang paling banyak diulas pengunjung.</p>
            </div>
            <a href="{{ route('explore') }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat semua &rarr;</a>
        </div>

        @if ($featured->isEmpty())
            <p class="mt-8 text-gray-500">Belum ada destinasi.</p>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $destination)
                    <x-destination-card :destination="$destination" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Quiz CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        <div class="rounded-3xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-12 text-white md:flex md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold">Bingung pilih destinasi?</h2>
                <p class="mt-2 text-amber-50">Jawab beberapa pertanyaan singkat, kami carikan yang paling cocok untukmu.</p>
            </div>
            <a href="{{ route('quiz.index') }}" class="mt-6 md:mt-0 inline-block rounded-xl bg-white px-6 py-3 font-semibold text-orange-600 hover:bg-amber-50">
                Mulai Kuis Preferensi
            </a>
        </div>
    </section>
</x-public-layout>
