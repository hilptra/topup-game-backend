# Game Top-Up Platform — Requirements Document

## 1. Deskripsi Proyek

Game Top-Up Platform adalah sistem yang memungkinkan pengguna melakukan top-up
item atau saldo game melalui website dan aplikasi mobile. Proyek ini dibangun
sebagai portofolio untuk menunjukkan kemampuan full-stack development
(backend API, web frontend, mobile app) dengan arsitektur yang realistis
dan sesuai praktik industri.

**Status:** MVP (Minimum Viable Product) — scope sengaja dibatasi agar
achievable dikerjakan solo dalam waktu wajar (estimasi 10–15 minggu,
part-time).

---

## 2. Tujuan

- Menunjukkan kemampuan membangun REST API yang melayani multi-client
  (web + mobile).
- Menunjukkan pemahaman alur transaksi dan pembayaran dunia nyata
  (order → payment → webhook → fulfillment).
- Menunjukkan penerapan software design principle (abstraction layer,
  separation of concern) lewat arsitektur top-up provider.
- Menghasilkan project yang bisa didemokan end-to-end (bukan sekadar CRUD).

---

## 3. Tech Stack

| Bagian | Teknologi |
|---|---|
| Web Frontend | Next.js, TypeScript, Tailwind CSS |
| UI (Web) | shadcn/ui |
| Web State/Data | TanStack Query, Axios |
| Mobile | Flutter, Dart |
| Mobile State Management | Riverpod |
| Mobile Networking | Dio |
| Backend | Laravel 12, PHP 8.2+ |
| Authentication | Laravel Sanctum |
| API | REST API |
| Database | PostgreSQL |
| Queue | Laravel Queue (database driver untuk MVP, Redis opsional di tahap lanjut) |
| Payment | Midtrans Sandbox |
| Top-Up Provider | Mock Provider (dengan abstraction layer ke provider asli) |
| Testing | Pest/PHPUnit (backend, prioritas utama) |
| Version Control | Git & GitHub |
| Local Dev Environment | Laragon (Apache/Nginx, PHP, PostgreSQL) |

> Catatan: Redis, Swagger otomatis, CI/CD, dan testing menyeluruh di semua
> layer masuk kategori **future improvement**, bukan prioritas MVP.

---

## 4. Struktur Repository

Menggunakan **monorepo sederhana** (satu repository, tiga folder project):

```
topup-game/
├── backend/        (Laravel API)
├── frontend/        (Next.js)
├── mobile/          (Flutter)
├── docs/           (ERD, API docs, diagram arsitektur)
├── AGENTS.md
└── README.md
```

---

## 5. Scope MVP

### 5.1 Fitur Wajib (In Scope)

- Autentikasi customer (register/login) via Laravel Sanctum
- Role dasar: `customer` dan `admin`
- Daftar game & produk (nominal/item top-up)
- Validasi akun game (User ID + Server ID opsional, dummy validation)
- Pembuatan order dari produk yang dipilih
- Pembayaran via Midtrans Sandbox + webhook handler
- Proses top-up otomatis via Mock Provider (dengan Laravel Job/Queue)
- Riwayat transaksi customer
- Admin dashboard sederhana (kelola game, produk, lihat transaksi)

### 5.2 Di Luar Scope MVP (Future Improvements)

- Multi payment gateway
- Sistem referral/affiliate
- Notifikasi realtime (websocket)
- Multi-bahasa / multi-currency
- Rating & review produk
- Redis (caching & queue)
- CI/CD pipeline penuh
- Swagger/OpenAPI lengkap manual
- Testing menyeluruh di semua layer (frontend, mobile)

---

## 6. Entity Relationship (Ringkasan)

Tabel inti:

- `users` — customer & admin (dibedakan lewat kolom `role`)
- `games` — daftar game yang didukung
- `products` — nominal/item top-up per game
- `orders` — order yang dibuat customer (menyimpan **price snapshot**,
  bukan referensi langsung ke harga produk saat ini)
- `payments` — data pembayaran dari Midtrans
- `top_up_transactions` — status proses top-up ke provider (mock/asli)
- `payment_webhook_logs` — log mentah webhook untuk keperluan idempotency
  dan audit

Detail kolom dan tipe data ada di `docs/ERD.md` (dibuat terpisah saat
migration dikerjakan).

---

## 7. Alur Transaksi Utama

```
Customer pilih produk
   ↓
Validasi akun game (dummy)
   ↓
Buat Order (status: pending)
   ↓
Redirect ke Midtrans Sandbox
   ↓
Customer bayar (sandbox)
   ↓
Midtrans kirim webhook ke Laravel
   ↓
Laravel verifikasi & catat payment (idempotent, cek payment_webhook_logs)
   ↓
Order status → paid
   ↓
Job dispatch ke Mock Top-Up Provider (queue)
   ↓
Provider proses (success/pending/failed/expired)
   ↓
Order status final di-update
   ↓
Customer lihat status di riwayat transaksi
```

---

## 8. Prinsip Desain yang Dipegang

1. **Price snapshot** — harga di `orders` disimpan terpisah dari
   `products.price` agar perubahan harga produk tidak memengaruhi order
   yang sudah dibuat.
2. **Idempotency pada webhook** — setiap payload webhook dicatat di
   `payment_webhook_logs` sebelum diproses, untuk mencegah double-processing
   akibat retry dari Midtrans.
3. **Abstraction layer top-up provider** — `TopUpProviderInterface` dengan
   implementasi `MockTopUpProvider`, agar provider asli (mis. Digiflazz,
   APIGames) dapat ditambahkan tanpa mengubah logic inti.
4. **Status berbasis enum**, bukan string bebas, untuk mencegah data
   tidak konsisten.
5. **Backend sebagai satu-satunya sumber kebenaran** — baik web maupun
   mobile berkomunikasi lewat REST API yang sama, tidak ada logic bisnis
   yang diduplikasi di frontend.

---

## 9. Kriteria Selesai (Definition of Done) untuk MVP

- [ ] Customer bisa register, login, dan melihat daftar game & produk
- [ ] Customer bisa membuat order dan membayar lewat Midtrans Sandbox
- [ ] Webhook pembayaran berhasil memproses status order secara idempotent
- [ ] Job top-up berjalan otomatis setelah pembayaran sukses (mock provider)
- [ ] Customer bisa melihat riwayat & status transaksinya
- [ ] Admin bisa melihat semua transaksi dan mengelola game/produk
- [ ] Alur lengkap (order → bayar → top-up sukses) bisa didemokan end-to-end
- [ ] README utama menjelaskan cara menjalankan ketiga bagian (backend,
      web, mobile) secara lokal

---

## 10. Roadmap Pengerjaan (Urutan)

1. Desain ERD & setup Laravel project
2. Migration + model sesuai ERD
3. Autentikasi (Sanctum)
4. Endpoint games & products
5. Endpoint orders
6. Integrasi Midtrans Sandbox + webhook
7. Mock top-up provider + queue job
8. Next.js web app
9. Flutter mobile app (versi minimal dulu)
10. Polish, testing backend, deploy