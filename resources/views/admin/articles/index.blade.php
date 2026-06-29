<x-admin-layout title="Artikel" heading="Kelola Artikel">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul..."
                   class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select name="status" class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Filter</button>
        </form>
        <a href="{{ route('admin.articles.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Tulis Artikel</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Penulis</th>
                    <th class="px-4 py-3">Destinasi</th>
                    <th class="px-4 py-3">Terbit</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($articles as $article)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $article->title }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.articles.toggle-publish', $article) }}">
                                @csrf
                                <button class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $article->status->value === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $article->status->label() }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $article->author?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $article->destinations_count }} tautan</td>
                        <td class="px-4 py-3 text-gray-500">{{ $article->published_at?->translatedFormat('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if ($article->status->value === 'published')
                                    <a href="{{ route('blog.show', $article) }}" target="_blank" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Lihat</a>
                                @endif
                                <a href="{{ route('admin.articles.edit', $article) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Hapus artikel ini?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada artikel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $articles->links() }}</div>
</x-admin-layout>
