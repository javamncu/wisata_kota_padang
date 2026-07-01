<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Wisata Alam', 'icon' => 'mountain', 'description' => 'Pantai, perbukitan, dan keindahan alam Kota Padang.'],
            ['name' => 'Wisata Sejarah & Budaya', 'icon' => 'landmark', 'description' => 'Bangunan tua, museum, dan jejak sejarah kota.'],
            ['name' => 'Wisata Religi', 'icon' => 'mosque', 'description' => 'Masjid dan tempat ibadah bersejarah.'],
            ['name' => 'Kuliner', 'icon' => 'utensils', 'description' => 'Sajian khas Minang dan kuliner legendaris Padang.'],
            ['name' => 'Belanja & Oleh-oleh', 'icon' => 'shopping-bag', 'description' => 'Pusat oleh-oleh dan pasar tradisional.'],
            ['name' => 'Rekreasi & Hiburan', 'icon' => 'ferris-wheel', 'description' => 'Tempat bersantai dan hiburan keluarga.'],
            ['name' => 'Mall', 'icon' => 'mall', 'description' => 'Pusat perbelanjaan modern di Kota Padang.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                ],
            );
        }
    }
}
