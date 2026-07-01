<x-public-layout title="Tanya Jawab — Wisata Kota Padang">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tanya Jawab</h1>
            <p class="mt-1 text-gray-500">Punya pertanyaan seputar wisata Kota Padang? Tanyakan ke admin — pertanyaan & jawabannya bisa dilihat semua pengunjung.</p>
        </div>

        {{-- Ask form --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900">Ajukan Pertanyaan</h2>
            <form method="POST" action="{{ route('questions.store') }}" class="mt-3 space-y-3">
                @csrf

                {{-- Honeypot: must stay empty (hidden from humans) --}}
                <div class="hidden" aria-hidden="true">
                    <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                @guest
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kamu"
                               class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @else
                    <p class="text-sm text-gray-500">Bertanya sebagai <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span></p>
                @endguest

                <div>
                    <label class="block text-sm font-medium text-gray-700">Pertanyaan</label>
                    <textarea name="question" rows="3" placeholder="Tulis pertanyaanmu di sini..."
                              class="mt-1 w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">{{ old('question') }}</textarea>
                    @error('question') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Kirim Pertanyaan</button>
            </form>
        </div>

        {{-- Q&A list --}}
        <div class="mt-8 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Pertanyaan Pengunjung</h2>

            @forelse ($questions as $question)
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    {{-- Question --}}
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">T</span>
                        <div class="min-w-0">
                            <p class="whitespace-pre-line text-gray-800">{{ $question->question }}</p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ $question->author_name }} · {{ $question->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </div>

                    {{-- Answer --}}
                    @if ($question->isAnswered())
                        <div class="mt-4 flex items-start gap-3 rounded-xl bg-emerald-50/60 p-4">
                            <span class="mt-0.5 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">J</span>
                            <div class="min-w-0">
                                <p class="whitespace-pre-line text-gray-700">{{ $question->answer }}</p>
                                <p class="mt-1 text-xs text-emerald-700/70">Admin · {{ $question->answered_at?->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 pl-10">
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                ⏳ Belum dijawab
                            </span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500">
                    Belum ada pertanyaan. Jadilah yang pertama bertanya!
                </div>
            @endforelse

            <div class="pt-2">{{ $questions->links() }}</div>
        </div>
    </div>
</x-public-layout>
