<?php

namespace Database\Seeders;

use App\Enums\TagType;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tagsByType = [
            TagType::Suasana->value => [
                'Santai', 'Ramai/hidup', 'Romantis', 'Instagramable',
                'Asri/sejuk', 'Klasik/bersejarah', 'Petualangan',
            ],
            TagType::Aktivitas->value => [
                'Foto-foto', 'Berenang', 'Hiking', 'Edukasi/sejarah',
                'Relaksasi', 'Ibadah', 'Belanja', 'Kulineran',
            ],
            TagType::Fasilitas->value => [
                'Parkir', 'Toilet', 'Mushola', 'Wifi',
                'Spot foto', 'Area anak', 'Ramah difabel',
            ],
        ];

        foreach ($tagsByType as $type => $names) {
            foreach ($names as $name) {
                Tag::updateOrCreate(
                    // Treat "/" as a word separator so "Ramai/hidup" -> "ramai-hidup"
                    // (Str::slug strips "/" entirely, which would give "ramaihidup").
                    ['slug' => Str::slug(str_replace('/', ' ', $name))],
                    ['name' => $name, 'type' => $type],
                );
            }
        }
    }
}
