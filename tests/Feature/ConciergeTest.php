<?php

namespace Tests\Feature;

use App\Services\Concierge\Vocabulary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConciergeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['concierge.gemini.key' => 'test-key']);
    }

    /** Fake a Gemini response whose extracted filter JSON is $filters. */
    private function fakeGemini(array $filters): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => json_encode($filters)]]],
                ]],
            ]),
        ]);
    }

    public function test_chat_page_loads(): void
    {
        $this->get('/asisten')->assertOk()->assertSee('AI Concierge');
    }

    public function test_extracts_filters_and_returns_grounded_destinations(): void
    {
        $this->fakeGemini(['off_topic' => false, 'category' => 'kuliner']);

        $response = $this->postJson('/asisten/tanya', ['message' => 'rumah makan enak'])
            ->assertOk()
            ->assertJsonStructure(['reply', 'off_topic', 'destinations']);

        $cards = $response->json('destinations');
        $this->assertNotEmpty($cards);
        $this->assertLessThanOrEqual(6, count($cards));
        foreach ($cards as $card) {
            $this->assertSame('Kuliner', $card['category']);
        }
    }

    public function test_off_topic_is_redirected_with_no_destinations(): void
    {
        $this->fakeGemini(['off_topic' => true]);

        $this->postJson('/asisten/tanya', ['message' => 'cara membuat kue ulang tahun'])
            ->assertOk()
            ->assertJson(['off_topic' => true, 'destinations' => []]);
    }

    public function test_no_match_is_answered_honestly(): void
    {
        // Religi destinations are all free, so "premium religi" yields nothing.
        $this->fakeGemini(['category' => 'wisata-religi', 'price_range' => 'premium']);

        $response = $this->postJson('/asisten/tanya', ['message' => 'wisata religi mewah'])->assertOk();

        $this->assertEmpty($response->json('destinations'));
        $this->assertStringContainsString('belum menemukan', $response->json('reply'));
    }

    public function test_llm_failure_falls_back_to_keyword_search(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('boom', 500)]);

        // "pantai" matches destination names/descriptions via keyword fallback.
        $response = $this->postJson('/asisten/tanya', ['message' => 'pantai'])->assertOk();

        $this->assertNotEmpty($response->json('destinations'));
    }

    public function test_guest_is_rate_limited(): void
    {
        config(['concierge.rate_limits.guest' => 1]);
        $this->fakeGemini(['category' => 'kuliner']);

        $this->postJson('/asisten/tanya', ['message' => 'satu'])->assertOk();
        $this->postJson('/asisten/tanya', ['message' => 'dua'])
            ->assertStatus(429)
            ->assertJson(['limited' => true]);
    }

    public function test_vocabulary_maps_and_validates_city(): void
    {
        $vocab = new Vocabulary();

        $this->assertSame('bukittinggi', $vocab->toCriteria(['city' => 'bukittinggi'])->city);
        $this->assertNull($vocab->toCriteria(['city' => 'kota-antah-berantah'])->city);
        $this->assertNull($vocab->toCriteria([])->city);
    }

    public function test_city_filter_scopes_concierge_results(): void
    {
        // Silungkang Art Centre is the seeded Sawahlunto destination.
        $this->fakeGemini(['off_topic' => false, 'city' => 'sawahlunto']);

        $response = $this->postJson('/asisten/tanya', ['message' => 'wisata di Sawahlunto'])->assertOk();

        $cards = $response->json('destinations');
        $this->assertNotEmpty($cards);
        foreach ($cards as $card) {
            $this->assertSame('Sawahlunto', $card['city']);
        }
    }

    public function test_city_with_no_matching_data_is_answered_honestly(): void
    {
        // Malls only exist in Padang, so "mall in Bukittinggi" yields nothing —
        // and city is a hard facet, so results must not leak in from Padang.
        $this->fakeGemini(['off_topic' => false, 'city' => 'bukittinggi', 'category' => 'mall']);

        $response = $this->postJson('/asisten/tanya', ['message' => 'mall di Bukittinggi'])->assertOk();

        $this->assertEmpty($response->json('destinations'));
        $this->assertStringContainsString('Bukittinggi', $response->json('reply'));
    }

    public function test_vocabulary_maps_and_cleans_exclude_terms(): void
    {
        $vocab = new Vocabulary();

        $criteria = $vocab->toCriteria(['exclude' => ['sate', '  seafood  ', '', 'sate']]);

        $this->assertSame(['sate', 'seafood'], $criteria->excludeKeywords);
        $this->assertSame([], $vocab->toCriteria([])->excludeKeywords);
    }

    public function test_exclusion_removes_unwanted_results(): void
    {
        $search = app(\App\Services\Search\DestinationSearch::class);

        // Sanity: "Sate Manang Kabau" is a kuliner destination in the dataset.
        $kuliner = $search->query(new \App\Services\Search\SearchCriteria(category: 'kuliner'))->pluck('name');
        $this->assertTrue($kuliner->contains('Sate Manang Kabau'));

        // "aku ingin kuliner untuk keluarga, tapi tidak ingin sate"
        $this->fakeGemini([
            'off_topic' => false,
            'category' => 'kuliner',
            'cocok_untuk' => ['keluarga'],
            'exclude' => ['sate'],
        ]);

        $response = $this->postJson('/asisten/tanya', [
            'message' => 'aku ingin kuliner yang cocok untuk keluarga, tidak ingin sate',
        ])->assertOk();

        $names = collect($response->json('destinations'))->pluck('name');
        $this->assertNotEmpty($names);
        foreach ($names as $name) {
            $this->assertStringNotContainsStringIgnoringCase('sate', $name);
        }
    }

    public function test_vocabulary_drops_invalid_filters(): void
    {
        $vocab = new Vocabulary();

        $criteria = $vocab->toCriteria([
            'category' => 'tidak-ada-kategori',
            'zone' => 'pusat_kota',
            'tags' => ['pedas', 'santai'],          // "pedas" is not a tag
            'cocok_untuk' => ['keluarga', 'alien'], // "alien" invalid
        ]);

        $this->assertNull($criteria->category);           // invalid dropped
        $this->assertSame(['pusat_kota'], $criteria->zones);
        $this->assertSame(['santai'], $criteria->tags);   // only valid tag kept
        $this->assertSame(['keluarga'], $criteria->cocokUntuk);
    }
}
