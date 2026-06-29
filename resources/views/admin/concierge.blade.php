<x-admin-layout title="AI Concierge" heading="AI Concierge">
    <div class="max-w-3xl" x-data="conciergePanel()">
        {{-- Honest note --}}
        <div class="mb-5 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
            <p class="font-medium">Tentang angka pemakaian</p>
            <p class="mt-1">Google <strong>tidak menyediakan sisa kuota lewat API</strong>. Angka "terpakai" dihitung dari panggilan aplikasi ini hari ini, dan "estimasi sisa" memakai perkiraan batas free-tier (bisa disetel di config). Untuk kepastian, gunakan tombol <strong>Cek status</strong> yang menguji model langsung (memakai 1 panggilan).</p>
        </div>

        <form method="POST" action="{{ route('admin.concierge.update') }}">
            @csrf @method('PUT')

            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 p-4">
                    <h2 class="font-semibold text-gray-900">Pilih Model Gemini</h2>
                    <a href="{{ route('admin.concierge.index', ['refresh' => 1]) }}" class="text-xs font-medium text-emerald-700 hover:underline">↻ Segarkan daftar model</a>
                </div>

                <div class="divide-y divide-gray-50">
                    @foreach ($models as $m)
                        <label class="flex items-center gap-4 p-4 hover:bg-gray-50">
                            <input type="radio" name="concierge_model" value="{{ $m }}" @checked($active === $m) class="text-emerald-600 focus:ring-emerald-500">

                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900">
                                    {{ $m }}
                                    @if ($active === $m)<span class="ml-2 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">aktif</span>@endif
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    Terpakai hari ini: <span class="font-medium" x-text="usage[@js($m)] ?? 0"></span> ·
                                    Estimasi sisa: <span x-text="caps[@js($m)] ? Math.max(0, caps[@js($m)] - (usage[@js($m)] ?? 0)) + ' / ' + caps[@js($m)] : 'N/A'"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <template x-if="statuses[@js($m)]">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="statuses[@js($m)].ok ? 'bg-emerald-50 text-emerald-700' : (statuses[@js($m)].transient ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600')"
                                          x-text="(statuses[@js($m)].ok ? '✅ ' : (statuses[@js($m)].transient ? '⏳ ' : '⛔ ')) + statuses[@js($m)].message"></span>
                                </template>
                                <button type="button" @click="check(@js($m))" :disabled="busy === @js($m)"
                                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50">
                                    <span x-show="busy !== @js($m)">Cek status</span>
                                    <span x-show="busy === @js($m)" x-cloak>Mengecek…</span>
                                </button>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 p-4">
                    <button class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Model</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function conciergePanel() {
                return {
                    statuses: {},
                    usage: @js($usage),
                    caps: @js((object) $caps),
                    busy: null,
                    async check(model) {
                        this.busy = model;
                        try {
                            const res = await fetch('{{ route('admin.concierge.check') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                                body: JSON.stringify({ model }),
                            });
                            const data = await res.json();
                            this.statuses[model] = { ok: !!data.ok, transient: !!data.transient, message: data.message || '' };
                            if (typeof data.used === 'number') this.usage[model] = data.used;
                        } catch (e) {
                            this.statuses[model] = { ok: false, message: 'Error koneksi' };
                        } finally {
                            this.busy = null;
                        }
                    },
                };
            }
        </script>
    @endpush
</x-admin-layout>
