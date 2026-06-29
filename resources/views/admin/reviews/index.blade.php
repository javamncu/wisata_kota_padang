<x-admin-layout title="Moderasi Review" heading="Moderasi Review">
    {{-- Status tabs --}}
    <div class="mb-4 flex gap-2">
        @php $tabs = ['pending' => 'Pending', 'published' => 'Dipublikasikan', '' => 'Semua']; @endphp
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.reviews.index', $value !== '' ? ['status' => $value] : []) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium {{ $currentStatus === $value || ($value === '' && ! in_array($currentStatus, ['pending', 'published'], true)) ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
                @if ($value === 'pending' && $pendingCount > 0)
                    <span class="ml-1 rounded-full bg-amber-400 px-1.5 text-xs text-white">{{ $pendingCount }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($reviews as $review)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900">{{ $review->user->name }}</span>
                            <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            @if ($review->status->value === 'pending')
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">Pending</span>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Published</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400">
                            untuk <a href="{{ route('destinations.show', $review->destination) }}" target="_blank" class="text-emerald-700 hover:underline">{{ $review->destination->name }}</a>
                            · {{ $review->created_at->translatedFormat('d M Y') }}
                        </p>
                        @if ($review->comment)
                            <p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if ($review->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                @csrf @method('PATCH')
                                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">Publikasikan</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Hapus review ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500">Tidak ada review.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
</x-admin-layout>
