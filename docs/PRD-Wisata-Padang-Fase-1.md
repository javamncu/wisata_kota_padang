# PRD / Spesifikasi — Aplikasi Wisata Kota Padang (Fase 1)

> Dokumen ini adalah spesifikasi produk untuk **Fase 1 (MVP)**. Ditujukan sebagai acuan implementasi oleh Claude Code. Fase 2 & 3 dicantumkan di akhir hanya sebagai konteks arah, **bukan** untuk dibangun sekarang.

---

## 1. Ringkasan Produk

Aplikasi direktori informasi wisata Kota Padang — semacam "satu pintu" informasi bagi wisatawan. Aplikasi menyediakan data lengkap tempat wisata, kuliner, dan kategori lain yang relevan, serta membantu pengunjung **menemukan destinasi berdasarkan preferensi mereka**.

- **Target pengguna:** wisatawan (domestik & asing) yang mencari informasi destinasi di Padang.
- **Value proposition:** pencarian destinasi yang relevan + database terkurasi & terpercaya.
- **Aset inti:** database destinasi yang lengkap dan ber-tag rapi.

---

## 2. Tujuan & Lingkup Fase 1

**Termasuk (in scope):**
- Database destinasi multi-kategori dengan panel admin (CRUD).
- Pencarian kombinasi: keyword + filter + kuis preferensi (skoring rule-based).
- Halaman detail destinasi (template reusable lintas kategori).
- Akun pengguna: favorit/wishlist + review & rating.
- Panel admin: kelola destinasi, kategori, tag, user, dan moderasi review.

**Tidak termasuk (out of scope, lihat Fase 2 & 3):**
- Penginapan/akomodasi, Wisata di Sekitarku (GPS), blog/artikel, settings admin (Fase 2).
- AI Concierge (Fase 3).
- Booking/pembayaran, multi-bahasa, mobile/PWA, optimasi rute.

---

## 3. Tech Stack

- **Framework:** Laravel (versi stabil terkini), fullstack.
- **Templating:** Blade.
- **Styling:** Tailwind CSS.
- **Database:** MySQL / MariaDB.
- **ORM:** Eloquent.
- **Auth:** Laravel Breeze (stack Blade) sebagai dasar, lalu ditambah kolom `role`.
- **Peta:** Leaflet + OpenStreetMap (gratis) atau Google Maps Embed — pilih salah satu.
- **Penyimpanan gambar:** Laravel Storage (disk `public`).

---

## 4. Peran Pengguna

| Peran | Definisi |
|-------|----------|
| **Guest** | Pengunjung belum login. Bukan baris di tabel `users`. |
| **User** | Pengunjung terdaftar & login. `users.role = user`. |
| **Admin** | Pengelola konten. `users.role = admin`. |

Aturan berlapis: **Admin** mewarisi semua kemampuan **User**, dan **User** mewarisi semua kemampuan **Guest**.

---

## 5. Matriks Hak Akses

| Aksi / Fitur | Guest | User | Admin |
|---|:--:|:--:|:--:|
| Lihat destinasi & detail | ✅ | ✅ | ✅ |
| Cari, filter, explore | ✅ | ✅ | ✅ |
| Pakai kuis preferensi | ✅ | ✅ | ✅ |
| Lihat review | ✅ | ✅ | ✅ |
| Simpan favorit / wishlist | ❌ | ✅ | ✅ |
| Tulis / edit review & rating | ❌ | ✅ | ✅ |
| Edit profil sendiri | ❌ | ✅ | ✅ |
| Kelola destinasi (CRUD) | ❌ | ❌ | ✅ |
| Kelola kategori & tag | ❌ | ❌ | ✅ |
| Kelola user & ubah role | ❌ | ❌ | ✅ |
| Moderasi review | ❌ | ❌ | ✅ |
| Akses dashboard admin | ❌ | ❌ | ✅ |

**Implementasi:** middleware `role` + Laravel Policy/Gate (mis. `DestinationPolicy`, `ReviewPolicy`). Area admin pakai prefix route `/admin` + middleware admin.

---

## 6. Arsitektur Pencarian (Kombinasi)

Tiga "pintu masuk" yang membaca **satu** mesin pencarian/skoring yang sama:

1. **Keyword search** — cari pada `name` & `description`.
2. **Filter** — query berdasarkan atribut enum & tag.
3. **Kuis preferensi** — jawaban user → profil preferensi → skoring → urut.

Mesin skoring bersifat **rule-based (tanpa AI/ML)**: tiap destinasi diberi skor berdasarkan kecocokan atribut, lalu diurutkan menurun. Filter & keyword cukup query langsung; kuis menambahkan skoring di atasnya.

---

## 7. Model Data

Data destinasi disusun 3 lapis: **field inti**, **atribut enum (dikunci di kode)**, dan **tag bebas (fleksibel)**.

### 7.1 Kategori utama (1 destinasi = 1 kategori)
- Wisata Alam
- Wisata Sejarah & Budaya
- Wisata Religi
- Kuliner
- Belanja & Oleh-oleh
- Rekreasi & Hiburan

### 7.2 Field inti (wajib, semua destinasi)
Nama, slug, deskripsi singkat, deskripsi lengkap, galeri foto, alamat, koordinat (lat/lng), jam & hari buka, kisaran harga/tiket, kontak (telp/Instagram/website), rating & jumlah review (**derived**, dihitung dari `reviews`), status (draft/aktif).

### 7.3 Atribut enum — **dikunci di kode (PHP Enum / config), BUKAN tabel CRUD**

Single-value (kolom enum biasa):
- `price_range`: Gratis · Murah · Sedang · Premium
- `indoor_outdoor`: Indoor · Outdoor · Campuran
- `duration`: Singkat (<1 jam) · Sedang (1–3 jam) · Lama (>3 jam)
- `zone`: Pusat Kota · Pesisir/Pantai · Kawasan Selatan · Kepulauan · Perbukitan
- `status`: draft · aktif

Multi-value (disimpan sebagai **kolom JSON**, di-query dengan `whereJsonContains`):
- `cocok_untuk`: Keluarga & anak · Pasangan · Solo · Rombongan · Lansia · Ramah difabel
- `waktu_ideal`: Pagi · Siang · Sore/sunset · Malam

### 7.4 Tag bebas — **many-to-many, full CRUD**
Tag dikelola admin (boleh tambah kapan saja). Dikelompokkan via kolom `type`:
- **suasana**: santai, ramai/hidup, romantis, instagramable, asri/sejuk, klasik/bersejarah, petualangan
- **aktivitas**: foto-foto, berenang, hiking, edukasi/sejarah, relaksasi, ibadah, belanja, kulineran
- **fasilitas**: parkir, toilet, mushola, wifi, spot foto, area anak, ramah difabel

### 7.5 Strategi CRUD (PENTING)
| Entitas | Perlakuan | Catatan |
|---|---|---|
| `destinations` | **Full CRUD** | Pekerjaan harian admin. |
| `tags` | **Full CRUD** | Boleh dibuat on-the-fly saat input destinasi. |
| `categories` | **CRUD + pengaman** | Dilarang hapus jika masih punya destinasi; gunakan `is_active` / soft delete. |
| Atribut enum | **Dikunci di kode** | PHP Enum / config — bukan tabel database. |

### 7.6 Catatan penting
- `cocok_untuk` & `waktu_ideal` multi-pilih → kolom JSON (Fase 1). Bisa dinaikkan jadi pivot bila perlu query berat.
- `rating` & `review_count` adalah **turunan** dari tabel `reviews` (boleh di-cache), bukan diisi manual.

---

## 8. Skema Database (ERD)

### Tabel & kolom utama

**users**
`id`, `name`, `email`, `password`, `role` (enum: `user`/`admin`), `avatar` (nullable), timestamps.

**categories**
`id`, `name`, `slug`, `description` (nullable), `icon` (nullable), `is_active` (bool), timestamps.

**destinations**
`id`, `category_id` (FK), `name`, `slug`, `description_short`, `description_long`, `address`, `latitude`, `longitude`, `opening_hours` (text/json), `price_range` (enum), `price_info` (nullable, teks bebas), `contact_phone` (nullable), `contact_instagram` (nullable), `contact_website` (nullable), `zone` (enum), `indoor_outdoor` (enum), `duration` (enum), `cocok_untuk` (json), `waktu_ideal` (json), `status` (enum: draft/aktif), `rating_cache` (decimal, nullable), `review_count_cache` (int, default 0), timestamps.

**destination_images**
`id`, `destination_id` (FK), `path`, `sort_order` (int), timestamps.

**tags**
`id`, `name`, `slug`, `type` (enum: suasana/aktivitas/fasilitas), timestamps.

**destination_tag** (pivot)
`destination_id` (FK), `tag_id` (FK).

**reviews**
`id`, `destination_id` (FK), `user_id` (FK), `rating` (int 1–5), `comment` (text, nullable), `status` (enum: pending/published), timestamps.

**favorites** (pivot)
`id`, `user_id` (FK), `destination_id` (FK), timestamps.

### Relasi
- `categories` 1—N `destinations`
- `destinations` 1—N `destination_images`
- `destinations` N—M `tags` (via `destination_tag`)
- `destinations` 1—N `reviews`
- `users` 1—N `reviews`
- `users` N—M `destinations` (via `favorites`)

---

## 9. Desain Kuis Preferensi

Wizard beberapa langkah. Setiap pertanyaan dipetakan ke satu atribut. Tiap pertanyaan menyertakan opsi **"Tidak masalah / lewati"** (dimensi itu tidak diskor).

| # | Pertanyaan | Atribut | Contoh jawaban |
|---|-----------|---------|----------------|
| 1 | Kamu jalan dengan siapa? | `cocok_untuk` | Keluarga & anak · Pasangan · Solo · Rombongan · Lansia |
| 2 | Suasana yang dicari? | tag `suasana` | Santai · Ramai · Romantis · Instagramable · Petualangan · Klasik |
| 3 | Budget per orang? | `price_range` | Gratis–Murah · Sedang · Premium |
| 4 | Rencananya kapan? | `waktu_ideal` | Pagi · Siang · Sore/sunset · Malam |
| 5 | Berapa lama waktu luang? | `duration` | Singkat · Sedang · Lama |
| 6 | Lebih suka di mana? | `indoor_outdoor` | Indoor · Outdoor · Bebas |
| 7 (opsional) | Tertarik kategori apa? | `category` | Alam · Kuliner · Religi · Sejarah … |

### Skoring (rule-based)
- Mulai skor 0, tambahkan poin per atribut yang cocok.
- **Bobot:** `cocok_untuk` & `price_range` = 3 · `suasana` & `waktu_ideal` = 2 · sisanya = 1.
- Urutkan hasil dari skor tertinggi.
- Tampilkan label **"kenapa direkomendasikan"** = daftar atribut yang cocok.

---

## 10. Daftar & Spesifikasi Halaman

### A. Publik (Guest)

**1. Hero / Beranda**
Search bar keyword menonjol; shortcut kategori (card/ikon); CTA "Temukan lewat kuis preferensi"; seksi destinasi populer/unggulan; sorotan per kategori; footer (Tentang, Kontak, kategori).

**2. Explore / Pencarian**
Search bar; panel filter (kategori, zona, budget, indoor/outdoor, cocok untuk, waktu, durasi, fasilitas); sorting (populer · rating · A–Z); toggle Grid ↔ Peta; hasil card (foto, nama, kategori, rating, harga, tag); chip filter aktif yang bisa dihapus; pagination; empty state.

**3. Kuis Preferensi**
Wizard multi-langkah dengan indikator progress; halaman hasil = daftar rekomendasi terurut; label "kenapa direkomendasikan".

**4. Detail Destinasi** (template reusable untuk semua kategori)
Header (nama, badge kategori, rating, lokasi singkat); galeri foto; info ringkas (jam buka, harga, durasi, kontak); deskripsi lengkap; chip atribut & tag; peta lokasi (embed) + tombol arah Google Maps; tombol **Favorit** (guest → diarahkan login); seksi review & rating (daftar + tombol "Tulis review" untuk user); "Destinasi serupa" (kategori/tag/zona).

**5. Halaman Kategori**
Versi Explore yang sudah ter-filter ke satu kategori (reuse komponen Explore) + header & deskripsi kategori.

**6. Peta Interaktif**
Peta penuh semua marker; filter overlay (kategori, zona); klik marker → popup ringkas → link Detail.

**7. Tentang & Kontak**
Tentang aplikasi; form/email kontak; sosial media.

**8. Auth** — Login · Register (nama, email, password) · Lupa Password.

### B. Khusus User (login)

**9. Dashboard / Profil** — edit profil (nama, foto, password); ringkasan jumlah favorit & review; navigasi ke Favorit & Review Saya.

**10. Favorit / Wishlist** — grid destinasi tersimpan + tombol hapus; empty state.

**11. Review Saya** — daftar review user; edit/hapus.

### C. Panel Admin (`/admin`)

**12. Dashboard Admin** — statistik (total destinasi, sebaran kategori, total user, review pending); quick action.

**13. Kelola Destinasi (CRUD penuh)** — tabel (search, filter, sort, pagination); form tambah/edit (field inti + kategori + atribut enum [dropdown] + tag [multi-select/create] + upload galeri + status); aksi edit, soft delete, toggle status.

**14. Kelola Kategori (CRUD + pengaman)** — daftar + jumlah destinasi; tambah/edit; hapus diblokir bila masih dipakai (nonaktif/soft delete).

**15. Kelola Tag (CRUD)** — daftar + jumlah pemakaian; tambah/edit/hapus.

**16. Kelola User** — daftar user; ubah role; blokir/aktifkan.

**17. Moderasi Review** — daftar review (pending/published); approve atau hapus.

---

## 11. Konvensi & Struktur (saran)

- **Struktur Laravel standar (MVC):**
  - Controllers: `app/Http/Controllers` (+ subfolder `Admin/` untuk panel admin).
  - Models: `app/Models`.
  - **Enums:** `app/Enums` (mis. `PriceRange`, `Zone`, `IndoorOutdoor`, `Duration`, `Status`, `Role`, `CocokUntuk`, `WaktuIdeal`).
  - Policies: `app/Policies`.
  - Views: `resources/views` (folder per area: `public/`, `user/`, `admin/`).
  - Routes: `routes/web.php`; group admin dengan prefix `/admin` + middleware.
- **Penamaan:** tabel `snake_case` jamak; model singular; slug unik untuk `destinations`, `categories`, `tags`.
- **Seeder:** sediakan seeder kategori + beberapa destinasi contoh + akun admin default.

---

## 12. Konteks Arah (Fase 2 & 3 — JANGAN dibangun di Fase 1)

**Fase 2 (Personalisasi):**
- Wisata di Sekitarku (GPS) — rekomendasi berbasis lokasi (Geolocation API + jarak `ST_Distance_Sphere`/Haversine).
- Blog / Artikel — konten panduan & SEO.
- Settings Admin — menyetel ambang enum (mis. rentang rupiah budget) tanpa ubah kode.

**Fase 3 (Teman Perjalanan):**
- AI Concierge — chat rekomendasi berbahasa natural; LLM via **API** (mulai dari free tier seperti Gemini, naik ke paid saat scale). Pola: LLM mengekstrak filter (teks → JSON) → reuse mesin pencarian Fase 1 → hasil grounded di database. Guardrail: hanya rekomendasi dari database, dilarang mengarang tempat; rate-limit per user.

**Backlog (fase mana pun nanti):** Trip Planner penuh + optimasi rute, Monetisasi (affiliate/sponsored UMKM), Analitik admin lanjutan, Kalender Event & Festival, Hidden Gems, Travel Passport, Galeri Komunitas, Multi-bahasa, Mobile/PWA.

---

*Akhir dokumen Fase 1.*
