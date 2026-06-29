@php
    $editing = $article->exists;
    $input = 'mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500';
    $lbl = 'block text-sm font-medium text-gray-700';
@endphp

<x-admin-layout :title="$editing ? 'Edit Artikel' : 'Tulis Artikel'" :heading="$editing ? 'Edit Artikel' : 'Tulis Artikel'">
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/trix@2.1.1/dist/trix.css">
        <style>
            trix-editor { min-height: 18rem; border-radius: 0.75rem; border-color: #e5e7eb; }
            .trix-content h1 { font-size: 1.5rem; font-weight: 700; }
            .trix-content a { color: #047857; text-decoration: underline; }
            .trix-content ul { list-style: disc; padding-left: 1.5rem; }
            .trix-content ol { list-style: decimal; padding-left: 1.5rem; }
        </style>
    @endpush

    <form method="POST" enctype="multipart/form-data" class="max-w-3xl space-y-6"
          action="{{ $editing ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">Ada {{ $errors->count() }} kesalahan input.</div>
        @endif

        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div>
                <label class="{{ $lbl }}">Judul</label>
                <input name="title" value="{{ old('title', $article->title) }}" class="{{ $input }}">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $lbl }}">Ringkasan (excerpt)</label>
                <textarea name="excerpt" rows="2" class="{{ $input }}">{{ old('excerpt', $article->excerpt) }}</textarea>
                @error('excerpt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $lbl }}">Isi artikel</label>
                <input id="article-body" type="hidden" name="body" value="{{ old('body', $article->body) }}">
                <trix-editor input="article-body" class="trix-content mt-1 bg-white"></trix-editor>
                @error('body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Publikasi</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $lbl }}">Status</label>
                    <select name="status" class="{{ $input }}">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $article->status?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $lbl }}">Tanggal terbit</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', $article->published_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                    <p class="mt-1 text-xs text-gray-400">Kosongkan = otomatis saat dipublikasikan.</p>
                </div>
            </div>
            <div>
                <label class="{{ $lbl }}">Cover</label>
                @if ($editing && $article->coverUrl())
                    <img src="{{ $article->coverUrl() }}" class="mt-1 h-32 rounded-xl object-cover">
                @endif
                <input type="file" name="cover" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700">
                @error('cover') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">Destinasi terkait</h2>
            <p class="text-xs text-gray-400">Tahan Ctrl/Cmd untuk pilih beberapa. Ditampilkan sebagai kartu di artikel.</p>
            <select name="destinations[]" multiple size="8" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                @foreach ($destinations as $d)
                    <option value="{{ $d->id }}" @selected(in_array($d->id, (array) old('destinations', $selectedDestinations)))>{{ $d->name }}</option>
                @endforeach
            </select>
        </section>

        <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-gray-900">SEO</h2>
            <div>
                <label class="{{ $lbl }}">Meta title</label>
                <input name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" class="{{ $input }}">
            </div>
            <div>
                <label class="{{ $lbl }}">Meta description</label>
                <textarea name="meta_description" rows="2" class="{{ $input }}">{{ old('meta_description', $article->meta_description) }}</textarea>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
            <a href="{{ route('admin.articles.index') }}" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
        </div>
    </form>

    @push('scripts')
        <script src="https://unpkg.com/trix@2.1.1/dist/trix.umd.min.js"></script>
    @endpush
</x-admin-layout>
