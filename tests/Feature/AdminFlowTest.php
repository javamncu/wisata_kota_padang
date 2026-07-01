<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Enums\Role;
use App\Enums\Status;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminFlowTest extends TestCase
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

    public function test_admin_area_is_blocked_for_non_admins(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $this->actingAs($this->user())->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'Agrowisata',
                'description' => 'Kebun dan agro.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'agrowisata', 'is_active' => 1]);
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $category = Destination::active()->first()->category;

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_admin_can_create_destination_with_tags_and_on_the_fly_tag(): void
    {
        $category = Category::first();
        $existingTag = Tag::first();

        $this->actingAs($this->admin())
            ->post(route('admin.destinations.store'), [
                'category_id' => $category->id,
                'name' => 'Taman Uji Coba',
                'description_short' => 'Singkat.',
                'description_long' => 'Panjang sekali deskripsinya.',
                'address' => 'Jl. Test',
                'price_range' => 'gratis',
                'zone' => 'pusat_kota',
                'city' => 'padang',
                'indoor_outdoor' => 'outdoor',
                'duration' => 'singkat',
                'status' => 'aktif',
                'cocok' => ['keluarga', 'solo'],
                'waktu' => ['pagi'],
                'tags' => [$existingTag->id],
                'new_suasana' => 'Tag Baru Uji, Tag Lain',
                'opening_hours' => "Setiap hari: 08:00 - 17:00",
            ])
            ->assertRedirect(route('admin.destinations.index'));

        $destination = Destination::where('slug', 'taman-uji-coba')->first();
        $this->assertNotNull($destination);
        $this->assertEqualsCanonicalizing(['keluarga', 'solo'], $destination->cocok_untuk->map->value->all());
        $this->assertDatabaseHas('tags', ['slug' => 'tag-baru-uji', 'type' => 'suasana']);
        // existing + 2 new tags attached
        $this->assertSame(3, $destination->tags()->count());
        $this->assertSame(['Setiap hari' => '08:00 - 17:00'], $destination->opening_hours);
    }

    public function test_admin_can_upload_destination_image(): void
    {
        $category = Category::first();

        $this->actingAs($this->admin())->post(route('admin.destinations.store'), [
            'category_id' => $category->id,
            'name' => 'Galeri Test',
            'description_short' => 'Singkat.',
            'description_long' => 'Panjang.',
            'address' => 'Jl. Foto',
            'price_range' => 'murah',
            'zone' => 'pesisir',
            'city' => 'padang',
            'indoor_outdoor' => 'outdoor',
            'duration' => 'sedang',
            'status' => 'aktif',
            'images' => [UploadedFile::fake()->image('foto.jpg')],
        ])->assertRedirect();

        $destination = Destination::where('slug', 'galeri-test')->first();
        $this->assertSame(1, $destination->images()->count());

        // Images are stored directly under public/, not the storage disk.
        $path = $destination->images->first()->path;
        $this->assertStringStartsWith('images/destinations/', $path);
        $this->assertFileExists(public_path($path));

        @unlink(public_path($path)); // cleanup the file created during the test
    }

    public function test_admin_can_toggle_destination_status(): void
    {
        $destination = Destination::active()->first();

        $this->actingAs($this->admin())
            ->post(route('admin.destinations.toggle-status', $destination));

        $this->assertSame(Status::Draft, $destination->fresh()->status);
    }

    public function test_admin_can_approve_review_and_rating_updates(): void
    {
        $destination = Destination::active()->first();
        $review = Review::create([
            'user_id' => $this->user()->id,
            'destination_id' => $destination->id,
            'rating' => 5,
            'status' => ReviewStatus::Pending,
        ]);

        $this->assertNull($destination->fresh()->rating_cache);

        $this->actingAs($this->admin())
            ->patch(route('admin.reviews.approve', $review));

        $this->assertSame(ReviewStatus::Published, $review->fresh()->status);
        $this->assertEquals(5.0, (float) $destination->fresh()->rating_cache);
    }

    public function test_admin_can_change_role_but_not_own(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        // promote a user
        $this->actingAs($admin)
            ->patch(route('admin.users.role', $user), ['role' => 'admin'])
            ->assertSessionHasNoErrors();
        $this->assertSame(Role::Admin, $user->fresh()->role);

        // cannot change own role
        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertSessionHas('error');
        $this->assertSame(Role::Admin, $admin->fresh()->role);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = $this->user();
        $user->update(['is_active' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_about_page_loads(): void
    {
        $this->get('/tentang')->assertOk();
    }

    public function test_admin_can_update_settings_and_site_reflects(): void
    {
        $this->actingAs($this->admin())->get(route('admin.settings.edit'))->assertOk();

        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'site_name' => 'Padang Tourism',
            'hero_title' => 'Selamat datang di Padang',
            'per_page' => 8,
            'default_sort' => 'rating',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('Padang Tourism', \App\Support\Settings::get('site_name'));

        // Hero title is wired into the home page.
        $this->get('/')->assertSee('Selamat datang di Padang');
    }

    public function test_settings_blocked_for_non_admin(): void
    {
        $this->actingAs($this->user())->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_settings_validation_rejects_bad_values(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'site_name' => '',
            'hero_title' => 'x',
            'per_page' => 999,
            'default_sort' => 'bogus',
        ])->assertSessionHasErrors(['site_name', 'per_page', 'default_sort']);
    }

    public function test_all_admin_pages_render(): void
    {
        $admin = $this->admin();
        $category = Category::first();
        $tag = Tag::first();
        $destination = Destination::first();

        $pages = [
            route('admin.dashboard'),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
            route('admin.tags.index'),
            route('admin.tags.create'),
            route('admin.tags.edit', $tag),
            route('admin.destinations.index'),
            route('admin.destinations.create'),
            route('admin.destinations.edit', $destination),
            route('admin.users.index'),
            route('admin.reviews.index'),
            route('admin.settings.edit'),
            route('admin.articles.index'),
            route('admin.articles.create'),
            route('admin.articles.edit', \App\Models\Article::first()),
        ];

        foreach ($pages as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_user_pages_render(): void
    {
        $this->actingAs($this->user());

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('favorites.index'))->assertOk();
        $this->get(route('reviews.mine'))->assertOk();
    }
}
