<x-public-layout title="AI Concierge — Wisata Kota Padang">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="concierge()">
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">✨ AI Concierge</h1>
            <p class="mt-1 text-gray-500">Tanya pakai bahasa sehari-hari, saya carikan destinasi yang cocok dari database kami.</p>
        </div>

        {{-- Thread --}}
        <div x-ref="thread" class="mt-6 h-[55vh] space-y-4 overflow-y-auto rounded-2xl border border-gray-100 bg-gray-50 p-4">
            {{-- Greeting --}}
            <div class="flex justify-start">
                <div class="max-w-[85%] rounded-2xl border border-gray-100 bg-white px-4 py-2.5 text-gray-800 shadow-sm">
                    Halo! 👋 Mau cari tempat seperti apa di Padang? Coba salah satu contoh di bawah, atau tulis sendiri.
                </div>
            </div>

            <template x-for="(m, i) in messages" :key="i">
                <div class="flex" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 shadow-sm"
                         :class="m.role === 'user' ? 'bg-emerald-600 text-white' : 'border border-gray-100 bg-white text-gray-800'">
                        <p x-text="m.text" class="whitespace-pre-line"></p>

                        <template x-if="m.destinations && m.destinations.length">
                            <div class="mt-3 space-y-2">
                                <template x-for="d in m.destinations" :key="d.url">
                                    <a :href="d.url" class="flex gap-3 rounded-xl border border-gray-100 bg-gray-50 p-2 transition hover:bg-gray-100">
                                        <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600">
                                            <template x-if="d.image"><img :src="d.image" class="h-full w-full object-cover"></template>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-gray-900" x-text="d.name"></p>
                                            <p class="text-xs text-gray-500" x-text="[d.category, d.city, d.price].filter(Boolean).join(' · ')"></p>
                                            <p class="text-xs text-amber-500" x-show="d.rating" x-text="'⭐ ' + (d.rating ? d.rating.toFixed(1) : '')"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="rounded-2xl border border-gray-100 bg-white px-4 py-3 shadow-sm">
                    <div class="flex gap-1">
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-300" style="animation-delay:0ms"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-300" style="animation-delay:150ms"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-300" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Example chips --}}
        <div class="mt-4 flex flex-wrap gap-2" x-show="messages.length === 0">
            @foreach ($examples as $example)
                <button type="button" @click="send(@js($example))" class="chip">{{ $example }}</button>
            @endforeach
        </div>

        {{-- Input --}}
        <form @submit.prevent="send()" class="mt-4 flex gap-2">
            <input type="text" x-model="input" :disabled="loading" maxlength="500"
                   placeholder="mis. kuliner pedas dekat pantai yang buka malam"
                   class="flex-1 rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500">
            <button type="submit" :disabled="loading"
                    class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                Kirim
            </button>
        </form>
        <p class="mt-2 text-center text-xs text-gray-400">Rekomendasi diambil dari database destinasi kami — bukan karangan AI.</p>
    </div>

    @push('scripts')
        <script>
            function concierge() {
                return {
                    messages: [],
                    input: '',
                    loading: false,
                    async send(text) {
                        const msg = (text ?? this.input).trim();
                        if (!msg || this.loading) return;
                        this.input = '';
                        this.messages.push({ role: 'user', text: msg, destinations: [] });
                        this.loading = true;
                        this.scrollDown();
                        try {
                            const res = await fetch('{{ route('concierge.ask') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                                body: JSON.stringify({ message: msg }),
                            });
                            const data = await res.json();
                            this.messages.push({
                                role: 'assistant',
                                text: data.reply || 'Maaf, terjadi kesalahan. Coba lagi ya.',
                                destinations: data.destinations || [],
                            });
                        } catch (e) {
                            this.messages.push({ role: 'assistant', text: 'Maaf, asisten sedang tidak bisa dihubungi. Coba lagi sebentar.', destinations: [] });
                        } finally {
                            this.loading = false;
                            this.scrollDown();
                        }
                    },
                    scrollDown() {
                        this.$nextTick(() => { const el = this.$refs.thread; if (el) el.scrollTop = el.scrollHeight; });
                    },
                };
            }
        </script>
    @endpush
</x-public-layout>
