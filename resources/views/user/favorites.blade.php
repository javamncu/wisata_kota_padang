<x-public-layout title="Favorit Saya — Wisata Kota Padang">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900">Favorit Saya</h1>
        <p class="mt-1 text-gray-500">Destinasi yang kamu simpan.</p>

        @if ($favorites->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center">
                <p class="text-gray-500">Belum ada favorit. Mulai jelajahi destinasi!</p>
                <a href="{{ route('explore') }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:underline">Jelajahi destinasi &rarr;</a>
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($favorites as $destination)
                    <div class="relative">
                        <x-destination-card :destination="$destination" />
                        <form method="POST" action="{{ route('favorites.toggle', $destination) }}" class="absolute right-3 top-3">
                            @csrf
                            <button title="Hapus dari favorit"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-rose-600 shadow hover:bg-white">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-8">{{ $favorites->links() }}</div>
        @endif
    </div>
</x-public-layout>
