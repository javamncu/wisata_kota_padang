# PRD / Spesifikasi — Aplikasi Wisata Kota Padang (Fase 3)

> Dokumen ini adalah spesifikasi **Fase 3 (AI Concierge)**. Dibangun **di atas Fase 1** (wajib) dan idealnya setelah Fase 2. Lihat `PRD-Wisata-Padang-Fase-1.md` dan `PRD-Wisata-Padang-Fase-2.md`. Backlog dicantumkan di akhir hanya sebagai konteks arah.

---

## 1. Ringkasan & Tujuan Fase 3

Menambah **AI Concierge / Asisten Rekomendasi** — chat berbahasa natural yang membantu user menemukan destinasi dengan mengetik bebas (mis. *"kuliner pedas dekat pantai yang buka malam"*), lalu membalas dengan rekomendasi nyata **dari database aplikasi**.

**Konsep inti:** AI Concierge adalah **pintu masuk berbahasa natural ke mesin pencarian Fase 1** — bukan otak yang berdiri sendiri. LLM hanya menerjemahkan kalimat manusia menjadi filter; pekerjaan pencarian tetap dilakukan mesin skoring Fase 1.

**Termasuk (in scope):**
- Antarmuka chat publik untuk tanya-jawab rekomendasi.
- Integrasi LLM via API untuk ekstraksi niat (teks → filter).
- Reuse mesin pencarian/skoring Fase 1 untuk menghasilkan rekomendasi.

**Tidak termasuk (out of scope):**
- Percakapan multi-turn dengan riwayat tersimpan (lihat Opsi B di backlog).
- RAG / embeddings / agent dengan function-calling (backlog).
- Membuat data destinasi baru lewat AI (dilarang — lihat guardrail).

---

## 2. Keputusan Desain (PENTING)

**Versi 1 = Opsi A (stateless, tanpa riwayat).**
- Tiap pertanyaan berdiri sendiri; riwayat percakapan **tidak disimpan** ke database.
- **Tidak ada tabel baru.**
- Tidak mendukung follow-up kontekstual (mis. *"yang lebih murah dong"*) — itu ranah Opsi B (backlog).
- Thread chat boleh tampil di sisi browser selama sesi, tapi tidak dipersist di server.

---

## 3. Alur Kerja

```
Pesan user (bahasa bebas)
        ↓
LLM — ekstrak niat (teks → filter JSON)
        ↓
Mesin pencarian Fase 1 (query + skoring) ←── Database destinasi
        ↓
Balasan ramah + kartu destinasi (grounded di database)
```

Yang benar-benar baru di sini **hanya langkah LLM**. Mesin pencarian & database sudah ada dari Fase 1.

---

## 4. Tambahan Tech Stack

- **LLM via API** (HTTP request dari Laravel). **Bukan** self-hosted.
- **Konfigurasi di `.env`**: provider, model, API key — dibuat **swappable** (mudah ganti provider).
- **Mulai dari free tier** (mis. Google Gemini Flash) untuk pengembangan; naik ke **paid tier murah** saat scale.
- Untuk tugas ekstraksi filter yang ringan, gunakan **model tier kecil/murah**.
- **Rate limiting:** pakai Laravel Rate Limiter bawaan (berbasis cache) — tidak perlu tabel.

---

## 5. Implementasi (Laravel)

1. **Endpoint chat** (controller) menerima pesan user.
2. **Panggil API LLM** dengan instruksi mengembalikan **filter dalam bentuk JSON** (lihat kontrak di §6). Contoh keluaran: `{ "category": "kuliner", "zone": "pesisir", "tags": ["pedas"], "waktu_ideal": ["malam"] }`.
3. **Parse JSON**, lalu **jalankan query Eloquent yang sama** seperti halaman Explore — reuse mesin skoring Fase 1. Tidak ada logika pencarian baru.
4. Ambil top-N destinasi.
5. Kembalikan sebagai **gelembung chat + kartu destinasi** yang nge-link ke halaman Detail.
6. *(Opsional)* satu panggilan LLM lagi untuk kalimat pembuka ramah. Untuk hemat, langkah ini bisa di-template tanpa panggilan tambahan.

> Tiap pesan = **1–2 panggilan API**. Desain "ekstrak ke filter" menjaga ukuran request tetap kecil → biaya per pesan kecil.

---

## 6. Kontrak Ekstraksi Filter (LLM → Backend)

LLM diminta mengembalikan **hanya JSON** (tanpa teks lain) dengan field yang memetakan ke atribut Fase 1. Semua field **opsional**; yang tidak relevan dikosongkan/diabaikan.

```json
{
  "keyword": "string | null",
  "category": "salah satu kategori utama | null",
  "zone": "enum zone | null",
  "price_range": "enum price_range | null",
  "indoor_outdoor": "enum indoor_outdoor | null",
  "duration": "enum duration | null",
  "cocok_untuk": ["enum...", "..."],
  "waktu_ideal": ["enum...", "..."],
  "tags": ["suasana/aktivitas/fasilitas...", "..."]
}
```

Backend mem-validasi nilai terhadap enum/tag yang sah, lalu menjalankan query + skoring Fase 1 (bobot skoring mengikuti aturan kuis Fase 1).

---

## 7. Spec Halaman — Chat AI Concierge

- **Pintu masuk:** tombol/CTA di navbar dan/atau Hero (mis. *"Tanya AI Concierge"*).
- **Antarmuka chat:** gelembung pesan (user & asisten), kotak input + tombol kirim di bawah, indikator "sedang mengetik".
- **Contoh prompt pemicu** (chip yang bisa diklik): mis. *"Kuliner pedas dekat pantai"*, *"Wisata keluarga sehari"*, *"Tempat instagramable yang murah"*.
- **Balasan asisten:** kalimat pembuka ramah + **kartu destinasi inline** (foto, nama, rating, kisaran harga, link Detail).
- **Empty/no-match state:** pesan ramah + saran melonggarkan kriteria.
- Karena stateless: thread tampil selama sesi browser, tidak disimpan di server.

---

## 8. Kebijakan Akses & Rate-Limit

- **Terbuka untuk semua** (termasuk guest), **tetapi dibatasi** demi mengendalikan biaya.
- Batas lebih longgar untuk **user login** dibanding guest; **admin** tanpa batas.
- Angka batas (mis. X pesan/hari per guest, Y per user) **dikonfigurasi** (idealnya lewat Settings Fase 2 atau `.env`).
- Implementasi via Laravel Rate Limiter (cache) — tidak perlu tabel.

---

## 9. Guardrail (WAJIB)

- **Hanya merekomendasikan destinasi dari database.** LLM **dilarang mengarang** tempat. Rekomendasi selalu berasal dari hasil query, bukan "ingatan" LLM.
- **Validasi sisi backend:** filter dari LLM dicocokkan ke enum/tag yang sah sebelum query.
- **No-match:** jika tidak ada hasil, balas jujur + sarankan melonggarkan kriteria (jangan memaksakan).
- **Off-topic:** jika pertanyaan di luar konteks wisata Padang, arahkan kembali dengan sopan.
- **Bahasa:** balas dalam Bahasa Indonesia (mengikuti bahasa user).
- **Jangan bocorkan** detail sistem/data internal.
- **Kendali biaya:** rate-limit + model kecil untuk ekstraksi + (opsional) balasan ramah di-template.

---

## 10. Model Data

**Tidak ada tabel baru** (versi stateless). Rate-limit memakai cache, bukan database. Tidak ada perubahan pada skema Fase 1 & 2.

---

## 11. Konteks Arah (Backlog — peningkatan AI Concierge)

- **Opsi B — percakapan stateful:** simpan riwayat (`conversations` + `messages`), dukung follow-up multi-turn, bahan analitik.
- **RAG (embeddings + pencarian semantik):** menangkap maksud yang samar lebih baik; butuh vector store.
- **Agent (function-calling):** LLM memanggil beberapa fungsi & bernalar bertahap.
- **Backlog produk lain:** Trip Planner penuh + optimasi rute, Monetisasi (affiliate/sponsored UMKM), Analitik admin lanjutan, Kalender Event & Festival, Hidden Gems, Travel Passport, Galeri Komunitas, Multi-bahasa, Mobile/PWA, Penginapan via affiliate.

---

*Akhir dokumen Fase 3.*
