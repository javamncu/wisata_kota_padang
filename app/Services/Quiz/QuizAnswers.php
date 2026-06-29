<?php

namespace App\Services\Quiz;

use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\WaktuIdeal;
use Illuminate\Http\Request;

/**
 * The preference profile produced by the quiz. Every dimension is optional —
 * a null/skip ("Tidak masalah") means that dimension is simply not scored.
 */
class QuizAnswers
{
    /**
     * Quiz budget options map to one-or-more price_range values.
     */
    public const PRICE_OPTIONS = [
        'gratis_murah' => ['gratis', 'murah'],
        'sedang' => ['sedang'],
        'premium' => ['premium'],
    ];

    public function __construct(
        public readonly ?CocokUntuk $cocokUntuk = null,
        public readonly ?string $suasanaTag = null,   // tag slug (type: suasana)
        /** @var string[] price_range values accepted by the chosen budget */
        public readonly array $priceRanges = [],
        public readonly ?string $priceOption = null,   // raw option key, for redisplay
        public readonly ?WaktuIdeal $waktuIdeal = null,
        public readonly ?Duration $duration = null,
        public readonly ?IndoorOutdoor $indoorOutdoor = null,
        public readonly ?string $category = null,      // category slug
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $priceOption = $request->input('price');
        $priceRanges = self::PRICE_OPTIONS[$priceOption] ?? [];

        return new self(
            cocokUntuk: CocokUntuk::fromValue($request->input('cocok')),
            suasanaTag: self::clean($request->input('suasana')),
            priceRanges: $priceRanges,
            priceOption: is_string($priceOption) && $priceOption !== '' ? $priceOption : null,
            waktuIdeal: WaktuIdeal::fromValue($request->input('waktu')),
            duration: Duration::fromValue($request->input('duration')),
            indoorOutdoor: IndoorOutdoor::fromValue($request->input('io')),
            category: self::clean($request->input('category')),
        );
    }

    /** True when the user skipped every question. */
    public function isEmpty(): bool
    {
        return $this->cocokUntuk === null
            && $this->suasanaTag === null
            && $this->priceRanges === []
            && $this->waktuIdeal === null
            && $this->duration === null
            && $this->indoorOutdoor === null
            && $this->category === null;
    }

    private static function clean(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
