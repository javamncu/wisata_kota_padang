<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionFlowTest extends TestCase
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

    public function test_tanya_jawab_page_loads_for_guests(): void
    {
        $this->get('/tanya-jawab')
            ->assertOk()
            ->assertSee('Tanya Jawab');
    }

    public function test_guest_can_ask_and_question_appears_publicly(): void
    {
        $this->post('/tanya-jawab', [
            'name' => 'Budi Wisatawan',
            'question' => 'Apakah ada rekomendasi pantai yang bagus di Kota Padang?',
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'author_name' => 'Budi Wisatawan',
            'user_id' => null,
            'answer' => null,
        ]);

        // Visible immediately (the user chose "langsung tampil").
        $this->get('/tanya-jawab')
            ->assertOk()
            ->assertSee('rekomendasi pantai yang bagus')
            ->assertSee('Belum dijawab');
    }

    public function test_logged_in_question_uses_account_name(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post('/tanya-jawab', [
            'question' => 'Jam berapa Masjid Raya biasanya ramai pengunjung?',
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'user_id' => $user->id,
            'author_name' => $user->name,
        ]);
    }

    public function test_honeypot_silently_blocks_spam(): void
    {
        $this->post('/tanya-jawab', [
            'name' => 'Spam Bot',
            'question' => 'Buy cheap things at spam dot com right now please.',
            'website' => 'http://spam.example.com',
        ])->assertRedirect();

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_validation_requires_name_and_a_real_question(): void
    {
        $this->post('/tanya-jawab', ['question' => 'pendek'])
            ->assertSessionHasErrors(['name', 'question']);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_admin_can_answer_and_answer_shows_publicly(): void
    {
        $question = Question::create([
            'author_name' => 'Tamu',
            'question' => 'Adakah wisata religi yang wajib dikunjungi?',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.questions.answer', $question), [
                'answer' => 'Tentu, kunjungi Masjid Raya Sumatera Barat.',
            ])->assertRedirect();

        $question->refresh();
        $this->assertNotNull($question->answer);
        $this->assertNotNull($question->answered_at);

        $this->get('/tanya-jawab')
            ->assertOk()
            ->assertSee('Masjid Raya Sumatera Barat');
    }

    public function test_admin_can_hide_question_from_public(): void
    {
        $question = Question::create([
            'author_name' => 'Tamu',
            'question' => 'Pertanyaan yang akan disembunyikan admin.',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.questions.toggle-hide', $question))
            ->assertRedirect();

        $this->assertTrue($question->refresh()->is_hidden);

        $this->get('/tanya-jawab')
            ->assertOk()
            ->assertDontSee('akan disembunyikan admin');
    }

    public function test_admin_questions_page_is_blocked_for_non_admins(): void
    {
        $this->get(route('admin.questions.index'))->assertRedirect(route('login'));
        $this->actingAs($this->user())->get(route('admin.questions.index'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.questions.index'))->assertOk();
    }
}
