<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Concierge\ConciergeUsage;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConciergeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['concierge.gemini.key' => 'test-key']);
        // Avoid a real ListModels HTTP call — seed the model list cache.
        Cache::put('concierge:models', ['gemini-2.5-flash', 'gemini-2.0-flash'], now()->addHour());
    }

    private function admin(): User
    {
        return User::where('email', 'admin@wisatapadang.test')->first();
    }

    private function user(): User
    {
        return User::where('email', 'user@wisatapadang.test')->first();
    }

    private function fakeGenerate(int $status = 200): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => json_encode(['category' => 'kuliner'])]]]]],
            ], $status),
        ]);
    }

    public function test_page_renders_for_admin_and_blocks_others(): void
    {
        $this->actingAs($this->admin())->get(route('admin.concierge.index'))
            ->assertOk()
            ->assertSee('Pilih Model Gemini');

        $this->actingAs($this->user())->get(route('admin.concierge.index'))->assertForbidden();
    }

    public function test_admin_can_select_model_and_it_persists(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.concierge.update'), ['concierge_model' => 'gemini-2.0-flash'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('gemini-2.0-flash', Settings::get('concierge_model'));
    }

    public function test_invalid_model_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->put(route('admin.concierge.update'), ['concierge_model' => 'gpt-4'])
            ->assertSessionHasErrors('concierge_model');
    }

    public function test_selected_model_is_used_by_concierge_and_usage_increments(): void
    {
        Settings::setMany(['concierge_model' => 'gemini-2.0-flash']);
        $this->fakeGenerate();

        $this->assertSame(0, ConciergeUsage::used('gemini-2.0-flash'));

        $this->postJson('/asisten/tanya', ['message' => 'kuliner enak'])->assertOk();

        // The chosen model was actually called...
        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-2.0-flash:generateContent'));
        // ...and usage was recorded.
        $this->assertSame(1, ConciergeUsage::used('gemini-2.0-flash'));
    }

    public function test_check_endpoint_reports_available(): void
    {
        $this->fakeGenerate(200);

        $this->actingAs($this->admin())
            ->postJson(route('admin.concierge.check'), ['model' => 'gemini-2.5-flash'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_check_endpoint_reports_limit(): void
    {
        $this->fakeGenerate(429);

        $this->actingAs($this->admin())
            ->postJson(route('admin.concierge.check'), ['model' => 'gemini-2.5-flash'])
            ->assertOk()
            ->assertJson(['ok' => false, 'status' => 429]);
    }
}
