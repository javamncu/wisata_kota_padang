@props(['destination', 'distance' => null])

@php $cover = $destination->coverUrl(); @endphp

<a href="{{ route('destinations.show', $destination) }}"
   class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm transition hover:shadow-md">
    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-emerald-400 to-teal-600">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $destination->name }}"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center p-4 text-center">
                <span class="text-white/90 font-semibold text-lg drop-shadow">{{ $destination->name }}</span>
            </div>
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-emerald-700 shadow-sm">
            {{ $destination->category->name }}
        </span>
        @if ($distance)
            <span class="absolute right-3 top-3 rounded-full bg-gray-900/80 px-2.5 py-1 text-xs font-semibold text-white shadow-sm">
                📍 {{ $distance }}
            </span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="font-semibold text-gray-900 group-hover:text-emerald-700">{{ $destination->name }}</h3>
        @if ($destination->city)
            <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $destination->city->label() }}
            </p>
        @endif
        <p class="mt-1 line-clamp-2 text-sm text-gray-500">{{ $destination->description_short }}</p>
        <div class="mt-3 flex items-center justify-between">
            <x-star-rating :rating="$destination->rating_cache" :count="$destination->review_count_cache" />
            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                {{ $destination->price_range->label() }}
            </span>
        </div>
    </div>
</a>
