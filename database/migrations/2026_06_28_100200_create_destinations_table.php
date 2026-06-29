<?php

use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\Status;
use App\Enums\Zone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            // Core fields
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description_short');
            $table->longText('description_long');
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('opening_hours')->nullable();
            $table->string('price_info')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_instagram')->nullable();
            $table->string('contact_website')->nullable();

            // Single-value enum attributes (locked in code)
            $table->enum('price_range', PriceRange::values())->index();
            $table->enum('zone', Zone::values())->index();
            $table->enum('indoor_outdoor', IndoorOutdoor::values())->index();
            $table->enum('duration', Duration::values())->index();

            // Multi-value attributes stored as JSON (whereJsonContains)
            $table->json('cocok_untuk')->nullable();
            $table->json('waktu_ideal')->nullable();

            // Publication state
            $table->enum('status', Status::values())->default(Status::Draft->value)->index();

            // Derived rating cache (computed from reviews)
            $table->decimal('rating_cache', 3, 2)->nullable();
            $table->unsignedInteger('review_count_cache')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
