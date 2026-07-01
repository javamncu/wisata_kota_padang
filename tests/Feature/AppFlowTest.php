<?php

namespace Tests\Feature;

use App\Enums\CocokUntuk;
use App\Enums\ReviewStatus;
use App\Enums\Status;
use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use App\Services\Quiz\QuizAnswers;
use App\Services\Quiz\QuizScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(): User
    {
        return User::where('email', 'user@wisatapadang.test')->first();
    }

    public function test_public_pages_load_for_guests(): void
    {
        $destination = Destination::active()->first();

        $this->get('/')->assertOk();
        $this->get('/explore')->assertOk();
        $this->get(route('categories.show', $destination->category))->assertOk();
        $this->get(route('destinations.show', $destination))->assertOk();
        $this->get('/kuis')->assertOk();
        $this->get('/peta')->assertOk();
    }

    public function test_draft_destination_is_hidden_from_guests(): void
    {
        $destination = Destination::active()->first();
        $destination->update(['status' => Status::Draft]);

        $this->get(route('destinations.show', $destination))->assertNotFound();
    }

    public function test_favorites_require_login(): void
    {
        $this->get('/favorit')->assertRedirect(route('login'));
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = $this->user();
        $destination = Destination::active()->first();

        $this->actingAs($user)
            ->post(route('favorites.toggle', $destination))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);
    }

    public function test_user_review_is_pending_and_not_counted_until_published(): void
    {
        $user = $this->user();
        $destination = Destination::active()->first();

        $this->actingAs($user)->post(route('reviews.store', $destination), [
            'rating' => 5,
            'comment' => 'Bagus sekali',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'destination_id' => $destination->id,
            'rating' => 5,
            'status' => ReviewStatus::Pending->value,
        ]);

        // Pending reviews do not affect the cached rating.
        $destination->refresh();
        $this->assertNull($destination->rating_cache);
        $this->assertSame(0, $destination->review_count_cache);
    }

    public function test_published_review_updates_rating_cache(): void
    {
        $user = $this->user();
        $destination = Destination::active()->first();

        Review::create([
            'user_id' => $user->id,
            'destination_id' => $destination->id,
            'rating' => 4,
            'status' => ReviewStatus::Published,
        ]);

        $destination->refresh();
        $this->assertEquals(4.0, (float) $destination->rating_cache);
        $this->assertSame(1, $destination->review_count_cache);
    }

    public function test_user_cannot_edit_another_users_review(): void
    {
        $owner = $this->user();
        $other = User::where('email', 'admin@wisatapadang.test')->first();
        // make "other" a normal user perspective by using a fresh non-admin
        $stranger = User::factory()->create();

        $destination = Destination::active()->first();
        $review = Review::create([
            'user_id' => $owner->id,
            'destination_id' => $destination->id,
            'rating' => 3,
            'status' => ReviewStatus::Published,
        ]);

        $this->actingAs($stranger)
            ->patch(route('reviews.update', $review), ['rating' => 1])
            ->assertForbidden();
    }

    public function test_nearby_prompt_loads_without_location(): void
    {
        // Without coordinates the page shows the permission prompt (no DB
        // distance query runs — ST_Distance_Sphere is MySQL-only).
        $this->get('/sekitar')
            ->assertOk()
            ->assertSee('Izinkan akses lokasi');
    }

    public function test_seed_dataset_is_clean(): void
    {
        // The destination seeder imports the JSON dataset and must reuse the
        // canonical tags (no accidental duplicate slugs).
        $this->assertSame(115, \App\Models\Destination::count());
        $this->assertSame(22, \App\Models\Tag::count());

        // No two tags share a normalized slug.
        $slugs = \App\Models\Tag::pluck('slug');
        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    public function test_city_filter_scopes_results(): void
    {
        $search = app(\App\Services\Search\DestinationSearch::class);
        $query = fn (string $city) => $search->query(new \App\Services\Search\SearchCriteria(city: $city))->get();

        $padang = $query('padang');
        $bukittinggi = $query('bukittinggi');

        // Each of the seeded cities has its own destinations.
        $this->assertGreaterThan(0, $padang->count());
        $this->assertGreaterThan(0, $bukittinggi->count());

        // A city filter must never leak destinations from another city.
        $this->assertSame(['padang'], $padang->pluck('city')->map->value->unique()->values()->all());
        $this->assertSame(['bukittinggi'], $bukittinggi->pluck('city')->map->value->unique()->values()->all());

        // An unknown city value is ignored (treated as "all cities").
        $this->get('/explore?city=tidak-ada')->assertOk();
        $this->get('/explore?city=bukittinggi')->assertOk();
    }

    public function test_quiz_scoring_ranks_matches(): void
    {
        $answers = new QuizAnswers(cocokUntuk: CocokUntuk::Keluarga);

        $results = app(QuizScoringService::class)->recommend($answers);

        $this->assertNotEmpty($results);
        // Every returned row matched the family preference (+3) at minimum.
        foreach ($results as $row) {
            $this->assertGreaterThan(0, $row['score']);
            $this->assertContains('Cocok untuk Keluarga & anak', $row['reasons']);
        }
    }
}
