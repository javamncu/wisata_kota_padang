<x-admin-layout title="Tanya Jawab" heading="Tanya Jawab">
    {{-- Filter tabs --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @php $tabs = ['unanswered' => 'Belum dijawab', 'answered' => 'Sudah dijawab', 'hidden' => 'Disembunyikan', '' => 'Semua']; @endphp
        @foreach ($tabs as $value => $label)
            <a href="{{ route('admin.questions.index', $value !== '' ? ['filter' => $value] : []) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium {{ $currentFilter === $value || ($value === '' && ! in_array($currentFilter, ['unanswered', 'answered', 'hidden'], true)) ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
                @if ($value === 'unanswered' && $unansweredCount > 0)
                    <span class="ml-1 rounded-full bg-amber-400 px-1.5 text-xs text-white">{{ $unansweredCount }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($questions as $question)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-gray-900">{{ $question->author_name }}</span>
                    @if (! $question->user_id)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Tamu</span>
                    @endif
                    @if ($question->isAnswered())
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Dijawab</span>
                    @else
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">Belum dijawab</span>
                    @endif
                    @if ($question->is_hidden)
                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600">Disembunyikan</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $question->created_at->translatedFormat('d M Y H:i') }}</span>
                </div>

                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $question->question }}</p>

                {{-- Answer form (textarea) --}}
                <form method="POST" action="{{ route('admin.questions.answer', $question) }}" class="mt-3">
                    @csrf @method('PUT')
                    <textarea name="answer" rows="2" placeholder="Tulis jawaban..."
                              class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('answer', $question->answer) }}</textarea>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                            {{ $question->isAnswered() ? 'Perbarui Jawaban' : 'Simpan Jawaban' }}
                        </button>
                        {{-- These buttons submit the sibling forms below via the form= attribute --}}
                        <button type="submit" form="q-hide-{{ $question->id }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            {{ $question->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}
                        </button>
                        <button type="submit" form="q-del-{{ $question->id }}" onclick="return confirm('Hapus pertanyaan ini?')" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                            Hapus
                        </button>
                    </div>
                </form>

                {{-- Sibling forms for the action buttons above (no nesting) --}}
                <form method="POST" action="{{ route('admin.questions.toggle-hide', $question) }}" id="q-hide-{{ $question->id }}">@csrf</form>
                <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" id="q-del-{{ $question->id }}">@csrf @method('DELETE')</form>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500">Tidak ada pertanyaan.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>
</x-admin-layout>
