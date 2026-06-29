<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@wisatapadang.test')->first();
    }

    private function user(): User
    {
        return User::where('email', 'user@wisatapadang.test')->first();
    }

    private function makeArticle(array $overrides = []): Article
    {
        return Article::create(array_merge([
            'title' => 'Contoh Artikel',
            'slug' => 'contoh-artikel',
            'excerpt' => 'Ringkasan.',
            'body' => '<p>Isi artikel.</p>',
            'author_id' => $this->admin()->id,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_blog_index_shows_published_only(): void
    {
        $this->makeArticle(['title' => 'Pantai Terbaik Padang', 'slug' => 'pantai-terbaik']);
        $this->makeArticle(['title' => 'Naskah Draft Rahasia', 'slug' => 'draft-rahasia', 'status' => ArticleStatus::Draft, 'published_at' => null]);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Pantai Terbaik Padang')
            ->assertDontSee('Naskah Draft Rahasia');
    }

    public function test_draft_article_is_hidden_from_guests(): void
    {
        $draft = $this->makeArticle(['slug' => 'draft-x', 'status' => ArticleStatus::Draft, 'published_at' => null]);

        $this->get(route('blog.show', $draft))->assertNotFound();
    }

    public function test_admin_can_preview_draft(): void
    {
        $draft = $this->makeArticle(['slug' => 'draft-y', 'status' => ArticleStatus::Draft, 'published_at' => null]);

        $this->actingAs($this->admin())->get(route('blog.show', $draft))->assertOk();
    }

    public function test_admin_can_create_article_with_sanitized_body_and_links(): void
    {
        $dest = Destination::first();

        $this->actingAs($this->admin())->post(route('admin.articles.store'), [
            'title' => 'Tips Liburan ke Padang',
            'excerpt' => 'Panduan singkat.',
            'body' => '<p>Halo dunia</p><script>alert(1)</script><a href="https://x.test">link</a>',
            'status' => 'published',
            'destinations' => [$dest->id],
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'tips-liburan-ke-padang')->first();
        $this->assertNotNull($article);

        // XSS stripped, legitimate markup kept.
        $this->assertStringNotContainsString('<script', $article->body);
        $this->assertStringContainsString('Halo dunia', $article->body);

        // Publishing sets published_at; related destination linked.
        $this->assertNotNull($article->published_at);
        $this->assertTrue($article->destinations()->whereKey($dest->id)->exists());
    }

    public function test_admin_can_toggle_publish(): void
    {
        $draft = $this->makeArticle(['slug' => 'toggle-me', 'status' => ArticleStatus::Draft, 'published_at' => null]);

        $this->actingAs($this->admin())->post(route('admin.articles.toggle-publish', $draft));

        $fresh = $draft->fresh();
        $this->assertSame(ArticleStatus::Published, $fresh->status);
        $this->assertNotNull($fresh->published_at);
    }

    public function test_article_admin_blocked_for_non_admins(): void
    {
        $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
        $this->actingAs($this->user())->get(route('admin.articles.index'))->assertForbidden();
    }

    public function test_sitemap_lists_published_article(): void
    {
        $pub = $this->makeArticle(['slug' => 'di-sitemap']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('blog.show', $pub), false);
    }
}
