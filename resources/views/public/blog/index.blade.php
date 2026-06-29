<x-public-layout title="Blog — Wisata Kota Padang">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900">Blog & Panduan</h1>
        <p class="mt-1 text-gray-500">Tips, cerita, dan panduan menjelajahi Kota Padang.</p>

        @if ($articles->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center text-gray-500">
                Belum ada artikel.
            </div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <a href="{{ route('blog.show', $article) }}"
                       class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                        <div class="aspect-[16/9] overflow-hidden bg-gradient-to-br from-emerald-400 to-teal-600">
                            @if ($article->coverUrl())
                                <img src="{{ $article->coverUrl() }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center p-4 text-center">
                                    <span class="font-semibold text-white/90">{{ $article->title }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs text-gray-400">{{ $article->published_at?->translatedFormat('d M Y') }}</p>
                            <h2 class="mt-1 font-semibold text-gray-900 group-hover:text-emerald-700">{{ $article->title }}</h2>
                            <p class="mt-2 line-clamp-3 text-sm text-gray-500">{{ $article->excerpt }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $articles->links() }}</div>
        @endif
    </div>
</x-public-layout>
