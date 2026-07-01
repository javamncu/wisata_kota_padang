<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Realistic sample Q&A for the "Tanya Jawab" page so the live page looks
 * populated. Intentionally NOT wired into DatabaseSeeder — run it on demand
 * with `php artisan db:seed --class=QuestionSeeder` (keeps the test suite,
 * which seeds the base data, free of these rows).
 *
 * Idempotent: an entry is skipped if a question with the same text exists.
 */
class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::where('email', 'user@wisatapadang.test')->first();

        foreach ($this->entries() as $e) {
            if (Question::where('question', $e['question'])->exists()) {
                continue;
            }

            $fromUser = ($e['from_user'] ?? false) && $demoUser;
            $createdAt = Carbon::now()->subDays($e['days_ago'])->setTime(8 + ($e['days_ago'] % 10), ($e['days_ago'] * 7) % 60);
            $answeredAt = isset($e['answer'])
                ? (clone $createdAt)->addDays($e['answer_after'] ?? 1)->addHours(3)
                : null;

            $q = new Question([
                'user_id' => $fromUser ? $demoUser->id : null,
                'author_name' => $fromUser ? $demoUser->name : $e['name'],
                'question' => $e['question'],
                'answer' => $e['answer'] ?? null,
                'answered_at' => $answeredAt,
                'is_hidden' => false,
            ]);
            $q->created_at = $createdAt;
            $q->updated_at = $answeredAt ?? $createdAt;
            $q->save();
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function entries(): array
    {
        return [
            [
                'name' => 'Rina Andini',
                'days_ago' => 41,
                'answer_after' => 1,
                'question' => 'Pantai mana yang paling bagus untuk menikmati sunset di Padang?',
                'answer' => 'Untuk sunset, Pantai Padang (Taplau) jadi favorit karena mudah dijangkau dan banyak tempat makan di sepanjang pantainya. Kalau ingin suasana yang lebih tenang, Pantai Air Manis juga sangat indah saat senja.',
            ],
            [
                'name' => 'Fauzan Pratama',
                'days_ago' => 38,
                'answer_after' => 1,
                'question' => 'Dari Bandara Internasional Minangkabau ke pusat kota Padang sebaiknya naik apa yang praktis?',
                'answer' => 'Bisa naik bus DAMRI menuju pusat kota dengan tarif terjangkau, atau memesan taksi online. Kalau bepergian rombongan, menyewa travel/mobil lebih nyaman. Perjalanan biasanya sekitar 45–60 menit.',
            ],
            [
                'name' => 'Siti Aisyah',
                'days_ago' => 35,
                'answer_after' => 1,
                'question' => 'Apakah wisatawan non-muslim diperbolehkan masuk ke Masjid Raya Sumatera Barat?',
                'answer' => 'Diperbolehkan. Pengunjung umum dipersilakan masuk untuk melihat arsitekturnya yang khas Minangkabau. Mohon berpakaian sopan dan menjaga ketenangan, terutama saat waktu salat berlangsung.',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'days_ago' => 31,
                'answer_after' => 2,
                'question' => 'Ada rekomendasi tempat makan rendang yang enak di dekat Pantai Padang?',
                'answer' => 'Di sekitar kawasan Taplau banyak rumah makan Padang. Untuk yang sudah cukup terkenal, RM Lamun Ombak dan RM Sederhana bisa jadi pilihan untuk mencicipi rendang serta masakan Minang lainnya.',
            ],
            [
                'name' => 'Putri Maharani',
                'days_ago' => 28,
                'answer_after' => 1,
                'question' => 'Kalau mau ke Pulau Pasumpahan untuk snorkeling, sewa perahunya dari mana?',
                'answer' => 'Penyeberangan ke Pulau Pasumpahan umumnya dari Dermaga Sungai Pisang. Sebaiknya datang pagi dan menyewa perahu bersama rombongan supaya lebih hemat. Alat snorkeling bisa dibawa sendiri atau disewa di lokasi.',
            ],
            [
                'name' => 'Andre Saputra',
                'days_ago' => 24,
                'answer_after' => 1,
                'question' => 'Wisata apa saja yang cocok untuk anak-anak di Kota Padang?',
                'answer' => 'Untuk keluarga, Pantai Padang dan Taman Muaro Lasak cocok untuk bermain. Anak-anak juga biasanya senang berfoto di Jembatan Siti Nurbaya serta melihat legenda Malin Kundang di Pantai Air Manis.',
            ],
            [
                'name' => 'Yulia Sari',
                'days_ago' => 20,
                'answer_after' => 1,
                'question' => 'Toko oleh-oleh keripik balado yang recommended belinya di mana ya?',
                'answer' => 'Christine Hakim adalah salah satu yang paling populer untuk keripik balado dan sanjai. Selain itu, Keripik Balado Mahkota/Shirley juga banyak diburu wisatawan. Keduanya berada di pusat kota dan mudah dijangkau.',
            ],
            [
                'name' => 'Reza Firmansyah',
                'days_ago' => 16,
                'answer_after' => 1,
                'question' => 'Berapa lama waktu ideal untuk berkeliling kawasan Kota Tua Padang?',
                'answer' => 'Sekitar 2–3 jam biasanya cukup untuk menyusuri kawasan Kota Tua (Kampung Pondok), Kelenteng See Hin Kiong, hingga Jembatan Siti Nurbaya. Paling nyaman dijelajahi pada pagi atau sore hari.',
            ],
            [
                'name' => 'Nabila Husna',
                'days_ago' => 12,
                'answer_after' => 2,
                'question' => 'Bulan apa waktu terbaik berkunjung ke Padang supaya tidak sering kehujanan?',
                'answer' => 'Musim kemarau sekitar bulan Mei hingga September umumnya lebih bersahabat untuk wisata pantai dan pulau. Meski begitu, tetap siapkan jas hujan ringan karena cuaca pesisir kadang berubah cepat.',
            ],
            [
                'from_user' => true,
                'name' => 'Pengguna',
                'days_ago' => 9,
                'answer_after' => 1,
                'question' => 'Apakah Pantai Caroline ramai saat akhir pekan?',
                'answer' => 'Saat akhir pekan memang cenderung lebih ramai, terutama pada siang hari. Kalau ingin suasana yang lebih tenang, sebaiknya datang pagi hari atau pada hari kerja.',
            ],
            [
                'name' => 'Bayu Nugroho',
                'days_ago' => 3,
                'question' => 'Adakah rekomendasi penginapan yang nyaman dan tidak jauh dari Pantai Padang?',
            ],
            [
                'name' => 'Indah Permata',
                'days_ago' => 1,
                'question' => 'Apakah ada transportasi umum untuk menuju Pantai Air Manis dari pusat kota?',
            ],
        ];
    }
}
