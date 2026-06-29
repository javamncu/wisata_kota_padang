<x-public-layout title="Tentang & Kontak — Wisata Kota Padang">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900">Tentang</h1>
        <div class="mt-4 space-y-4 text-gray-600 leading-relaxed">
            <p>
                <strong>{{ setting('site_name') }}</strong> adalah direktori informasi wisata — semacam "satu pintu"
                bagi wisatawan domestik maupun asing yang ingin menjelajahi Kota Padang. {{ setting('about_text') }}
            </p>
            <p>
                Selain pencarian dan filter, tersedia <a href="{{ route('quiz.index') }}" class="text-emerald-700 hover:underline">kuis preferensi</a>
                yang merekomendasikan destinasi paling cocok dengan keinginanmu, serta
                <a href="{{ route('map.index') }}" class="text-emerald-700 hover:underline">peta interaktif</a> untuk melihat sebaran lokasi.
            </p>
        </div>

        <h2 class="mt-10 text-2xl font-bold text-gray-900">Kontak</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @if (setting('contact_email'))
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-900">Email</p>
                    <a href="mailto:{{ setting('contact_email') }}" class="text-sm text-emerald-700 hover:underline">{{ setting('contact_email') }}</a>
                </div>
            @endif
            @if (setting('contact_phone'))
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-900">Telepon</p>
                    <p class="text-sm text-gray-700">{{ setting('contact_phone') }}</p>
                </div>
            @endif
            @if (setting('social_instagram'))
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-gray-900">Instagram</p>
                    <p class="text-sm text-emerald-700">{{ setting('social_instagram') }}</p>
                </div>
            @endif
        </div>

        @if (setting('contact_email'))
            <div class="mt-8 rounded-2xl bg-emerald-50 p-6 text-center">
                <p class="text-emerald-800">Punya masukan tempat wisata yang belum terdaftar?</p>
                <a href="mailto:{{ setting('contact_email') }}" class="mt-3 inline-block rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Hubungi kami</a>
            </div>
        @endif
    </div>
</x-public-layout>
