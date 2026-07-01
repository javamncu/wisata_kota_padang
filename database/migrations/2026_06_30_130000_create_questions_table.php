<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // Asker — null for guests; name is always stored for display.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->text('question');

            // Admin answer (null = belum dijawab).
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();

            // Admin moderation: hide spam/abuse from the public page.
            $table->boolean('is_hidden')->default(false)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
