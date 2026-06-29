@php $metaDesc = $article->meta_description ?: $article->excerpt; @endphp

<x-public-layout :title="($article->meta_title ?: $article->title).' — '.setting('site_name')">
    @push('head')
        @if ($metaDesc)<meta name="description" content="{{ $metaDesc }}">@endif
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $article->meta_title ?: $article->title }}">
        @if ($metaDesc)<meta property="og:description" content="{{ $metaDesc }}">@endif
        @if ($article->coverUrl())<meta property="og:image" content="{{ url($article->coverUrl()) }}">@endif
        <meta property="og:url" content="{{ url()->current() }}">
        <style>
            .article-body { color: #374151; line-height: 1.75; }
            .article-body h1 { font-size: 1.5rem; font-weight: 700; margin: 1rem 0 .5rem; }
            .article-body p { margin: .75rem 0; }
            .article-body a { color: #047857; text-decoration: underline; }
            .article-body ul { list-style: disc; padding-left: 1.5rem; margin: .75rem 0; }
            .article-body ol { list-style: decimal; padding-left: 1.5rem; margin: .75rem 0; }
            .article-body img { border-radius: .75rem; margin: 1rem 0; }
        </style>
    @endpush

    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-sm text-gray-400 mb-3">
            <a href="{{ route('blog.index') }}" class="hover:text-emerald-700">Blog</a> / <span class="text-gray-600">{{ $article->title }}</span>
        </nav>

        @if (! $article->isPublished())
            <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-800">Pratinjau draft (hanya admin yang bisa melihat ini).</div>
        @endif

        <h1 class="text-3xl font-bold text-gray-900">{{ $article->title }}</h1>
        <p class="mt-2 text-sm text-gray-400">
            {{ $article->published_at?->translatedFormat('d F Y') }}
            @if ($article->author) · oleh {{ $article->author->name }} @endif
        </p>

        @if ($article->coverUrl())
            <img src="{{ $article->coverUrl() }}" alt="{{ $article->title }}" class="mt-6 aspect-[16/9] w-full rounded-2xl object-cover">
        @endif

        <div class="article-body mt-6">{!! $article->body !!}</div>

        {{-- Related destinations --}}
        @if ($article->destinations->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl font-bold text-gray-900">Destinasi terkait</h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($article->destinations as $destination)
                        <x-destination-card :destination="$destination" />
                    @endforeach
                </div>
            </div>
        @endif
    </article>

    {{-- Other articles --}}
    @if ($others->isNotEmpty())
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <h2 class="text-xl font-bold text-gray-900">Artikel lainnya</h2>
            <div class="mt-4 space-y-3">
                @foreach ($others as $other)
                    <a href="{{ route('blog.show', $other) }}" class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-3 shadow-sm hover:shadow-md">
                        <div class="h-16 w-24 flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600">
                            @if ($other->coverUrl())<img src="{{ $other->coverUrl() }}" class="h-full w-full object-cover">@endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $other->title }}</p>
                            <p class="text-xs text-gray-400">{{ $other->published_at?->translatedFormat('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</x-public-layout>
