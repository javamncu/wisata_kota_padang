<x-public-layout title="Kuis Preferensi — Wisata Kota Padang">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900">Kuis Preferensi</h1>
            <p class="mt-2 text-gray-500">Jawab pertanyaan berikut — boleh dilewati. Kami carikan destinasi paling cocok.</p>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('quiz.result') }}" x-data="{ step: 1, total: 7 }" class="mt-8">
            {{-- Progress --}}
            <div class="mb-8 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" :style="`width: ${(step / total) * 100}%`"></div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <x-quiz-step :index="1" :total="7" title="Kamu jalan dengan siapa?" subtitle="Pilih teman perjalananmu.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($cocokOptions as $value => $label)
                            <x-quiz-option name="cocok" :value="$value" :label="$label" />
                        @endforeach
                        <x-quiz-option name="cocok" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="2" :total="7" title="Suasana yang dicari?" subtitle="Vibe seperti apa yang kamu mau.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($suasanaTags as $tag)
                            <x-quiz-option name="suasana" :value="$tag->slug" :label="$tag->name" />
                        @endforeach
                        <x-quiz-option name="suasana" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="3" :total="7" title="Budget per orang?" subtitle="Perkiraan pengeluaran.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($priceOptions as $value => $label)
                            <x-quiz-option name="price" :value="$value" :label="$label" />
                        @endforeach
                        <x-quiz-option name="price" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="4" :total="7" title="Rencananya kapan?" subtitle="Waktu kunjungan ideal.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($waktuOptions as $value => $label)
                            <x-quiz-option name="waktu" :value="$value" :label="$label" />
                        @endforeach
                        <x-quiz-option name="waktu" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="5" :total="7" title="Berapa lama waktu luangmu?" subtitle="Durasi kunjungan.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($durationOptions as $value => $label)
                            <x-quiz-option name="duration" :value="$value" :label="$label" />
                        @endforeach
                        <x-quiz-option name="duration" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="6" :total="7" title="Lebih suka di mana?" subtitle="Indoor atau outdoor.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($ioOptions as $value => $label)
                            <x-quiz-option name="io" :value="$value" :label="$label" />
                        @endforeach
                        <x-quiz-option name="io" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>

                <x-quiz-step :index="7" :total="7" title="Tertarik kategori apa?" subtitle="Opsional — boleh dilewati.">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($categories as $cat)
                            <x-quiz-option name="category" :value="$cat->slug" :label="$cat->name" />
                        @endforeach
                        <x-quiz-option name="category" value="" label="Tidak masalah" />
                    </div>
                </x-quiz-step>
            </div>
        </form>
    </div>
</x-public-layout>
