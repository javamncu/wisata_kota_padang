<?php

namespace Database\Seeders;

use App\Enums\City;
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
        'Plaza Andalas' => [-0.949673, 100.362153],
        'Lapangan Imam Bonjol' => [-0.945500, 100.359500],
        // Mall
        'Basko Grand Mall' => [-0.914562, 100.349831],
        'Transmart Padang' => [-0.922158, 100.360142],
        'Living Plaza Padang' => [-0.945821, 100.359142],
        'Damar Plaza' => [-0.945512, 100.358914],
        // Gereja (Wisata Religi)
        'Gereja Katedral Santa Theresia (Katedral Padang)' => [-0.956128, 100.357142],
        'GPIB Efrata Padang (Gereja Ayam)' => [-0.952512, 100.362121],
        'Gereja Katolik Santo Fransiskus Asisi' => [-0.941512, 100.362142],
        'Kapel St. Leo' => [-0.956512, 100.356914],
        'Gereja Advent Padang' => [-0.957512, 100.354128],
        // Masjid (Wisata Religi)
        'Masjid Raya Ganting' => [-0.954201, 100.372512],
        'Masjid Muhammadan' => [-0.964128, 100.366421],
        'Masjid Tuo Kayu Jao' => [-0.984121, 100.635821],
        'Masjid Taqwa Muhammadiyah' => [-0.951512, 100.363914],
        'Masjid Raya Andalas' => [-0.942128, 100.384142],
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
        // Bukittinggi
        'Jam Gadang' => [-0.305141, 100.369460],
        'Ngarai Sianok' => [-0.307739, 100.363842],
        'Lubang Jepang (Goa Jepang)' => [-0.308253, 100.364371],
        'Benteng Fort de Kock' => [-0.301382, 100.368845],
        'Taman Margasatwa & Budaya Kinantan' => [-0.302312, 100.367462],
        'Taman Panorama' => [-0.307689, 100.363912],
        'Jembatan Limpapeh' => [-0.301732, 100.367851],
        'Janjang Koto Gadang (Great Wall)' => [-0.304581, 100.358242],
        'Pasar Atas Bukittinggi' => [-0.304412, 100.369121],
        'Nasi Kapau Uni Lis' => [-0.304128, 100.369842],
        // Padang Panjang
        'PDIKM (Pusat Dokumentasi Kebudayaan Minangkabau)' => [-0.470512, 100.395121],
        'Rumah Puisi Taufiq Ismail' => [-0.455821, 100.384142],
        'Lubuk Mata Kucing' => [-0.473912, 100.388242],
        'Minang Fantasy Water Park (Mifan)' => [-0.469142, 100.394121],
        'Masjid Asasi Nagari Gunung' => [-0.444128, 100.411121],
        'Bukit Tui' => [-0.479142, 100.402128],
        'ISI Padang Panjang' => [-0.465128, 100.404142],
        'Pasar Padang Panjang' => [-0.463121, 100.414128],
        'Air Terjun Lembah Anai' => [-0.484128, 100.334142],
        'Desa Wisata Kubu Gadang' => [-0.462121, 100.428121],
        // Pariaman
        'Pantai Gandoriah' => [-0.625121, 100.114142],
        'Pulau Angso Duo' => [-0.619142, 100.088121],
        'Pantai Kata' => [-0.648128, 100.124142],
        'Pantai Cermin' => [-0.632121, 100.116128],
        'Tugu Tabuik Pariaman' => [-0.624142, 100.117121],
        'Konservasi Penyu Pariaman' => [-0.598128, 100.112142],
        'Talao Pauah' => [-0.612121, 100.114128],
        'Hutan Mangrove Apar' => [-0.594142, 100.111121],
        'Sala Lauak Pariaman' => [-0.625412, 100.113912],
        'Nasi Sek Pariaman' => [-0.626121, 100.114242],
        // Payakumbuh
        'Lembah Harau' => [-0.108142, 100.662121],
        'Kelok Sembilan (Kelok 9)' => [-0.068121, 100.754142],
        'Ngalau Indah' => [-0.237121, 100.617142],
        'Kapalo Banda Taram' => [-0.211142, 100.704121],
        'Panorama Ampangan' => [-0.264128, 100.654121],
        'Batang Tabik' => [-0.262121, 100.672142],
        'Medan nan Bapaneh Ratapan Ibu' => [-0.224142, 100.634121],
        'Puncak Marajo' => [-0.236128, 100.615142],
        'Sate Danguang-Danguang' => [-0.222121, 100.631128],
        'Pusat Oleh-oleh Batiah & Galamai' => [-0.223142, 100.632142],
        // Sawahlunto
        'Museum Situs Lubang Tambang Mbah Soero' => [-0.681121, 100.778142],
        'Museum Kereta Api Sawahlunto' => [-0.679142, 100.776121],
        'Museum Goedang Ransoem' => [-0.684128, 100.779142],
        'Museum Tambang Ombilin (Info Box)' => [-0.680121, 100.775128],
        'Danau Kandih' => [-0.628142, 100.754121],
        'Puncak Cemara Sawahlunto' => [-0.685121, 100.785142],
        'Kota Tua Sawahlunto' => [-0.681142, 100.777121],
        'Waterboom Sawahlunto' => [-0.654128, 100.762142],
        'Kebun Binatang Kandi (Kandi Resort)' => [-0.631121, 100.751128],
        'Masjid Agung Nurul Islam Sawahlunto' => [-0.682128, 100.778142],
        // Solok
        'Danau Singkarak' => [-0.612142, 100.534121],
        'Danau Kembar (Diateh & Dibawah)' => [-1.012121, 100.714142],
        'Kebun Teh Alahan Panjang' => [-1.024128, 100.684121],
        'Danau Talang' => [-0.984121, 100.692142],
        'Pulau Belibis' => [-0.791142, 100.641121],
        'Dermaga Singkarak' => [-0.598121, 100.528142],
        'Air Terjun Sarasah Batimpo' => [-0.804128, 100.664121],
        'Puncak Gagoan' => [-0.654121, 100.502142],
        'Agrowisata Alahan Panjang' => [-1.018142, 100.694121],
        'Pasar Raya Solok' => [-0.794121, 100.655128],
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        // Padang is the original dataset; the Sumbar file adds the 6 other
        // cities. Both share the same shape (Sumbar entries carry a "city").
        $items = [];
        foreach (['data/destinasi-padang.json', 'data/destinasi-sumbar.json'] as $file) {
            $path = database_path($file);
            if (is_file($path)) {
                $items = array_merge($items, json_decode(file_get_contents($path), true) ?? []);
            }
        }

        foreach ($items as $item) {
            $categoryId = $categories[$item['category']] ?? null;

            if ($categoryId === null) {
                $this->command?->warn("Kategori tidak ditemukan: {$item['category']} ({$item['name']})");
                continue;
            }

            // City is optional in the JSON; anything without one is Padang.
            $cityValue = isset($item['city'])
                ? $this->enumValue(City::class, $item['city'])
                : City::Padang->value;

            $attributes = [
                'category_id' => $categoryId,
                'name' => $item['name'],
                'description_short' => $item['description_short'],
                'description_long' => $item['description_short'],
                'address' => 'Kota '.City::from($cityValue)->label().', Sumatera Barat',
                'price_range' => $this->enumValue(PriceRange::class, $item['price_range']),
                'zone' => $this->enumValue(Zone::class, $item['zone']),
                'city' => $cityValue,
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
