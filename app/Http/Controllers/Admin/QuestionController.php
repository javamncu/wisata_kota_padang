<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'unanswered');

        $questions = Question::query()
            ->with('user')
            ->when($filter === 'unanswered', fn ($q) => $q->unanswered())
            ->when($filter === 'answered', fn ($q) => $q->answered())
            ->when($filter === 'hidden', fn ($q) => $q->where('is_hidden', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions,
            'currentFilter' => $filter,
            'unansweredCount' => Question::unanswered()->where('is_hidden', false)->count(),
        ]);
    }

    public function answer(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:2000'],
        ]);

        $question->update([
            'answer' => $validated['answer'],
            'answered_at' => now(),
        ]);

        return back()->with('status', 'Jawaban disimpan.');
    }

    public function toggleHide(Question $question): RedirectResponse
    {
        $question->update(['is_hidden' => ! $question->is_hidden]);

        return back()->with('status', $question->is_hidden ? 'Pertanyaan disembunyikan.' : 'Pertanyaan ditampilkan kembali.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', 'Pertanyaan dihapus.');
    }
}
