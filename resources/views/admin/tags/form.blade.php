@php
    use App\Enums\TagType;
    $editing = $tag->exists;
@endphp

<x-admin-layout :title="$editing ? 'Edit Tag' : 'Tambah Tag'" :heading="$editing ? 'Edit Tag' : 'Tambah Tag'">
    <div class="max-w-xl">
        <form method="POST" action="{{ $editing ? route('admin.tags.update', $tag) : route('admin.tags.store') }}"
              class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $tag->name) }}"
                       class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tipe</label>
                <select name="type" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach (TagType::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $tag->type?->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
                <a href="{{ route('admin.tags.index') }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
