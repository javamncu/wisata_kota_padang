<x-public-layout title="Review Saya — Wisata Kota Padang">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900">Review Saya</h1>
        <p class="mt-1 text-gray-500">Ulasan yang kamu tulis.</p>

        @if ($reviews->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                <p class="text-gray-500">Kamu belum menulis ulasan.</p>
                <a href="{{ route('explore') }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">Jelajahi destinasi &rarr;</a>
            </div>
        @else
            <div class="mt-6 space-y-3">
                @foreach ($reviews as $review)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('destinations.show', $review->destination) }}" class="font-medium text-gray-900 hover:text-emerald-700">{{ $review->destination->name }}</a>
                                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                    @if ($review->status->value === 'pending')
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">Menunggu moderasi</span>
                                    @else
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Tayang</span>
                                    @endif
                                </div>
                                @if ($review->comment)
                                    <p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">{{ $review->updated_at->translatedFormat('d M Y') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('destinations.show', $review->destination) }}#reviews" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Hapus ulasan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $reviews->links() }}</div>
        @endif
    </div>
</x-public-layout>
