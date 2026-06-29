<?php

namespace Database\Seeders;

use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\Status;
use App\Enums\WaktuIdeal;
use App\Enums\Zone;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DestinationSeeder extends Seeder
{
    /**
     * Tag keywords in the source JSON that map to an existing canonical
     * tag slug (created by TagSeeder). Anything not listed is matched by
     * Str::slug() and created on the fly if missing.
     */
    private const TAG_ALIASES = [
        'suasana' => ['ramai' => 'ramai-hidup', 'klasik' => 'klasik-bersejarah', 'asri' => 'asri-sejuk'],
        'aktivitas' => ['foto' => 'foto-foto', 'edukasi' => 'edukasi-sejarah'],
        'fasilitas' => [],
    ];

    /**
     * Coordinates (lat, lng) per destination — keyed by name. The JSON has
     * none, so they live here; all 41 destinations are covered.
     */
    private const COORDS = [
        'Pantai Padang (Taplau)' => [-0.960600, 100.353900],
        'Pantai Air Manis' => [-1.008611, 100.355556],
        'Jembatan Siti Nurbaya' => [-0.949900, 100.361800],
        'Masjid Raya Sumatera Barat' => [-0.924300, 100.360100],
        'Museum Adityawarman' => [-0.933300, 100.355600],
        'Pulau Pasumpahan' => [-1.083300, 100.366700],
        'Christine Hakim' => [-0.948000, 100.358000],
        'RM Lamun Ombak' => [-0.913000, 100.357000],
        'Masjid Al Hakim' => [-0.947000, 100.353000],
        'Plaza Andalas' => [-0.948500, 100.360500],
        'Lapangan Imam Bonjol' => [-0.945500, 100.359500],
        'Air Terjun Lubuk Hitam' => [-1.042502, 100.413728],
        'Bukit Nobita' => [-0.963473, 100.416390],
        'Lubuk Paraku' => [-0.942701, 100.468234],
        'Pantai Caroline (Carolina)' => [-1.025357, 100.413490],
        'Pantai Nirwana' => [-0.995960, 100.395781],
        'Pantai Pasir Jambak' => [-0.835158, 100.316828],
        'Pulau Sikuai' => [-1.031575, 100.353342],
        'Pulau Sirandah' => [-1.037142, 100.334759],
        'Sitinjau Lauik' => [-0.946353, 100.460835],
        'Kelenteng See Hin Kiong' => [-0.962040, 100.364425],
        'Kota Tua Padang (Kampung Pondok / Kampung Cina)' => [-0.961633, 100.364943],
        'Taman Budaya Sumatera Barat' => [-0.950793, 100.354178],
        'Taman Muaro Lasak & Monumen Merpati Perdamaian' => [-0.932971, 100.351221],
        'Masjid Agung Nurul Iman' => [-0.956790, 100.364746],
        'Miniatur Makkah' => [-0.849921, 100.383125],
        'Es Durian Iko Gantinyo' => [-0.958739, 100.361421],
        'Katupek Pitalah Purus 3' => [-0.939223, 100.353328],
        'Martabak Kubang Hayuda' => [-0.950942, 100.363920],
        'Pondok Ikan Bakar Khatib Sulaiman' => [-0.923891, 100.357121],
        'RM Pagi Sore' => [-0.952541, 100.358514],
        'RM Sederhana' => [-0.941915, 100.361512],
        'Sate Manang Kabau' => [-0.926298, 100.358245],
        'Soto Garuda' => [-0.916942, 100.356428],
        'Warung Kopi Nan Yo' => [-0.959828, 100.361128],
        'Keripik Balado Mahkota / Shirley' => [-0.954739, 100.354628],
        'Pasar Raya Padang' => [-0.952119, 100.364421],
        'Pusat Oleh-Oleh Ummi Aufa Hakim' => [-0.939281, 100.357642],
        'Silungkang Art Centre' => [-0.955512, 100.364121],
        'Skywalk Pantai Air Manis' => [-0.970512, 100.367121],
        'Taman Siti Nurbaya' => [-0.963234, 100.350842],
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $items = json_decode(
            file_get_contents(database_path('data/destinasi-padang.json')),
            true,
        );

        foreach ($items as $item) {
            $categoryId = $categories[$item['category']] ?? null;

            if ($categoryId === null) {
                $this->command?->warn("Kategori tidak ditemukan: {$item['category']} ({$item['name']})");
                continue;
            }

            $attributes = [
                'category_id' => $categoryId,
                'name' => $item['name'],
                'description_short' => $item['description_short'],
                'description_long' => $item['description_short'],
                'address' => 'Kota Padang, Sumatera Barat',
                'price_range' => $this->enumValue(PriceRange::class, $item['price_range']),
                'zone' => $this->enumValue(Zone::class, $item['zone']),
                'indoor_outdoor' => $this->enumValue(IndoorOutdoor::class, $item['indoor_outdoor']),
                'duration' => $this->enumValue(Duration::class, $item['duration']),
                'cocok_untuk' => array_map(fn ($v) => $this->enumValue(CocokUntuk::class, $v), $item['cocok_untuk']),
                'waktu_ideal' => array_map(fn ($v) => $this->enumValue(WaktuIdeal::class, $v), $item['waktu_ideal']),
                'status' => Status::Aktif->value,
            ];

            // Only set coordinates when known — don't overwrite existing ones with null.
            if (isset(self::COORDS[$item['name']])) {
                [$attributes['latitude'], $attributes['longitude']] = self::COORDS[$item['name']];
            }

            $destination = Destination::updateOrCreate(
                ['slug' => Str::slug($item['name'])],
                $attributes,
            );

            $destination->tags()->sync($this->resolveTagIds($item['tags']));
        }
    }

    /**
     * Resolve the case value of an enum from a human label (with fallbacks
     * for enums whose label differs from the backing value, e.g. Duration).
     */
    private function enumValue(string $enumClass, string $label): string
    {
        foreach ($enumClass::cases() as $case) {
            if ($case->label() === $label) {
                return $case->value;
            }
        }

        $case = $enumClass::tryFrom(strtolower($label))
            ?? $enumClass::tryFrom(Str::slug($label, '_'));

        if ($case === null) {
            throw new \RuntimeException("Tidak bisa memetakan '{$label}' ke {$enumClass}");
        }

        return $case->value;
    }

    /**
     * @param  array<string, string[]>  $tagsByType
     * @return int[]
     */
    private function resolveTagIds(array $tagsByType): array
    {
        $ids = [];

        foreach ($tagsByType as $type => $keywords) {
            foreach ($keywords as $keyword) {
                $slug = self::TAG_ALIASES[$type][$keyword] ?? Str::slug($keyword);

                $tag = Tag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::title(str_replace('-', ' ', $slug)), 'type' => $type],
                );

                $ids[] = $tag->id;
            }
        }

        return $ids;
    }
}
