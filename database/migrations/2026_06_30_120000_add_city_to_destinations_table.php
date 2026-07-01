<?php

use App\Enums\City;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            // City the destination belongs to. Existing rows default to Padang
            // (the app started as a Padang-only directory). Kept as a plain
            // indexed string — the App\Enums\City cast enforces valid values.
            $table->string('city')
                ->default(City::Padang->value)
                ->after('zone')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropColumn('city');
        });
    }
};
