<?php

namespace App\Http\Controllers;

use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\TagType;
use App\Enums\WaktuIdeal;
use App\Services\Quiz\QuizAnswers;
use App\Services\Quiz\QuizScoringService;
use App\Services\Search\FilterOptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /** Budget options (label only) — values map to price ranges in QuizAnswers. */
    private const PRICE_OPTIONS = [
        'gratis_murah' => 'Gratis - Murah',
        'sedang' => 'Sedang',
        'premium' => 'Premium',
    ];

    public function index(FilterOptions $options): View
    {
        return view('public.quiz.index', [
            'cocokOptions' => \App\Enums\CocokUntuk::options(),
            'suasanaTags' => ($options->tagsByType()->get(TagType::Suasana->value) ?? collect()),
            'priceOptions' => self::PRICE_OPTIONS,
            'waktuOptions' => WaktuIdeal::options(),
            'durationOptions' => Duration::options(),
            'ioOptions' => IndoorOutdoor::options(),
            'categories' => $options->categories(),
        ]);
    }

    public function result(Request $request, QuizScoringService $scorer): View|RedirectResponse
    {
        $answers = QuizAnswers::fromRequest($request);

        if ($answers->isEmpty()) {
            return redirect()
                ->route('quiz.index')
                ->with('status', 'Pilih minimal satu preferensi agar kami bisa merekomendasikan destinasi.');
        }

        $recommendations = $scorer->recommend($answers);

        return view('public.quiz.result', [
            'answers' => $answers,
            'recommendations' => $recommendations,
        ]);
    }
}
