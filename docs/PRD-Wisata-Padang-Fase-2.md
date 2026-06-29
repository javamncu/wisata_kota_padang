# PRD / Spesifikasi — Aplikasi Wisata Kota Padang (Fase 2)

> Dokumen ini adalah spesifikasi **Fase 2 (Personalisasi & Konten)**. Dibangun **di atas Fase 1** — pastikan Fase 1 (lihat `PRD-Wisata-Padang-Fase-1.md`) sudah selesai lebih dulu. Fase 3 & backlog dicantumkan di akhir hanya sebagai konteks arah, **bukan** untuk dibangun sekarang.

---

## 1. Ringkasan & Tujuan Fase 2

Menambah **lapisan personalisasi & konten** di atas direktori inti Fase 1, mengubah aplikasi dari "situs informasi" menjadi panduan wisata yang lebih hidup.

**Termasuk (in scope):**
1. **Wisata di Sekitarku (GPS)** — rekomendasi destinasi berbasis lokasi.
2. **Blog / Artikel** — konten panduan & SEO, terhubung ke destinasi.
3. **Settings Admin** — konfigurasi situs & label tanpa ubah kode.

**Tidak termasuk (out of scope):**
- AI Concierge (Fase 3).
- Penginapan/akomodasi (dibatalkan).
- Booking/pembayaran, multi-bahasa, mobile/PWA, optimasi rute (backlog/Fase 3).

**Prinsip:** sebisa mungkin **reuse** komponen & data Fase 1 (mesin pencarian, peta, kartu destinasi). Tidak menyentuh keputusan struktur Fase 1 (mis. enum tetap dikunci di kode).

---

## 2. Tambahan Tech Stack

- **Geolocation API** (browser, client-side) untuk lokasi user.
- **Editor rich text** untuk body artikel (mis. Trix / TipTap / CKEditor).
- **HTML sanitizer** untuk konten artikel (mis. HTMLPurifier / `mews/purifier`) — wajib, cegah XSS.
- **Penyimpanan settings:** tabel `settings` pola key→value + cache (atau paket `spatie/laravel-settings`, opsional).
- **Peta:** reuse peta Fase 1 (Leaflet/OpenStreetMap atau Google Maps).

---

## 3. Fitur 1 — Wisata di Sekitarku (GPS)

### Cara kerja
Browser meminta lokasi user (Geolocation API) → dapat `lat/lng` → query destinasi diurutkan dari yang terdekat → tampilkan dengan label jarak (mis. "1,2 km dari kamu").

### Model data
**Tidak ada tabel baru.** Murni menumpang `destinations.latitude` & `destinations.longitude` dari Fase 1.

### Spec halaman "Wisata di Sekitarku"
- Tombol/prompt **"Izinkan akses lokasi"** + penjelasan singkat alasannya.
- Setelah diizinkan: grid/list destinasi terdekat, tiap card menampilkan jarak, urut terdekat → terjauh.
- Filter: **kategori** + **radius** (1 / 3 / 5 / 10 km).
- Toggle **Grid ↔ Peta** (peta berpusat di lokasi user; marker = destinasi sekitar; klik marker → popup → link Detail).
- **Fallback** (izin ditolak / GPS tidak tersedia): arahkan user memilih **zona/area manual** → jatuh ke Explore biasa yang difilter zona.

### Catatan teknis
- **Wajib HTTPS.**
- Lokasi user **tidak disimpan** — hanya dipakai untuk query (transient/privasi).
- Hitung jarak: `ST_Distance_Sphere(POINT(lng, lat), POINT(dest_lng, dest_lat))` (meter) atau rumus Haversine. Untuk skala satu kota, query sederhana sudah cukup; tambahkan spatial index bila perlu.
- Halaman ini ≈ **Explore + "urut berdasarkan jarak dari saya"** → reuse komponen Explore & kartu destinasi.

---

## 4. Fitur 2 — Blog / Artikel

Praktis sebuah mini-CMS terkurasi yang terhubung ke direktori.

### Model data — tabel baru `articles`
`id`, `title`, `slug`, `excerpt`, `body` (rich text/HTML), `cover_image` (path), `author_id` (FK users), `status` (enum: `draft`/`published`), `published_at` (nullable datetime), `meta_title` (nullable), `meta_description` (nullable), timestamps.

### Relasi — artikel ↔ destinasi
Pivot **`article_destination`** (`article_id` FK, `destination_id` FK), many-to-many.
→ Artikel (mis. *"5 Pantai Terbaik di Padang"*) bisa menautkan beberapa destinasi yang sudah ada, ditampilkan sebagai kartu "destinasi terkait" di halaman artikel. Inilah sinergi blog ↔ direktori.

*(Tag/topik artikel opsional untuk v1 — bisa field `topic` sederhana atau ditunda.)*

### Halaman
**Admin — Kelola Artikel (CRUD penuh)**
- Tabel daftar (search, filter status, pagination).
- Form: judul, slug (otomatis), excerpt, **body via editor rich text**, upload cover, `status`, `published_at`, **pilih destinasi terkait** (multi-select), field SEO (`meta_title`, `meta_description`).
- Aksi: edit, hapus, toggle publish/draft.

**Publik — Daftar Artikel (`/blog`)**
- Grid artikel ber-status `published` (cover, judul, excerpt, tanggal), pagination.

**Publik — Detail Artikel (`/blog/{slug}`)**
- Cover, judul, meta (tanggal, penulis), isi artikel.
- Seksi **"Destinasi terkait"** (kartu dari pivot).
- Seksi "Artikel lainnya".

### Catatan penting
- Publik hanya melihat `published`; draft hanya untuk admin.
- **Editor rich text + sanitasi HTML wajib** (cegah XSS).
- URL bersih `/blog/{slug}` + meta/Open Graph tags + masuk `sitemap.xml`. Karena Blade server-rendered, SEO sudah baik secara default.
- Enum baru: `ArticleStatus` (`draft`/`published`).

---

## 5. Fitur 3 — Settings Admin

Konfigurasi situs & label oleh admin **tanpa menyentuh kode**.

### Keputusan desain (PENTING)
**Budget = Opsi A (label/ambang saja).** Settings hanya menyimpan **keterangan** tiap tier `price_range` (mis. *"Murah = di bawah Rp50.000"*) untuk ditampilkan sebagai hint ke user & panduan admin. **Tidak** mengubah logika, **tidak** menambah kolom harga numerik, **tidak** menyentuh enum.

### Guardrail
Settings hanya menyetel **label/ambang & konfigurasi situs** — **dilarang** menambah/menghapus jumlah opsi enum (`price_range`, `zone`, dll tetap dikunci di kode).

### Yang dikonfigurasi
- **Umum:** nama situs, kontak (email/telp), tautan sosial media.
- **Konten Beranda:** judul/subjudul hero, teks Tentang, pilihan destinasi unggulan.
- **Budget (Opsi A):** keterangan/label untuk tiap tier `price_range`.
- **Tampilan:** jumlah item per halaman, sorting default.

### Model data — tabel `settings`
Pola key→value: `id`, `key` (string, unik), `value` (json/text), timestamps. Akses lewat helper/facade `Setting::get('key')` dengan **cache** (bust cache saat disimpan).

### Spec halaman — Settings Admin
- Satu form bertab/seksi: **Umum · Konten Beranda · Budget · Tampilan**.
- Simpan → persist ke `settings` + **bersihkan cache**.
- Admin-only.

---

## 6. Tambahan Skema Database (Fase 2)

**Tabel baru:**
- **articles** — `id`, `title`, `slug`, `excerpt`, `body`, `cover_image`, `author_id` (FK users), `status` (enum), `published_at`, `meta_title`, `meta_description`, timestamps.
- **article_destination** (pivot) — `article_id` (FK), `destination_id` (FK).
- **settings** — `id`, `key`, `value` (json), timestamps.

**Relasi baru:**
- `users` 1—N `articles` (penulis)
- `articles` N—M `destinations` (via `article_destination`)

**Tidak ada perubahan** pada tabel `destinations` dan enum Fase 1.

---

## 7. Hak Akses Tambahan (Fase 2)

| Aksi / Fitur | Guest | User | Admin |
|---|:--:|:--:|:--:|
| Wisata di Sekitarku (GPS) | ✅ | ✅ | ✅ |
| Lihat blog / artikel (published) | ✅ | ✅ | ✅ |
| Kelola artikel (CRUD) | ❌ | ❌ | ✅ |
| Akses & ubah Settings | ❌ | ❌ | ✅ |

*(Matriks Fase 1 tetap berlaku.)*

---

## 8. Konteks Arah (Fase 3 & Backlog — JANGAN dibangun di Fase 2)

**Fase 3 — AI Concierge:** chat rekomendasi berbahasa natural; LLM via **API** (mulai free tier seperti Gemini, naik ke paid saat scale). Pola: LLM mengekstrak filter (teks → JSON) → reuse mesin pencarian → hasil grounded di database. Guardrail: hanya rekomendasi dari database, dilarang mengarang tempat; rate-limit per user.

**Backlog (fase mana pun nanti):** Trip Planner penuh + optimasi rute, Monetisasi (affiliate/sponsored UMKM), Analitik admin lanjutan, Kalender Event & Festival, Hidden Gems, Travel Passport, Galeri Komunitas, Multi-bahasa, Mobile/PWA, Penginapan via affiliate.

---

*Akhir dokumen Fase 2.*
