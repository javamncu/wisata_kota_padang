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
