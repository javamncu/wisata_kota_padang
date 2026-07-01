@php $editing = $category->exists; @endphp

<x-admin-layout :title="$editing ? 'Edit Kategori' : 'Tambah Kategori'" :heading="$editing ? 'Edit Kategori' : 'Tambah Kategori'">
    <div class="max-w-2xl">
        <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}"
                       class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $category->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Icon (kata kunci)</label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" placeholder="mis. mountain, mosque, utensils"
                       class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Opsi: mountain, landmark, mosque, utensils, shopping-bag, ferris-wheel, mall</p>
                @error('icon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="rounded text-emerald-600 focus:ring-emerald-500">
                Aktif
            </label>

            <div class="flex gap-3 pt-2">
                <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
