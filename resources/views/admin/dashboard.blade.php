<x-admin-layout title="Dashboard" heading="Dashboard">
    @php
        $cards = [
            ['label' => 'Total Destinasi', 'value' => $stats['destinations'], 'sub' => $stats['active'].' aktif · '.$stats['draft'].' draft', 'color' => 'emerald'],
            ['label' => 'Kategori', 'value' => $stats['categories'], 'sub' => 'kategori', 'color' => 'sky'],
            ['label' => 'Tag', 'value' => $stats['tags'], 'sub' => 'tag', 'color' => 'violet'],
            ['label' => 'User', 'value' => $stats['users'], 'sub' => 'terdaftar', 'color' => 'amber'],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Pending reviews callout --}}
    @if ($stats['pendingReviews'] > 0)
        <div class="mt-5 flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <div>
                <p class="font-semibold text-amber-800">{{ $stats['pendingReviews'] }} review menunggu moderasi</p>
                <p class="text-sm text-amber-700">Tinjau dan publikasikan agar tampil di halaman destinasi.</p>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Moderasi &rarr;</a>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Category breakdown --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Sebaran per Kategori</h2>
            <div class="mt-4 space-y-3">
                @php $max = max(1, $byCategory->max('destinations_count')); @endphp
                @foreach ($byCategory as $cat)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700">{{ $cat->name }}</span>
                            <span class="font-medium text-gray-900">{{ $cat->destinations_count }}</span>
                        </div>
                        <div class="mt-1 h-2 w-full rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ ($cat->destinations_count / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Latest pending reviews --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Review Terbaru (Pending)</h2>
            <div class="mt-4 space-y-3">
                @forelse ($latestReviews as $review)
                    <div class="flex items-start justify-between gap-3 border-b border-gray-50 pb-3 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $review->user->name }} <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}</span></p>
                            <p class="text-xs text-gray-500">{{ $review->destination->name }}</p>
                        </div>
                        <a href="{{ route('admin.reviews.index') }}" class="text-xs font-medium text-emerald-700 hover:underline">Tinjau</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada review pending. 🎉</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.destinations.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
            + Tambah Destinasi
        </a>
    </div>
</x-admin-layout>
