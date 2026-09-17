# AI Concierge — Cara Kerja & Panduan Implementasi

Dokumen ini menjelaskan bagaimana fitur **AI Concierge** bekerja pada aplikasi
Wisata Kota Padang (Laravel 12 + Gemini API), termasuk **halaman admin untuk
memilih model Gemini**.

Ditulis agar bisa dipakai sebagai **acuan implementasi di project lain** —
polanya tidak terikat pada domain wisata dan bisa diadaptasi ke direktori/katalog
apa pun (properti, restoran, produk, lowongan kerja, dll).

---

## 1. Ide Inti (paling penting)

> **LLM tidak pernah mencari, tidak pernah mengarang jawaban.
> LLM hanya menerjemahkan kalimat bebas menjadi filter pencarian terstruktur.**

Semua hasil rekomendasi diambil dari **database sendiri** lewat mesin pencarian
yang sudah ada. Jadi alurnya:

```
"kuliner enak untuk keluarga di Bukittinggi, jangan sate"
                      │
                      ▼
        LLM (Gemini) → ekstrak JSON filter
                      │
                      ▼
   { category: "kuliner", city: "bukittinggi",
     cocok_untuk: ["keluarga"], exclude: ["sate"] }
                      │
                      ▼
        Validasi terhadap allow-list (buang nilai asing)
                      │
                      ▼
        Mesin pencarian existing (Eloquent query)
                      │
                      ▼
        Hasil = baris database asli (bukan karangan AI)
```

### Kenapa desain ini?

| Manfaat | Penjelasan |
|---|---|
| **Anti-halusinasi** | AI tidak bisa menyebut tempat yang tidak ada — hasil selalu dari DB |
| **Murah** | 1 pertanyaan = 1 panggilan API, output cuma ±80 token (JSON kecil) |
| **Cepat** | Tidak perlu LLM menulis paragraf panjang; kalimat pembuka dari template |
| **Tidak terkunci vendor** | Ganti provider = ganti 1 adapter; sisanya kode sendiri |
| **Tetap hidup saat AI mati** | Ada fallback keyword lokal kalau API gagal |
| **Nilai produk tetap milikmu** | Yang berharga adalah data + mesin pencarian, bukan model AI-nya |

---

## 2. Peta Komponen

```
app/
├── Services/Concierge/
│   ├── ConciergeService.php    ← Otak: extract → validasi → search → reply
│   ├── Vocabulary.php          ← Allow-list + validasi hasil LLM
│   ├── GeminiModels.php        ← List model & probe status (untuk admin)
│   └── ConciergeUsage.php      ← Hitung pemakaian harian per model
│
├── Services/Search/            ← Mesin pencarian (dipakai bersama fitur lain)
│   ├── SearchCriteria.php      ← DTO filter (readonly)
│   └── DestinationSearch.php   ← Bangun query Eloquent dari SearchCriteria
│
└── Http/Controllers/
    ├── ConciergeController.php        ← Publik: halaman chat + endpoint tanya
    └── Admin/ConciergeController.php  ← Admin: pilih model, cek status

config/concierge.php                   ← Provider, model, caps, rate limit
resources/views/public/concierge.blade.php   ← UI chat (Alpine.js)
resources/views/admin/concierge.blade.php    ← UI pilih model (Alpine.js)
tests/Feature/ConciergeTest.php              ← Test dengan Http::fake()
```

**Prinsip pemisahan:** `Services/Search/` **tidak tahu apa-apa soal AI**. Ia
dipakai juga oleh halaman Explore, kategori, dan kuis. AI Concierge hanya
"pengguna lain" dari mesin yang sama. Ini yang membuat hasil AI konsisten dengan
hasil pencarian manual.

---

## 3. Alur Lengkap Satu Pertanyaan

### 3.1 Request masuk

`POST /asisten/tanya` dengan body `{ "message": "..." }`

```php
// ConciergeController::ask()
$request->validate(['message' => ['required', 'string', 'max:500']]);

if ($limit = $this->rateLimited($request)) {
    return $limit;   // 429 kalau kuota harian user habis
}

$result = $concierge->answer($request->input('message'));
```

### 3.2 Ekstraksi oleh LLM

`ConciergeService::extract()` memanggil Gemini:

```php
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

$payload = [
    'system_instruction' => ['parts' => [['text' => $this->systemPrompt($vocab)]]],
    'contents' => [['role' => 'user', 'parts' => [['text' => $message]]]],
    'generationConfig' => [
        'temperature' => 0.1,                  // deterministik — ini tugas ekstraksi
        'responseMimeType' => 'application/json', // paksa output JSON
    ],
];

$response = Http::timeout(20)->withQueryParameters(['key' => $key])->post($url, $payload);
$text = data_get($response->json(), 'candidates.0.content.parts.0.text');
$text = trim(preg_replace('/^```(json)?|```$/m', '', $text)); // buang code fence
$decoded = json_decode($text, true);
```

**Detail penting:**
- `temperature: 0.1` — kita mau konsisten, bukan kreatif. Pertanyaan sama →
  hasil sama (ini juga yang membuat hasilnya bisa di-cache).
- `responseMimeType: application/json` — Gemini dipaksa keluarkan JSON valid.
- Tetap **strip code fence** untuk jaga-jaga, karena model kadang membungkus
  jawaban dengan ` ```json `.
- Kalau gagal parse → `return null` → jatuh ke fallback keyword (§ 3.7).

### 3.3 System prompt (kontrak dengan LLM)

```
Kamu adalah asisten aplikasi direktori wisata Kota Padang. Tugasmu HANYA
menerjemahkan pertanyaan pengguna menjadi filter pencarian berbentuk JSON.
JANGAN mengarang tempat. JANGAN menulis teks lain selain JSON.

Kembalikan HANYA JSON dengan struktur ini:
{
  "off_topic": boolean,
  "keyword": string|null,
  "category": string|null,
  "city": string|null,
  "zone": string|null,
  "price_range": string|null,
  "indoor_outdoor": string|null,
  "duration": string|null,
  "cocok_untuk": [string],
  "waktu_ideal": [string],
  "tags": [string],
  "exclude": [string]
}

Untuk semua field selain "keyword" dan "exclude", gunakan HANYA nilai dari
daftar di bawah. Jika ragu, kosongkan (null atau []). Kata/istilah bebas yang
tidak ada di daftar (mis. nama makanan, "pedas", "viral", nama tempat spesifik)
taruh di "keyword".

PENTING soal negasi: jika pengguna menyebut sesuatu yang TIDAK diinginkan
(mis. "jangan sate", "tidak ingin sate", "selain seafood", "bukan yang ramai"),
masukkan kata intinya ke "exclude" (mis. ["sate"]). JANGAN memasukkan kata yang
dinegasikan itu ke "keyword".

Daftar nilai yang sah:
{daftar allow-list di-generate dari DB/enum}

Set "off_topic": true HANYA jika pertanyaan jelas tidak berkaitan dengan
wisata/kuliner/tempat di Kota Padang; saat itu kosongkan field lainnya.
```

Daftar allow-list dibangun otomatis oleh `Vocabulary::forPrompt()`:

```
category (pilih satu slug): wisata-alam, kuliner, wisata-religi, mall, ...
city (kota — pilih satu slug jika pengguna menyebut nama kota): padang (Padang), bukittinggi (Bukittinggi), ...
zone: pusat_kota, pesisir, selatan, kepulauan, perbukitan
price_range: gratis, murah, sedang, premium
indoor_outdoor: indoor, outdoor, campuran
duration: singkat, sedang, lama
cocok_untuk (boleh beberapa): keluarga, pasangan, solo, rombongan, lansia, difabel
waktu_ideal (boleh beberapa): pagi, siang, sore, malam
tags (suasana): santai, ramai-hidup, klasik-bersejarah, asri-sejuk, ...
tags (aktivitas): kulineran, foto-foto, berenang, hiking, ...
tags (fasilitas): parkir, toilet, mushola, area-anak, ...
```

> **Karena di-generate dari database**, menambah kategori/kota/tag baru lewat
> panel admin otomatis membuat AI mengenalinya — tanpa ubah kode.

### 3.4 Guardrail: validasi hasil LLM

Ini lapisan keamanan utama. **Apa pun yang dikembalikan LLM di luar allow-list
akan dibuang diam-diam.**

```php
// Vocabulary::toCriteria()
return new SearchCriteria(
    keyword:        $this->cleanKeyword($raw['keyword'] ?? null),        // bebas, dipotong 100 char
    category:       $this->categories->keys()->contains($raw['category'] ?? null) ? $raw['category'] : null,
    city:           in_array($raw['city'] ?? null, City::values(), true) ? $raw['city'] : null,
    zones:          $this->only($raw['zone'] ?? null, Zone::values()),
    priceRanges:    $this->only($raw['price_range'] ?? null, PriceRange::values()),
    indoorOutdoor:  $this->only($raw['indoor_outdoor'] ?? null, IndoorOutdoor::values()),
    durations:      $this->only($raw['duration'] ?? null, Duration::values()),
    cocokUntuk:     $this->onlyMany($raw['cocok_untuk'] ?? [], CocokUntuk::values()),
    waktuIdeal:     $this->onlyMany($raw['waktu_ideal'] ?? [], WaktuIdeal::values()),
    tags:           $this->onlyMany($raw['tags'] ?? [], $this->tags->keys()->all()),
    excludeKeywords: $this->cleanExclude($raw['exclude'] ?? []),          // bebas, max 5 istilah
);
```

Contoh: LLM mengembalikan `category: "hotel"` (tidak ada di sistem) →
di-drop jadi `null`. Query tetap jalan, hanya lebih longgar. **Tidak pernah
error, tidak pernah query kolom asing.**

### 3.5 Pencarian dengan pelonggaran bertahap (progressive relaxation)

Masalah nyata: pertanyaan yang terlalu spesifik sering menghasilkan 0 baris.
Solusinya, coba dari yang paling ketat lalu longgarkan:

```php
$variants = [
    $c,                                    // 1. persis semua kriteria
    /* 2. buang keyword bebas */           // keyword sering terlalu sempit
    /* 3. buang juga tags */
    /* 4. sisakan facet keras saja */      // category + city + zone + price
];

foreach ($variants as $i => $variant) {
    $results = $this->search->query($variant)->take($limit)->get();
    if ($results->isNotEmpty()) {
        return ['destinations' => $results, 'relaxed' => $i > 0];
    }
}
return ['destinations' => collect(), 'relaxed' => false]; // jujur: tidak ketemu
```

**Batasan keras yang TIDAK PERNAH dilonggarkan:**

| Facet | Alasan |
|---|---|
| **`city`** | Kalau user minta Bukittinggi, jangan pernah tampilkan tempat Padang |
| **`excludeKeywords`** | Kalau user bilang "jangan sate", jangan pernah tampilkan sate |

Kalau hasil didapat dari varian longgar (`relaxed = true`), balasannya jujur:
> *"Tidak ada yang persis cocok semua kriteria, tapi ini beberapa yang paling mendekati:"*

### 3.6 Penanganan negasi (exclude)

Ini bug nyata yang pernah terjadi: user menulis *"saya tidak ingin sate"*, tapi
LLM menaruh `"sate"` di `keyword` — akibatnya **Sate Manang Kabau justru muncul
paling atas**.

Perbaikannya dua sisi:

**a) Sisi prompt** — instruksi negasi eksplisit (lihat § 3.3).

**b) Sisi query** — `DestinationSearch::applyExclude()`:

```php
foreach ($c->excludeKeywords as $term) {
    $like = '%'.$term.'%';
    $query->whereNot(function (Builder $q) use ($like) {
        $q->where('name', 'like', $like)
          ->orWhere('description_short', 'like', $like)
          ->orWhere('description_long', 'like', $like);
    });
}
```

Dicek pada **nama dan deskripsi**, bukan cuma nama — supaya "warung yang menjual
sate" juga ikut tersaring.

### 3.7 Fallback saat LLM tidak tersedia

Kalau API key kosong, timeout, atau gagal parse — fitur **tetap berguna**:

```php
private function keywordFallback(string $message): Collection
{
    $stop = ['yang','untuk','dekat','dari','dan','atau','ada','mau','cari', ...];

    $words = collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($message)))
        ->filter(fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, $stop, true))
        ->unique()->take(6);

    return Destination::query()->active()
        ->where(fn ($q) => /* OR LIKE pada name + deskripsi */)
        ->orderByDesc('review_count_cache')
        ->take(6)->get();
}
```

Sederhana, tapi menjaga fitur tidak mati total saat Gemini down.

### 3.8 Balasan (tanpa panggilan LLM kedua)

Kalimat pembuka **dibuat dari template**, bukan AI — ini penghematan besar
(menghindari 1 panggilan API tambahan per pertanyaan):

```php
"Berikut {$count} rekomendasi Kuliner · di Bukittinggi · budget Murah yang mungkin cocok untukmu:"
```

Kasus khusus yang ditangani:

| Kondisi | Balasan |
|---|---|
| Off-topic | Arahkan kembali ke topik + contoh pertanyaan |
| Kosong + ada `city` | *"Belum ada destinasi di {Kota}... pilihan paling lengkap ada di Padang."* |
| Kosong (umum) | Sarankan longgarkan kriteria |
| Hasil dari varian longgar | *"Tidak persis cocok, tapi ini yang paling mendekati"* |

### 3.9 Response ke frontend

```json
{
  "reply": "Berikut 6 rekomendasi Kuliner · di Bukittinggi ...",
  "off_topic": false,
  "destinations": [
    {
      "name": "Nasi Kapau Uni Lis",
      "url": "https://.../destinasi/nasi-kapau-uni-lis",
      "image": "/images/destinations/nasi-kapau-uni-lis.jpg",
      "category": "Kuliner",
      "city": "Bukittinggi",
      "price": "Murah",
      "rating": 4.5,
      "reviews": 12
    }
  ]
}
```

---

## 4. Rate Limiting (kontrol biaya)

Rem di **sisi aplikasi**, bukan mengandalkan limit provider:

```php
// ConciergeController::rateLimited()
if ($user?->isAdmin()) return null;          // admin bebas

$max   = (int) config($user ? 'concierge.rate_limits.user' : 'concierge.rate_limits.guest');
$decay = (int) config('concierge.rate_limits.decay_seconds', 86400);
$key   = 'concierge:'.($user?->id ?? $request->ip());

if (RateLimiter::tooManyAttempts($key, $max)) {
    $hours = (int) ceil(RateLimiter::availableIn($key) / 3600);
    return response()->json([
        'reply' => "Kamu sudah mencapai batas chat hari ini. Coba lagi dalam ±{$hours} jam.",
        'limited' => true,
    ], 429);
}
RateLimiter::hit($key, $decay);
```

Default: **tamu 8/hari** (per IP), **user login 40/hari**, **admin tak terbatas**.
Ini sekaligus insentif agar pengunjung mendaftar.

> ⚠️ **Catatan:** ini batas *per pengguna*, **bukan batas global situs**. Kalau
> butuh kepastian biaya maksimum, tambahkan penghitung harian global
> (mis. `Cache::increment('concierge:global:'.today())` dengan plafon).

---

## 5. Halaman Admin — Pilih Model Gemini

**Route:** `GET|PUT /admin/concierge`, `POST /admin/concierge/check`

### 5.1 Yang ditampilkan

Daftar radio button semua model Gemini yang **benar-benar bisa dipakai API key
ini**, masing-masing dengan:

- Nama model + badge `aktif`
- **Terpakai hari ini** — dihitung aplikasi sendiri
- **Estimasi sisa** — dari `config('concierge.model_daily_caps')`
- Tombol **Cek status** — uji model secara langsung

### 5.2 Daftar model diambil live

```php
// GeminiModels::available() — di-cache 1 jam
$res = Http::withQueryParameters(['key' => $key, 'pageSize' => 200])
    ->get('https://generativelanguage.googleapis.com/v1beta/models');

$models = collect($res->json('models', []))
    ->filter(fn ($m) => in_array('generateContent', $m['supportedGenerationMethods'] ?? [], true))
    ->map(fn ($m) => str_replace('models/', '', $m['name'] ?? ''))
    ->filter(fn ($id) => str_starts_with($id, 'gemini'))
    ->values()->all();

return $models ?: config('concierge.models_fallback');  // fallback kalau API gagal
```

Tombol **↻ Segarkan daftar model** memanggil `?refresh=1` → `GeminiModels::forget()`.

### 5.3 Menyimpan pilihan

Disimpan ke tabel `settings` (key-value), **bukan** ke `.env` — supaya bisa
diubah admin tanpa deploy ulang:

```php
Settings::setMany(['concierge_model' => $request->input('concierge_model')]);
```

Dibaca saat runtime, dengan fallback berlapis:

```php
private function activeModel(): string
{
    $chosen = setting('concierge_model');                    // 1. pilihan admin (DB)
    return is_string($chosen) && $chosen !== ''
        ? $chosen
        : config('concierge.gemini.model');                  // 2. .env / config
}
```

Validasi: `Rule::in(GeminiModels::available())` — admin tidak bisa menyimpan
model yang tidak didukung API key-nya.

### 5.4 Penghitung pemakaian

Google **tidak menyediakan API untuk melihat sisa kuota**. Jadi aplikasi
menghitung sendiri, per model per hari, di cache:

```php
// ConciergeUsage
private static function key(string $model): string
{
    return 'concierge:usage:'.$model.':'.now()->toDateString();
}

public static function increment(string $model): void
{
    $key = self::key($model);
    Cache::add($key, 0, now()->endOfDay());   // auto-hapus tengah malam
    Cache::increment($key);
}
```

Dipanggil di dua tempat: setiap `extract()` dan setiap `probe()`.

> **Jujur ke admin:** panel menampilkan disclaimer bahwa angka ini adalah
> hitungan lokal + estimasi cap, bukan data resmi Google. Jangan menyajikan
> tebakan seolah-olah fakta.

### 5.5 Probe status model (fitur paling berguna)

Menguji model dengan permintaan super kecil (memakai 1 kuota):

```php
$res = Http::post("{$endpoint}/{$model}:generateContent", [
    'contents' => [['role' => 'user', 'parts' => [['text' => 'ok']]]],
]);
```

Yang membuatnya berguna: **membedakan jenis error 429**.

```php
private static function interpretRateLimit(?array $json): array
{
    $details = collect(data_get($json, 'error.details', []));

    $quotaIds = $details
        ->where('@type', 'type.googleapis.com/google.rpc.QuotaFailure')
        ->flatMap(fn ($d) => collect($d['violations'] ?? [])->pluck('quotaId'))
        ->implode(' ');

    $retry = data_get($details->firstWhere('@type', 'type.googleapis.com/google.rpc.RetryInfo'), 'retryDelay');

    if (str_contains($quotaIds, 'PerDay')) {
        return ['ok' => false, 'transient' => false, 'message' => 'Kuota harian habis'];
    }
    return ['ok' => false, 'transient' => true, 'message' => "Limit per-menit (coba lagi ~{$retry})"];
}
```

Hasilnya dibedakan visual di UI:

| Status | Badge | Arti |
|---|---|---|
| `ok` | ✅ hijau — Tersedia | Siap dipakai |
| `transient` | ⏳ kuning — Limit per-menit / server sibuk | Sementara, tunggu sebentar |
| gagal | ⛔ merah — Kuota harian habis | Sampai reset besok |

> **Pelajaran nyata:** tanpa pembedaan ini, admin melihat "unavailable" padahal
> baru 3 kali pakai — dan mengira kuota harian habis, padahal itu hanya limit
> per-menit (15 RPM) atau Gemini sedang 503.

---

## 6. Konfigurasi (`config/concierge.php`)

```php
return [
    'provider' => env('CONCIERGE_PROVIDER', 'gemini'),

    'gemini' => [
        'key'      => env('GEMINI_API_KEY'),
        'model'    => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),  // lihat catatan di bawah
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
        'timeout'  => (int) env('GEMINI_TIMEOUT', 20),
        'retries'  => (int) env('GEMINI_RETRIES', 2),   // percobaan ekstra saat 5xx
    ],

    // Dipakai kalau ListModels API gagal
    'models_fallback' => [
        'gemini-2.5-flash', 'gemini-2.5-flash-lite',
        'gemini-2.0-flash', 'gemini-2.0-flash-lite',
    ],

    // ESTIMASI cap free-tier (RPD) — hanya untuk hint "sisa" di panel admin.
    // Google tidak mengekspos sisa kuota lewat API. Sesuaikan dengan paketmu.
    'model_daily_caps' => [
        'gemini-2.5-flash'      => 250,
        'gemini-2.5-flash-lite' => 1000,
        'gemini-2.0-flash'      => 200,
        'gemini-2.0-flash-lite' => 200,
        'gemini-1.5-flash'      => 50,
    ],

    'results' => (int) env('CONCIERGE_RESULTS', 6),

    'rate_limits' => [
        'guest'         => (int) env('CONCIERGE_LIMIT_GUEST', 8),
        'user'          => (int) env('CONCIERGE_LIMIT_USER', 40),
        'decay_seconds' => (int) env('CONCIERGE_LIMIT_DECAY', 86400),
    ],
];
```

`.env`:

```env
GEMINI_API_KEY=...
GEMINI_MODEL=gemini-2.5-flash-lite
CONCIERGE_LIMIT_GUEST=8
CONCIERGE_LIMIT_USER=40
```

### 6.1 Model default: `gemini-2.5-flash-lite` (dan kenapa)

**Pakai `gemini-2.5-flash-lite` sebagai default.** Ini berlaku baik di `.env`
maupun sebagai fallback di `config/`, supaya project yang lupa mengisi
`GEMINI_MODEL` tetap mendarat di model yang tepat.

| Model | Free tier (perkiraan) | Harga /1jt token | Verdict untuk pola ini |
|---|---|---|---|
| **`gemini-2.5-flash-lite`** | **~1.000/hari** | $0.10 / $0.40 | ⭐ **Default** |
| `gemini-2.0-flash-lite` | ~200/hari | $0.075 / $0.30 | Sedikit lebih murah, kuota gratis jauh lebih kecil |
| `gemini-2.0-flash` | ~200/hari | $0.10 / $0.40 | ❌ Hindari — generasi lama, kuota kecil |
| `gemini-2.5-flash` | ~250/hari | $0.30 / $2.50 | ~6x lebih mahal, tanpa manfaat nyata di sini |
| `gemini-2.5-pro` | sangat kecil | $1.25 / $10.00 | ❌ Pemborosan besar untuk ekstraksi JSON |

Alasannya: tugas di pola ini **ringan dan sangat terstruktur** — memetakan
kalimat ke JSON dari daftar tertutup. Model yang lebih besar tidak menghasilkan
filter yang lebih benar, hanya tagihan yang lebih besar. Naik ke `2.5-flash`
baru masuk akal kalau terbukti kalimat rumit (negasi bertingkat, banyak syarat
sekaligus) sering meleset.

> ⚠️ **Jebakan:** jangan biarkan fallback tertinggal di `gemini-2.0-flash`.
> Free tier-nya hanya ~200 request/hari (**5x lebih kecil**), dan project yang
> lupa mengisi `.env` akan kehabisan kuota tanpa tahu sebabnya.

---

## 7. Retry untuk Error Sementara

Gemini cukup sering mengembalikan 503 (overloaded). Tanpa retry, user langsung
jatuh ke fallback keyword dan **hasilnya terasa "itu-itu saja"**:

```php
$attempts = max(1, (int) config('concierge.gemini.retries', 2) + 1);

for ($i = 1; $i <= $attempts; $i++) {
    $response = Http::timeout(20)->post($url, $payload);

    if ($response->successful() || ! in_array($response->status(), [500, 502, 503], true)) {
        break;   // sukses, atau error permanen (jangan retry)
    }
    if ($i < $attempts) {
        usleep(400000 * $i);   // backoff 0.4s, lalu 0.8s
    }
}
```

**Hanya retry 500/502/503.** Jangan retry 429 (malah memperparah) atau 400/403
(error permanen).

---

## 8. Testing

Semua test memakai `Http::fake()` — **tidak pernah memanggil API sungguhan**:

```php
private function fakeGemini(array $filters): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[ 'content' => ['parts' => [['text' => json_encode($filters)]]] ]],
        ]),
    ]);
}
```

Skenario yang wajib dites:

| Test | Memastikan |
|---|---|
| Ekstraksi normal | Filter diterapkan, hasil dari DB, dibatasi `results` |
| Off-topic | Tidak ada hasil, balasan mengarahkan kembali |
| Tidak ada yang cocok | Balasan jujur, tidak mengarang |
| **LLM gagal (500)** | Fallback keyword tetap memberi hasil |
| **Rate limit** | Request ke-N+1 → 429 `limited: true` |
| **Vocabulary menolak nilai asing** | `category: "tidak-ada"` → `null`, tag palsu dibuang |
| **Filter kota** | Hasil hanya dari kota itu, tidak bocor |
| **Negasi/exclude** | `exclude: ["sate"]` → tidak ada hasil mengandung "sate" |

⚠️ **Gotcha:** `Http::fake()` yang dipanggil **dua kali dalam satu test** akan
menumpuk stub dan **yang pertama menang**. Kalau butuh dua respons berbeda,
pisahkan jadi dua test.

---

## 9. Pelajaran / Gotcha Penting

1. **Kuota Gemini dihitung per model, per project.** Kalau `gemini-2.0-flash`
   habis, pindah ke `gemini-2.5-flash-lite` langsung dapat jatah segar. Ini
   alasan utama fitur pemilih model di admin ada.

2. **Ada 3 lapis limit**: RPD (per hari), RPM (per menit, ~15 di free tier), TPM
   (token per menit). "Unavailable padahal baru 3x pakai" hampir selalu RPM
   atau 503 — bukan kuota harian.

3. **Jangan panggil LLM dua kali per pertanyaan.** Kalimat pembuka cukup
   template. Ini memangkas ~50% biaya tanpa penurunan kualitas terasa.

4. **`temperature` rendah untuk tugas ekstraksi.** 0.1 memberi hasil konsisten,
   dan membuka peluang caching hasil.

5. **Selalu validasi output LLM terhadap allow-list.** Jangan pernah masukkan
   nilai dari LLM langsung ke query. Ini pertahanan utama.

6. **Negasi harus ditangani eksplisit.** Model cenderung memperlakukan semua
   kata benda sebagai keyword positif. Tanpa instruksi + field `exclude`
   terpisah, "jangan sate" justru memunculkan sate.

7. **Facet yang mengubah makna jangan ikut dilonggarkan.** Kota dan exclusion
   adalah batasan keras. Melonggarkannya menghasilkan jawaban yang terasa
   "tidak nyambung".

8. **Jujur soal angka yang kamu tebak.** Panel admin menyebut terang-terangan
   bahwa "estimasi sisa" adalah perkiraan, karena Google tidak menyediakan
   datanya.

---

## 10. Optimasi Lanjutan (kalau trafik besar)

Belum diimplementasi, tapi ini urutan dampak terbesar:

| Optimasi | Dampak | Usaha |
|---|---|---|
| **Cache hasil ekstraksi di aplikasi** | −60% panggilan | Kecil |
| **Context caching provider** | −75% biaya token input | Kecil |
| **Batas harian global** | Kunci biaya maksimum | Kecil |
| Adapter OpenAI-compatible | Bebas pindah provider | Sedang |

**Cache hasil** paling berdampak dan mudah, karena ekstraksi bersifat
deterministik (temperature 0.1):

```php
$key = 'concierge:q:'.md5(mb_strtolower(trim($message)));
$extracted = Cache::remember($key, now()->addDay(), fn () => $this->extract($message, $vocab));
```

Di situs katalog, pertanyaan sangat sering berulang ("kuliner enak", "tempat
keluarga"), jadi hit-rate biasanya tinggi — biaya turun **dan** respons untuk
pertanyaan populer jadi instan.

---

## 11. Cara Mengadaptasi ke Project Lain

Pola ini cocok untuk **domain apa pun yang punya katalog + filter**.

### Checklist

1. **Pastikan sudah ada mesin pencarian/filter** yang dipakai halaman biasa.
   Kalau belum, buat dulu — AI Concierge menumpang di atasnya, bukan
   menggantikannya.

2. **Buat DTO kriteria** (`SearchCriteria`) berisi semua facet + `excludeKeywords`.
   Gunakan `readonly` properties.

3. **Buat `Vocabulary`** yang:
   - `forPrompt()` — render allow-list dari enum/tabel (jangan hardcode)
   - `toCriteria()` — validasi output LLM, buang semua yang tidak dikenal

4. **Sesuaikan system prompt**: ganti deskripsi domain, daftar field, dan
   contoh negasi. Pertahankan tiga aturan: *hanya JSON*, *hanya nilai dari
   daftar*, *negasi ke `exclude`*.

5. **Salin pola** `ConciergeService` (extract → validasi → relaxation → reply),
   `ConciergeUsage`, `GeminiModels` — ketiganya hampir tidak perlu diubah.

6. **Tentukan facet keras** untuk domainmu — yang tidak boleh dilonggarkan.
   Contoh: lokasi/kota (properti), tanggal (event), ukuran (fashion).

7. **Setel rate limit** sesuai nilai ekonomi tiap pertanyaan.

8. **Tulis test dengan `Http::fake()`** sejak awal — mustahil mengembangkan ini
   dengan nyaman kalau setiap percobaan memakan kuota asli.

### Yang perlu diganti per domain

| Bagian | Contoh untuk domain lain |
|---|---|
| Deskripsi peran di prompt | "asisten direktori properti", "asisten katalog produk" |
| Field JSON | `bedrooms`, `price_max`, `brand`, `size` |
| Facet keras | `city` → `location`, `available_date`, `in_stock` |
| Balasan template | Sesuaikan kalimat pembuka & kasus kosong |
| Contoh prompt di UI | Pertanyaan khas domain tersebut |

### Yang bisa dipakai apa adanya

- `ConciergeUsage` (penghitung harian per model)
- `GeminiModels` (list + probe + interpretasi 429)
- Panel admin pemilih model
- Struktur retry
- Pola rate limiting
- Pola `Http::fake()` di test

---

## 12. Ringkasan Endpoint

| Method | Path | Akses | Fungsi |
|---|---|---|---|
| `GET` | `/asisten` | Publik | Halaman chat |
| `POST` | `/asisten/tanya` | Publik (rate-limited) | Kirim pertanyaan → JSON |
| `GET` | `/admin/concierge` | Admin | Panel pilih model + pemakaian |
| `PUT` | `/admin/concierge` | Admin | Simpan model aktif |
| `POST` | `/admin/concierge/check` | Admin | Probe status model (1 kuota) |

---

## 13. Catatan Biaya (referensi cepat)

Dengan pola ini, 1 pertanyaan ≈ **1.000 token input + 80 token output**
(input didominasi system prompt berisi allow-list).

Pada `gemini-2.5-flash-lite` (~$0.10 input / $0.40 output per 1 juta token):

| Volume | Perkiraan biaya |
|---|---|
| 1 pertanyaan | ~$0.00013 |
| 1.000 pertanyaan | ~$0.13 |
| 100/hari selama sebulan | ~$0.40 |
| 100.000/hari selama sebulan | ~$396 (turun ~$75 dengan caching) |

> Harga berubah sewaktu-waktu — verifikasi di **ai.google.dev/pricing**.
> Free tier `gemini-2.5-flash-lite` sekitar 1.000 request/hari, cukup longgar
> untuk situs skala kecil–menengah.
