<x-admin-layout title="Pengaturan" heading="Pengaturan Situs">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl" x-data="{ tab: 'umum' }">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                Periksa kembali isian — ada {{ $errors->count() }} kesalahan.
            </div>
        @endif

        {{-- Tabs --}}
        <div class="mb-5 flex flex-wrap gap-2 border-b border-gray-200">
            @foreach (['umum' => 'Umum', 'beranda' => 'Konten Beranda', 'budget' => 'Budget', 'tampilan' => 'Tampilan'] as $key => $label)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="-mb-px border-b-2 px-4 py-2 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </div>

        @php
            $input = 'mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500';
            $lbl = 'block text-sm font-medium text-gray-700';
        @endphp

        {{-- Umum --}}
        <div x-show="tab === 'umum'" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div>
                <label class="{{ $lbl }}">Nama situs</label>
                <input name="site_name" value="{{ old('site_name', $settings['site_name']) }}" class="{{ $input }}">
                @error('site_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $lbl }}">Email kontak</label>
                    <input name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="{{ $input }}">
                    @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $lbl }}">Telepon</label>
                    <input name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="{{ $input }}">
                </div>
            </div>
            <div>
                <label class="{{ $lbl }}">Instagram</label>
                <input name="social_instagram" value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="@akun" class="{{ $input }}">
            </div>
        </div>

        {{-- Konten Beranda --}}
        <div x-show="tab === 'beranda'" x-cloak class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div>
                <label class="{{ $lbl }}">Judul hero</label>
                <input name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}" class="{{ $input }}">
                @error('hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $lbl }}">Subjudul hero</label>
                <textarea name="hero_subtitle" rows="2" class="{{ $input }}">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
            </div>
            <div>
                <label class="{{ $lbl }}">Teks Tentang</label>
                <textarea name="about_text" rows="3" class="{{ $input }}">{{ old('about_text', $settings['about_text']) }}</textarea>
            </div>
            <div>
                <label class="{{ $lbl }}">Destinasi unggulan (maks 6)</label>
                <p class="text-xs text-gray-400">Kosongkan untuk otomatis (paling banyak diulas). Tahan Ctrl/Cmd untuk pilih beberapa.</p>
                @php $selectedFeatured = (array) old('featured_slugs', $settings['featured_slugs'] ?? []); @endphp
                <select name="featured_slugs[]" multiple size="8" class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($destinations as $d)
                        <option value="{{ $d->slug }}" @selected(in_array($d->slug, $selectedFeatured, true))>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('featured_slugs') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Budget --}}
        <div x-show="tab === 'budget'" x-cloak class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-500">Keterangan tiap tingkat budget — hanya ditampilkan sebagai hint ke pengunjung. Tidak mengubah logika filter.</p>
            @foreach ($priceRanges as $range)
                <div>
                    <label class="{{ $lbl }}">{{ $range->label() }}</label>
                    <input name="budget_{{ $range->value }}" value="{{ old('budget_'.$range->value, $settings['budget_'.$range->value] ?? '') }}" class="{{ $input }}">
                </div>
            @endforeach
        </div>

        {{-- Tampilan --}}
        <div x-show="tab === 'tampilan'" x-cloak class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $lbl }}">Item per halaman (Explore)</label>
                    <input type="number" name="per_page" min="4" max="48" value="{{ old('per_page', $settings['per_page']) }}" class="{{ $input }}">
                    @error('per_page') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="{{ $lbl }}">Sorting default</label>
                    <select name="default_sort" class="{{ $input }}">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('default_sort', $settings['default_sort']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <button class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Pengaturan</button>
        </div>
    </form>
</x-admin-layout>
