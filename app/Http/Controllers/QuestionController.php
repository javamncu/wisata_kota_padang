<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function index(): View
    {
        $questions = Question::query()
            ->visible()
            ->with('user')
            // Answered questions first (more useful), then newest.
            ->orderByRaw('answer is null')
            ->latest()
            ->paginate(10);

        return view('public.questions.index', [
            'questions' => $questions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: bots fill the hidden "website" field. Pretend success so we
        // don't teach them what tripped the filter — but save nothing.
        if (filled($request->input('website'))) {
            return back()->with('status', 'Pertanyaanmu sudah terkirim.');
        }

        $rules = [
            'question' => ['required', 'string', 'min:10', 'max:1000'],
        ];

        // Guests must provide a name; logged-in users reuse their account name.
        if (! Auth::check()) {
            $rules['name'] = ['required', 'string', 'min:2', 'max:100'];
        }

        $validated = $request->validate($rules, [], ['name' => 'nama']);

        Question::create([
            'user_id' => Auth::id(),
            'author_name' => Auth::check() ? Auth::user()->name : $validated['name'],
            'question' => $validated['question'],
        ]);

        return back()->with('status', 'Pertanyaanmu sudah terkirim dan akan dijawab admin.');
    }
}
