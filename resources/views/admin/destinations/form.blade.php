@php
    $editing = $destination->exists;
    $cocokValues = $destination->cocok_untuk ? $destination->cocok_untuk->map->value->all() : [];
    $waktuValues = $destination->waktu_ideal ? $destination->waktu_ideal->map->value->all() : [];
    $selectedCocok = (array) old('cocok', $cocokValues);
    $selectedWaktu = (array) old('waktu', $waktuValues);
    $selectedTags = (array) old('tags', $selectedTagIds);
@endphp

<x-admin-layout :title="$editing ? 'Edit Destinasi' : 'Tambah Destinasi'" :heading="$editing ? 'Edit Destinasi' : 'Tambah Destinasi'">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}"
          class="max-w-4xl space-y-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                Ada {{ $errors->count() }} kesalahan input. Periksa kembali form.
            </div>
        @endif

        {{-- Basic --}}
        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Informasi Dasar</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="category_id" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((int) old('category_id', $destination->category_id) === $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach ($enums['status'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $destination->status?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $destination->name) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
                <textarea name="description_short" rows="2" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description_short', $destination->description_short) }}</textarea>
                @error('description_short') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi Lengkap</label>
                <textarea name="description_long" rows="5" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description_long', $destination->description_long) }}</textarea>
                @error('description_long') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $destination->address) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="text" name="latitude" value="{{ old('latitude', $destination->latitude) }}" placeholder="-0.95" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('latitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="text" name="longitude" value="{{ old('longitude', $destination->longitude) }}" placeholder="100.35" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('longitude') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Attributes --}}
        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Atribut</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['price_range' => ['Budget', 'price'], 'zone' => ['Zona', 'zone'], 'indoor_outdoor' => ['Indoor/Outdoor', 'io'], 'duration' => ['Durasi', 'duration']] as $field => [$label, $key])
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                        <select name="{{ $field }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach ($enums[$key] as $value => $optLabel)
                                <option value="{{ $value }}" @selected(old($field, $destination->{$field}?->value) === $value)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                        @error($field) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cocok untuk</label>
                    <div class="mt-2 grid grid-cols-2 gap-1">
                        @foreach ($enums['cocok'] as $value => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="cocok[]" value="{{ $value }}" @checked(in_array($value, $selectedCocok, true)) class="rounded text-emerald-600 focus:ring-emerald-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Waktu ideal</label>
                    <div class="mt-2 grid grid-cols-2 gap-1">
                        @foreach ($enums['waktu'] as $value => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="waktu[]" value="{{ $value }}" @checked(in_array($value, $selectedWaktu, true)) class="rounded text-emerald-600 focus:ring-emerald-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Contact & hours --}}
        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Kontak & Jam Buka</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Info harga</label>
                    <input type="text" name="price_info" value="{{ old('price_info', $destination->price_info) }}" placeholder="mis. Tiket Rp10.000" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $destination->contact_phone) }}" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Instagram</label>
                    <input type="text" name="contact_instagram" value="{{ old('contact_instagram', $destination->contact_instagram) }}" placeholder="@akun" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Website</label>
                    <input type="text" name="contact_website" value="{{ old('contact_website', $destination->contact_website) }}" placeholder="https://..." class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('contact_website') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jam buka</label>
                <textarea name="opening_hours" rows="3" placeholder="Satu baris per entri, format — Label: jam&#10;Contoh:&#10;Setiap hari: 08:00 - 17:00" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('opening_hours', $openingHoursText) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Tiap baris: <code>Label: jam</code> — mis. <code>Selasa - Minggu: 08:00 - 16:00</code></p>
            </div>
        </section>

        {{-- Tags --}}
        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Tag</h2>
            @foreach (['suasana' => 'Suasana', 'aktivitas' => 'Aktivitas', 'fasilitas' => 'Fasilitas'] as $type => $label)
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                        @foreach (($tagsByType->get($type) ?? []) as $tag)
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags)) class="rounded text-emerald-600 focus:ring-emerald-500">
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                    <input type="text" name="new_{{ $type }}" value="{{ old('new_'.$type) }}" placeholder="Tambah {{ strtolower($label) }} baru (pisahkan dengan koma)"
                           class="mt-2 w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            @endforeach
        </section>

        {{-- Images --}}
        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Galeri Foto</h2>
            @if ($editing && $destination->images->isNotEmpty())
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    @foreach ($destination->images as $img)
                        <label class="relative block cursor-pointer overflow-hidden rounded-xl border border-gray-200">
                            <img src="{{ $img->url }}" class="h-24 w-full object-cover">
                            <span class="absolute inset-x-0 bottom-0 flex items-center gap-1 bg-black/50 px-2 py-1 text-xs text-white">
                                <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="rounded"> Hapus
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700">Tambah foto</label>
                <input type="file" name="images[]" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700">
                @error('images.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="flex gap-3">
            <button class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
            <a href="{{ route('admin.destinations.index') }}" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
        </div>
    </form>
</x-admin-layout>
