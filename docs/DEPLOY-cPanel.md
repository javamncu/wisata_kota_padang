# Panduan Deploy ke Shared Hosting (cPanel)

> Untuk hosting biasa/shared (bukan VPS), tanpa Node.js, kemungkinan tanpa SSH.
> Aset sudah di-build & database sudah di-export oleh tim dev — kamu tinggal upload & konfigurasi.

## 0) Yang sudah disiapkan (ada di project)
- `public/build/` → CSS/JS produksi (hasil `npm run build`). **Wajib ikut diupload.**
- `database/deploy/wisatakotapadang.sql` → seluruh data (destinasi, gambar, artikel, settings, user).
- `.env.production.example` → contoh konfigurasi `.env` produksi.

---

## 1) Siapkan paket upload (di komputer lokal)
1. (Opsional, biar `vendor` lebih kecil) jalankan: `composer install --no-dev --optimize-autoloader`.
2. Buat **ZIP** isi project, **KECUALIKAN**:
   - `node_modules/` (tidak dipakai di server)
   - `.git/`
   - `.env` lokal (jangan diupload — buat baru di server)
3. Yang **HARUS** ada di ZIP: `app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ vendor/ artisan composer.json` dan **`public/build/`** + **`public/images/`** (logo, hero, foto destinasi & blog).

---

## 2) cPanel → PHP & Database
1. **MultiPHP Manager**: set domain ke **PHP 8.2 atau lebih tinggi**.
2. **Select PHP Version → Extensions**: pastikan aktif: `pdo_mysql, mbstring, openssl, fileinfo, ctype, json, bcmath, gd, curl`.
3. **MySQL Databases**:
   - Buat database baru (mis. `cpaneluser_wisatapadang`).
   - Buat user DB + password, lalu **Add User To Database** dengan **ALL PRIVILEGES**.
   - Catat: nama database, username, password.
4. **phpMyAdmin** → pilih database tadi → tab **Import** → upload `wisatakotapadang.sql` → **Go**.
   - (Kalau file ditolak karena besar, zip dulu file .sql-nya; phpMyAdmin bisa import .zip.)

---

## 3) Upload & struktur folder (pilih SATU)

### Opsi A — Subdomain / addon domain (paling bersih, disarankan)
1. Upload & extract seluruh project ke folder, mis. `~/wisatapadang/`.
2. Di cPanel **Domains/Subdomains**, buat subdomain (mis. `wisata.domainkamu.com`) dan set **Document Root = `wisatapadang/public`**.
3. Selesai — `public/` Laravel jadi web root. Aman (file `.env`, `vendor`, dll. tidak bisa diakses publik).

### Opsi B — Domain utama (root terkunci di `public_html`)
1. Upload & extract project ke folder **di luar** `public_html`, mis. `~/wisatapadang_app/`.
2. **Pindahkan ISI** folder `wisatapadang_app/public/` (file `index.php`, `.htaccess`, folder `build/`, `images/`, `favicon.ico`, dll.) **ke dalam `public_html/`**.
3. Edit `public_html/index.php` — ubah 3 path berikut agar menunjuk ke folder app:

```php
// dari:
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// menjadi (sesuaikan nama folder app-mu):
if (file_exists($maintenance = __DIR__.'/../wisatapadang_app/storage/framework/maintenance.php')) {
require __DIR__.'/../wisatapadang_app/vendor/autoload.php';
$app = require_once __DIR__.'/../wisatapadang_app/bootstrap/app.php';
```

> Pastikan file `.htaccess` ikut ada di `public_html/` (untuk URL rewriting Laravel).

---

## 4) Buat file `.env` di server
1. Di folder app (root `wisatapadang/` untuk Opsi A, atau `wisatapadang_app/` untuk Opsi B), buat file **`.env`** dari `.env.production.example`.
2. Isi minimal:
   - `APP_KEY=` → **salin nilai `APP_KEY` dari `.env` lokal-mu** (yang diawali `base64:...`).
   - `APP_URL=https://domainkamu.com` (atau subdomain-nya).
   - `DB_DATABASE / DB_USERNAME / DB_PASSWORD` → kredensial dari langkah 2.3.
   - `GEMINI_API_KEY=` → API key Gemini-mu.
   - `APP_ENV=production`, `APP_DEBUG=false` (jangan `true` di produksi).

---

## 5) Permission folder
Pastikan folder ini **writable** (chmod 755 atau 775) — lewat File Manager (Permissions) atau Terminal:
- `storage/` (dan seluruh isinya)
- `bootstrap/cache/`

---

## 6) Finalisasi cache
- **Kalau ada Terminal/SSH**, dari folder app jalankan:
  ```
  php artisan config:clear && php artisan cache:clear && php artisan view:clear
  php artisan config:cache && php artisan route:cache && php artisan view:cache
  ```
  (Database sudah diimport, **tidak perlu** `migrate`.)
- **Kalau tidak ada Terminal**: cukup pastikan tidak ada file cache basi di `bootstrap/cache/` (hapus `config.php`, `routes-*.php`, `packages.php`, `services.php` bila ada). Laravel akan regenerate otomatis.

---

## 7) Khusus aplikasi ini (penting)
- **Gambar** disajikan langsung dari `public/images/...` (tanpa symlink `storage`). Jadi pastikan `public/images/` (logo, hero, destinations, blog) ikut terupload ke web root.
- **Login admin** (dari data yang diimport): `admin@wisatapadang.test` / `password`.
  → **Segera ganti password & email admin** setelah live (menu Profil), demi keamanan.
- **AI Concierge** butuh server bisa koneksi keluar (HTTPS) ke `generativelanguage.googleapis.com`. Sebagian shared hosting memblokir koneksi keluar → kalau diblokir, chat otomatis fallback ke pencarian keyword (tetap jalan, tanpa AI). Tanyakan ke hosting bila perlu “allow outbound https”.
- **Session & cache** pakai database (tabel `sessions` & `cache` sudah ada di SQL) — tidak perlu konfigurasi tambahan.

---

## 8) Checklist uji setelah live
- [ ] Buka beranda → tampil + gambar muncul.
- [ ] `/explore`, `/blog`, `/peta`, `/asisten` → 200, normal.
- [ ] Login admin → ganti password.
- [ ] Coba AI Concierge (kalau outbound https diizinkan).
- [ ] Pastikan `APP_DEBUG=false` (halaman error tidak membocorkan detail).
