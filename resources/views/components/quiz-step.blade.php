@props(['index', 'total', 'title', 'subtitle' => null])

<div x-show="step === {{ $index }}" x-cloak class="space-y-6">
    <div>
        <div class="text-sm font-medium text-emerald-600">Langkah {{ $index }} / {{ $total }}</div>
        <h2 class="mt-1 text-2xl font-bold text-gray-900">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>

    <div>{{ $slot }}</div>

    <div class="flex items-center pt-2">
        <button type="button" @click="step--" x-show="step > 1"
                class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
        </button>
        <div class="ml-auto">
            @if ($index < $total)
                <button type="button" @click="step++"
                        class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Lanjut
                </button>
            @else
                <button type="submit"
                        class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    Lihat Rekomendasi ✨
                </button>
            @endif
        </div>
    </div>
</div>
