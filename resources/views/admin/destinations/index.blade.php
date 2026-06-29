<x-admin-layout title="Destinasi" heading="Kelola Destinasi">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama..."
                   class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <select name="category" class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border-gray-200 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Filter</button>
        </form>
        <a href="{{ route('admin.destinations.create') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">+ Tambah Destinasi</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Rating</th>
                    <th class="px-4 py-3">Review</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($destinations as $d)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $d->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $d->category->name }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.destinations.toggle-status', $d) }}">
                                @csrf
                                <button class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $d->status->value === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $d->status->label() }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $d->rating_cache ? number_format($d->rating_cache, 1) : '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $d->reviews_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('destinations.show', $d) }}" target="_blank" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Lihat</a>
                                <a href="{{ route('admin.destinations.edit', $d) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('admin.destinations.destroy', $d) }}" onsubmit="return confirm('Hapus destinasi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada destinasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $destinations->links() }}</div>
</x-admin-layout>
