<?php

namespace App\Services\Quiz;

use App\Models\Destination;
use Illuminate\Support\Collection;

/**
 * Rule-based recommendation scorer (no AI/ML). Each matching attribute adds
 * weighted points; results are returned sorted by score, each carrying the
 * list of matched attributes for the "kenapa direkomendasikan" label.
 */
class QuizScoringService
{
    /** Weights per PRD section 9. */
    private const WEIGHTS = [
        'cocok_untuk' => 3,
        'price_range' => 3,
        'suasana' => 2,
        'waktu_ideal' => 2,
        'duration' => 1,
        'indoor_outdoor' => 1,
        'category' => 1,
    ];

    /**
     * @return Collection<int, array{destination: Destination, score: int, reasons: string[]}>
     */
    public function recommend(QuizAnswers $answers, int $limit = 12): Collection
    {
        $destinations = Destination::query()
            ->active()
            ->with(['category', 'images', 'tags'])
            ->get();

        return $destinations
            ->map(fn (Destination $d) => $this->scoreDestination($d, $answers))
            ->filter(fn (array $row) => $row['score'] > 0)
            ->sortByDesc(fn (array $row) => [$row['score'], (float) $row['destination']->rating_cache])
            ->values()
            ->take($limit);
    }

    /**
     * @return array{destination: Destination, score: int, reasons: string[]}
     */
    private function scoreDestination(Destination $destination, QuizAnswers $a): array
    {
        $score = 0;
        $reasons = [];

        // cocok_untuk (multi-value JSON) — destination contains the answer
        if ($a->cocokUntuk !== null && $destination->cocok_untuk->contains($a->cocokUntuk)) {
            $score += self::WEIGHTS['cocok_untuk'];
            $reasons[] = 'Cocok untuk '.$a->cocokUntuk->label();
        }

        // price_range — destination price is within the chosen budget set
        if ($a->priceRanges !== [] && in_array($destination->price_range->value, $a->priceRanges, true)) {
            $score += self::WEIGHTS['price_range'];
            $reasons[] = 'Sesuai budget ('.$destination->price_range->label().')';
        }

        // suasana — destination carries the chosen suasana tag
        if ($a->suasanaTag !== null) {
            $tag = $destination->tags->firstWhere('slug', $a->suasanaTag);
            if ($tag !== null) {
                $score += self::WEIGHTS['suasana'];
                $reasons[] = 'Suasana '.$tag->name;
            }
        }

        // waktu_ideal (multi-value JSON)
        if ($a->waktuIdeal !== null && $destination->waktu_ideal->contains($a->waktuIdeal)) {
            $score += self::WEIGHTS['waktu_ideal'];
            $reasons[] = 'Pas dikunjungi '.$a->waktuIdeal->label();
        }

        // duration
        if ($a->duration !== null && $destination->duration === $a->duration) {
            $score += self::WEIGHTS['duration'];
            $reasons[] = 'Durasi '.$a->duration->label();
        }

        // indoor_outdoor
        if ($a->indoorOutdoor !== null && $destination->indoor_outdoor === $a->indoorOutdoor) {
            $score += self::WEIGHTS['indoor_outdoor'];
            $reasons[] = $a->indoorOutdoor->label();
        }

        // category
        if ($a->category !== null && $destination->category->slug === $a->category) {
            $score += self::WEIGHTS['category'];
            $reasons[] = 'Kategori '.$destination->category->name;
        }

        return [
            'destination' => $destination,
            'score' => $score,
            'reasons' => $reasons,
        ];
    }
}
