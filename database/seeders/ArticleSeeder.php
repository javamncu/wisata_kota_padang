<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@wisatapadang.test')->first();

        if (! $author) {
            return;
        }

        foreach ($this->articles() as $data) {
            $destinationSlugs = $data['destinations'];
            unset($data['destinations']);

            // Cover image lives in public/images/blog/ (referenced in place).
            $cover = $data['cover'] ?? null;
            unset($data['cover']);
            $data['cover_image'] = $cover && file_exists(public_path('images/blog/'.$cover))
                ? 'images/blog/'.$cover
                : null;

            $data['slug'] = Str::slug($data['title']);
            $data['author_id'] = $author->id;
            $data['status'] = ArticleStatus::Published;
            $data['published_at'] = now();

            $article = Article::updateOrCreate(['slug' => $data['slug']], $data);

            $ids = Destination::whereIn('slug', $destinationSlugs)->pluck('id');
            $article->destinations()->sync($ids);
        }
    }

    private function articles(): array
    {
        return [
            [
                'title' => '5 Pantai Terbaik di Padang untuk Berburu Sunset',
                'cover' => 'pantai_terbaik.png',
                'excerpt' => 'Dari Taplau yang ikonik sampai Pulau Pasumpahan yang jernih — ini deretan pantai wajib kunjung di Padang.',
                'body' => '<p>Kota Padang dianugerahi garis pantai yang memesona. Berikut lima pantai favorit untuk menikmati matahari terbenam.</p>'
                    .'<h1>Pantai Padang (Taplau)</h1><p>Pantai di tepi pusat kota, ramai di sore hari dengan aneka jajanan khas Minang.</p>'
                    .'<h1>Pantai Air Manis</h1><p>Berlatar legenda Malin Kundang, cocok untuk keluarga.</p>'
                    .'<h1>Pulau Pasumpahan</h1><p>Dijuluki "Maldives-nya Padang", surga snorkeling.</p>'
                    .'<p>Selamat berlibur dan jangan lupa jaga kebersihan pantai!</p>',
                'meta_title' => '5 Pantai Terbaik di Padang',
                'meta_description' => 'Rekomendasi pantai terbaik di Kota Padang untuk menikmati sunset dan bermain air.',
                'destinations' => ['pantai-padang-taplau', 'pantai-air-manis', 'pulau-pasumpahan', 'pantai-nirwana'],
            ],
            [
                'title' => 'Kuliner Wajib Coba Saat Berkunjung ke Padang',
                'cover' => 'kuliner_wajib.png',
                'excerpt' => 'Rendang hanyalah awal. Jelajahi sate Padang, soto, hingga es durian legendaris kota ini.',
                'body' => '<p>Padang adalah surga kuliner. Saat berkunjung, sempatkan mencicipi sajian berikut.</p>'
                    .'<ul><li>Sate Padang dengan kuah kuning berempah</li><li>Soto Padang yang gurih</li><li>Es durian untuk penutup yang segar</li></ul>'
                    .'<p>Selamat berwisata kuliner!</p>',
                'meta_title' => 'Kuliner Wajib di Padang',
                'meta_description' => 'Daftar kuliner khas Padang yang wajib dicoba wisatawan.',
                'destinations' => ['sate-manang-kabau', 'soto-garuda', 'es-durian-iko-gantinyo', 'rm-lamun-ombak'],
            ],
            [
                'title' => 'Menyusuri Wisata Religi & Sejarah Kota Padang',
                'cover' => 'wisata_kuliner.png',
                'excerpt' => 'Masjid Raya yang ikonik, Kota Tua, hingga Museum Adityawarman — jejak budaya Minangkabau.',
                'body' => '<p>Selain alam dan kuliner, Padang kaya akan situs religi dan sejarah.</p>'
                    .'<h1>Masjid Raya Sumatera Barat</h1><p>Berarsitektur gonjong khas Minangkabau tanpa kubah.</p>'
                    .'<h1>Museum Adityawarman</h1><p>Menyimpan ribuan koleksi budaya Minangkabau.</p>',
                'meta_title' => 'Wisata Religi & Sejarah Padang',
                'meta_description' => 'Panduan wisata religi dan sejarah di Kota Padang.',
                'destinations' => ['masjid-raya-sumatera-barat', 'museum-adityawarman', 'kota-tua-padang-kampung-pondok-kampung-cina'],
            ],
        ];
    }
}
