@props(['rating' => null, 'count' => 0])

@php
    $value = (float) ($rating ?? 0);
    $rounded = round($value * 2) / 2; // nearest half
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
    <div class="flex text-amber-400">
        @for ($i = 1; $i <= 5; $i++)
            @if ($rounded >= $i)
                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.36 4.18a1 1 0 00.95.69h4.4c.97 0 1.37 1.24.59 1.81l-3.56 2.59a1 1 0 00-.36 1.12l1.36 4.18c.3.92-.75 1.69-1.54 1.12l-3.56-2.59a1 1 0 00-1.18 0l-3.56 2.59c-.79.57-1.84-.2-1.54-1.12l1.36-4.18a1 1 0 00-.36-1.12L1.4 9.6c-.78-.57-.38-1.81.59-1.81h4.4a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @elseif ($rounded >= $i - 0.5)
                <svg class="h-4 w-4" viewBox="0 0 20 20"><defs><linearGradient id="half{{ $i }}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#e5e7eb"/></linearGradient></defs><path fill="url(#half{{ $i }})" d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.36 4.18a1 1 0 00.95.69h4.4c.97 0 1.37 1.24.59 1.81l-3.56 2.59a1 1 0 00-.36 1.12l1.36 4.18c.3.92-.75 1.69-1.54 1.12l-3.56-2.59a1 1 0 00-1.18 0l-3.56 2.59c-.79.57-1.84-.2-1.54-1.12l1.36-4.18a1 1 0 00-.36-1.12L1.4 9.6c-.78-.57-.38-1.81.59-1.81h4.4a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @else
                <svg class="h-4 w-4 fill-current text-gray-200" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.36 4.18a1 1 0 00.95.69h4.4c.97 0 1.37 1.24.59 1.81l-3.56 2.59a1 1 0 00-.36 1.12l1.36 4.18c.3.92-.75 1.69-1.54 1.12l-3.56-2.59a1 1 0 00-1.18 0l-3.56 2.59c-.79.57-1.84-.2-1.54-1.12l1.36-4.18a1 1 0 00-.36-1.12L1.4 9.6c-.78-.57-.38-1.81.59-1.81h4.4a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @endif
        @endfor
    </div>
    @if ($value > 0)
        <span class="text-xs font-medium text-gray-600">{{ number_format($value, 1) }}</span>
        <span class="text-xs text-gray-400">({{ $count }})</span>
    @else
        <span class="text-xs text-gray-400">Belum ada ulasan</span>
    @endif
</div>
