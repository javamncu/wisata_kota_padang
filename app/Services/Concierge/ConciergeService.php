<?php

namespace App\Services\Concierge;

use App\Enums\City;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\Zone;
use App\Models\Destination;
use App\Services\Search\DestinationSearch;
use App\Services\Search\SearchCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Concierge (stateless). The LLM only translates a free-text question into
 * a filter JSON; the actual recommendation work reuses the Fase 1 search
 * engine. The LLM never invents places — results always come from the DB.
 */
class ConciergeService
{
    public function __construct(
        private readonly DestinationSearch $search,
    ) {
    }

    /**
     * @return array{reply: string, off_topic: bool, destinations: Collection, criteria: ?SearchCriteria}
     */
    public function answer(string $message): array
    {
        $vocab = new Vocabulary();
        $extracted = $this->extract($message, $vocab);

        // Off-topic guardrail.
        if (($extracted['off_topic'] ?? false) === true) {
            return [
                'reply' => 'Maaf, saya hanya bisa membantu seputar wisata, kuliner, dan tempat menarik di Kota Padang. Coba tanyakan, misalnya: "kuliner enak dekat pantai" atau "tempat keluarga yang murah".',
                'off_topic' => true,
                'destinations' => collect(),
                'criteria' => null,
            ];
        }

        // Primary path: LLM extracted filters → reuse Fase 1 search engine,
        // with progressive relaxation so over-specific questions still help.
        if ($extracted !== null) {
            $criteria = $vocab->toCriteria($extracted);
            ['destinations' => $destinations, 'relaxed' => $relaxed] = $this->searchRelaxed($criteria);

            return [
                'reply' => $this->reply($criteria, $destinations, $relaxed),
                'off_topic' => false,
                'destinations' => $destinations,
                'criteria' => $criteria,
            ];
        }

        // Degraded path (LLM unavailable): local keyword fallback so the chat
        // still helps even without the AI.
        $destinations = $this->keywordFallback($message);

        return [
            'reply' => $destinations->isEmpty()
                ? 'Hmm, saya belum menemukan destinasi yang pas. Coba sebutkan jenis tempat atau areanya, ya — misalnya "pantai", "kuliner", atau "pusat kota".'
                : 'Berikut beberapa destinasi yang mungkin cocok:',
            'off_topic' => false,
            'destinations' => $destinations,
            'criteria' => null,
        ];
    }

    /**
     * Ask the LLM to map the message to a filter object. Returns the decoded
     * array, or null if the call/parse fails (caller falls back gracefully).
     */
    private function extract(string $message, Vocabulary $vocab): ?array
    {
        $key = config('concierge.gemini.key');
        if (! $key) {
            return null;
        }

        $model = $this->activeModel();
        $url = rtrim(config('concierge.gemini.endpoint'), '/')."/{$model}:generateContent";

        // Count this call against today's per-model usage.
        ConciergeUsage::increment($model);

        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->systemPrompt($vocab)]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $message]]]],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            // Retry transient server errors (503/500/502 — Gemini overloaded)
            // before giving up to the keyword fallback.
            $attempts = max(1, (int) config('concierge.gemini.retries', 2) + 1);
            $response = null;

            for ($i = 1; $i <= $attempts; $i++) {
                $response = Http::timeout((int) config('concierge.gemini.timeout', 20))
                    ->withQueryParameters(['key' => $key])
                    ->acceptJson()
                    ->post($url, $payload);

                if ($response->successful() || ! in_array($response->status(), [500, 502, 503], true)) {
                    break;
                }
                if ($i < $attempts) {
                    usleep(400000 * $i); // 0.4s, 0.8s backoff
                }
            }

            if ($response->failed()) {
                Log::warning('Concierge LLM call failed', ['status' => $response->status()]);

                return null;
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            if (! is_string($text)) {
                return null;
            }

            // Strip accidental code fences, then decode.
            $text = trim(preg_replace('/^```(json)?|```$/m', '', $text));
            $decoded = json_decode($text, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('Concierge LLM exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /** Active model: admin choice (settings) → config/.env default. */
    private function activeModel(): string
    {
        $chosen = setting('concierge_model');

        return is_string($chosen) && $chosen !== ''
            ? $chosen
            : config('concierge.gemini.model');
    }

    private function systemPrompt(Vocabulary $vocab): string
    {
        return <<<PROMPT
        Kamu adalah asisten aplikasi direktori wisata Kota Padang. Tugasmu HANYA menerjemahkan pertanyaan pengguna menjadi filter pencarian berbentuk JSON. JANGAN mengarang tempat. JANGAN menulis teks lain selain JSON.

        Kembalikan HANYA JSON dengan struktur ini:
        {
          "off_topic": boolean,
          "keyword": string|null,
          "category": string|null,
          "city": string|null,
          "zone": string|null,
          "price_range": string|null,
          "indoor_outdoor": string|null,
          "duration": string|null,
          "cocok_untuk": [string],
          "waktu_ideal": [string],
          "tags": [string],
          "exclude": [string]
        }

        Untuk semua field selain "keyword" dan "exclude", gunakan HANYA nilai dari daftar di bawah. Jika ragu, kosongkan (null atau []). Kata/istilah bebas yang tidak ada di daftar (mis. nama makanan, "pedas", "viral", nama tempat spesifik) taruh di "keyword".

        PENTING soal negasi: jika pengguna menyebut sesuatu yang TIDAK diinginkan (mis. "jangan sate", "tidak ingin sate", "selain seafood", "bukan yang ramai"), masukkan kata intinya ke "exclude" (mis. ["sate"]). JANGAN memasukkan kata yang dinegasikan itu ke "keyword".

        Daftar nilai yang sah:
        {$vocab->forPrompt()}

        Set "off_topic": true HANYA jika pertanyaan jelas tidak berkaitan dengan wisata/kuliner/tempat di Kota Padang; saat itu kosongkan field lainnya.
        PROMPT;
    }

    /**
     * Run the search, relaxing the criteria step by step until something is
     * found (drop keyword → drop tags → keep only the hard facets). Honest
     * no-match if even the loosest still returns nothing.
     *
     * @return array{destinations: Collection, relaxed: bool}
     */
    private function searchRelaxed(SearchCriteria $c): array
    {
        $limit = (int) config('concierge.results', 6);

        // City and exclusions are HARD constraints: kept in every variant so
        // results never leak into another city, and never include something the
        // user explicitly said they don't want.
        $variants = [
            $c, // exact
            new SearchCriteria( // drop free-text keyword
                category: $c->category, city: $c->city, zones: $c->zones, priceRanges: $c->priceRanges,
                indoorOutdoor: $c->indoorOutdoor, durations: $c->durations,
                cocokUntuk: $c->cocokUntuk, waktuIdeal: $c->waktuIdeal, tags: $c->tags,
                excludeKeywords: $c->excludeKeywords,
            ),
            new SearchCriteria( // also drop tags
                category: $c->category, city: $c->city, zones: $c->zones, priceRanges: $c->priceRanges,
                indoorOutdoor: $c->indoorOutdoor, durations: $c->durations,
                cocokUntuk: $c->cocokUntuk, waktuIdeal: $c->waktuIdeal,
                excludeKeywords: $c->excludeKeywords,
            ),
            new SearchCriteria( // keep only the hard facets (incl. city + exclusions)
                category: $c->category, city: $c->city, zones: $c->zones, priceRanges: $c->priceRanges,
                excludeKeywords: $c->excludeKeywords,
            ),
        ];

        foreach ($variants as $i => $variant) {
            $results = $this->search->query($variant)->take($limit)->get();
            if ($results->isNotEmpty()) {
                return ['destinations' => $results, 'relaxed' => $i > 0];
            }
        }

        return ['destinations' => collect(), 'relaxed' => false];
    }

    /** Friendly, templated opener — no extra LLM call (cost control). */
    private function reply(SearchCriteria $criteria, Collection $destinations, bool $relaxed = false): string
    {
        if ($destinations->isEmpty()) {
            if ($criteria->city) {
                return 'Maaf, saya belum menemukan destinasi di '.City::from($criteria->city)->label()
                    .' yang cocok. Data untuk kota ini masih terbatas — untuk saat ini pilihan paling lengkap ada di Padang.';
            }

            return 'Hmm, saya belum menemukan destinasi yang pas dengan kriteria itu. Coba longgarkan kriterianya ya — misalnya kurangi salah satu filter atau perluas areanya.';
        }

        if ($relaxed) {
            return 'Tidak ada yang persis cocok semua kriteria, tapi ini beberapa yang paling mendekati:';
        }

        $bits = [];
        if ($criteria->category) {
            $bits[] = $this->categoryName($criteria->category);
        }
        if ($criteria->city) {
            $bits[] = 'di '.City::from($criteria->city)->label();
        }
        if ($criteria->priceRanges) {
            $bits[] = 'budget '.PriceRange::from($criteria->priceRanges[0])->label();
        }
        if ($criteria->zones) {
            $bits[] = 'area '.Zone::from($criteria->zones[0])->label();
        }
        if ($criteria->indoorOutdoor) {
            $bits[] = IndoorOutdoor::from($criteria->indoorOutdoor[0])->label();
        }
        if ($criteria->durations) {
            $bits[] = 'durasi '.Duration::from($criteria->durations[0])->label();
        }

        $what = $bits ? ' '.implode(' · ', $bits) : '';
        $count = $destinations->count();

        return "Berikut {$count} rekomendasi{$what} yang mungkin cocok untukmu:";
    }

    private function categoryName(string $slug): string
    {
        return \App\Models\Category::where('slug', $slug)->value('name') ?? $slug;
    }

    /**
     * Offline degraded search: match any significant word from the message
     * against name/description. Used only when the LLM is unavailable.
     */
    private function keywordFallback(string $message): Collection
    {
        $stop = ['yang', 'untuk', 'dekat', 'dari', 'dan', 'atau', 'ada', 'mau', 'cari', 'carikan',
            'tolong', 'tempat', 'wisata', 'rekomendasi', 'enak', 'bagus', 'buka', 'saya', 'aku',
            'kami', 'dong', 'lah', 'sih', 'kah'];

        $words = collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($message)))
            ->filter(fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, $stop, true))
            ->unique()
            ->take(6)
            ->values();

        if ($words->isEmpty()) {
            return collect();
        }

        return Destination::query()
            ->active()
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $like = '%'.$w.'%';
                    $q->orWhere('name', 'like', $like)
                        ->orWhere('description_short', 'like', $like)
                        ->orWhere('description_long', 'like', $like);
                }
            })
            ->with(['category', 'images'])
            ->orderByDesc('review_count_cache')
            ->take((int) config('concierge.results', 6))
            ->get();
    }
}

