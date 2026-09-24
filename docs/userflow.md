# User Flow

Dokumen ini menjelaskan alur penggunaan sistem dari sisi customer dan
admin, untuk dijadikan acuan saat membangun UI (web & mobile) maupun
endpoint API di backend.

---

## 1. Flow Customer — Registrasi & Login

```mermaid
flowchart TD
    A[Buka aplikasi/web] --> B{Sudah punya akun?}
    B -- Tidak --> C[Isi form register: nama, email, password]
    C --> D[Submit ke POST /api/auth/register]
    D --> E[Akun dibuat, auto login]
    B -- Ya --> F[Isi form login: email, password]
    F --> G[Submit ke POST /api/auth/login]
    G --> H{Valid?}
    H -- Ya --> I[Terima token Sanctum, simpan di client]
    H -- Tidak --> J[Tampilkan pesan error]
    E --> K[Masuk ke Home]
    I --> K
```

**Catatan implementasi:**
- Web (Next.js): token disimpan di cookie httpOnly atau memory + refresh strategy.
- Mobile (Flutter): token disimpan lewat Flutter Secure Storage.

---

## 2. Flow Customer — Browsing & Membuat Order

```mermaid
flowchart TD
    A[Home] --> B[Lihat daftar game GET /api/games]
    B --> C[Pilih game]
    C --> D[Lihat daftar produk/nominal GET /api/games/id/... atau GET /api/products?game_id=]
    D --> E[Pilih produk]
    E --> F[Isi User ID]
    F --> G{Game butuh Server ID?}
    G -- Ya --> H[Isi Server ID]
    G -- Tidak --> I[Lewati]
    H --> J[Validasi akun POST /api/games/id/validate-account]
    I --> J
    J --> K{Akun valid?}
    K -- Tidak --> F
    K -- Ya --> L[Konfirmasi order: ringkasan produk + harga]
    L --> M[Submit POST /api/orders]
    M --> N[Order dibuat, status: pending]
    N --> O[Redirect ke halaman pembayaran Midtrans]
```

**Catatan:**
- Validasi akun game di MVP bersifat dummy (tidak benar-benar mengecek
  ke server game), tapi tetap melalui endpoint terpisah agar strukturnya
  siap diisi validasi asli di kemudian hari.
- Harga yang ditampilkan di layar konfirmasi harus sama dengan
  `price_snapshot` yang nanti disimpan di order — jangan ambil ulang dari
  `products.price` setelah order dibuat.

---

## 3. Flow Customer — Pembayaran

```mermaid
flowchart TD
    A[Order dibuat, status: pending] --> B[Sistem membuat transaksi ke Midtrans Sandbox]
    B --> C[Customer diarahkan ke halaman/pop-up pembayaran Midtrans]
    C --> D[Customer pilih metode & bayar]
    D --> E[Midtrans memproses pembayaran]
    E --> F[Midtrans kirim webhook ke POST /api/webhooks/payment]
    F --> G[Backend catat payload ke payment_webhook_logs]
    G --> H{Sudah pernah diproses?}
    H -- Ya, duplikat --> I[Abaikan, cukup catat log]
    H -- Belum --> J{Signature valid?}
    J -- Tidak --> K[Tolak, catat sebagai invalid]
    J -- Ya --> L[Update payments.status]
    L --> M{Status settlement?}
    M -- Ya --> N[Order status → paid]
    M -- Tidak, expire/cancel/deny --> O[Order status → failed/expired]
    N --> P[Dispatch job top-up ke queue]
```

**Catatan:**
- Langkah G–H adalah mekanisme idempotency — krusial karena Midtrans bisa
  mengirim webhook yang sama lebih dari sekali.
- Customer di sisi client (web/mobile) melakukan polling atau menerima
  update status order setelah kembali dari halaman pembayaran, bukan
  menunggu webhook secara langsung (webhook adalah proses server-to-server).

---

## 4. Flow Sistem — Proses Top-Up (Background Job)

```mermaid
flowchart TD
    A[Job top-up di-dispatch setelah order status: paid] --> B[Job berjalan di queue]
    B --> C[Panggil TopUpProviderInterface]
    C --> D[MockTopUpProvider memproses]
    D --> E{Hasil simulasi}
    E -- success --> F[top_up_transactions.status = success]
    E -- pending --> G[Job dijadwalkan ulang / dicek lagi nanti]
    E -- failed --> H[top_up_transactions.status = failed]
    E -- expired --> I[top_up_transactions.status = failed, catat sebagai expired]
    F --> J[orders.status = success]
    H --> K[orders.status = failed]
    I --> K
```

**Catatan:**
- Proses ini berjalan asynchronous lewat Laravel Queue, tidak memblokir
  response ke customer saat pembayaran selesai.
- `attempt_count` di `top_up_transactions` digunakan untuk membatasi
  jumlah retry jika status masih `pending`.

---

## 5. Flow Customer — Melihat Riwayat Transaksi

```mermaid
flowchart TD
    A[Menu Riwayat Transaksi] --> B[GET /api/orders]
    B --> C[Tampilkan list order milik user, terbaru di atas]
    C --> D[Pilih salah satu order]
    D --> E[GET /api/orders/id]
    E --> F[Tampilkan detail: produk, harga, status, waktu]
```

---

## 6. Flow Admin — Kelola Game & Produk

```mermaid
flowchart TD
    A[Login sebagai admin] --> B[Dashboard Admin]
    B --> C[Menu Kelola Game]
    C --> D[Tambah/Edit/Nonaktifkan game]
    B --> E[Menu Kelola Produk]
    E --> F[Pilih game]
    F --> G[Tambah/Edit/Nonaktifkan produk & harga]
```

---

## 7. Flow Admin — Monitoring Transaksi

```mermaid
flowchart TD
    A[Dashboard Admin] --> B[Menu Transaksi]
    B --> C[Lihat semua order, filter by status]
    C --> D[Pilih satu order]
    D --> E[Lihat detail: data payment, data top_up_transactions, log webhook terkait]
    E --> F{Perlu tindakan manual?}
    F -- Ya, mis. top-up gagal terus --> G[Admin proses manual / retry]
    F -- Tidak --> H[Selesai, hanya monitoring]
```

> Fitur retry manual oleh admin bersifat opsional di MVP — bisa disebut
> di README sebagai "future improvement" jika waktu tidak mencukupi.

---

## 8. Ringkasan Endpoint yang Terlibat per Flow

| Flow | Endpoint Utama |
|---|---|
| Register/Login | `POST /api/auth/register`, `POST /api/auth/login` |
| Lihat game & produk | `GET /api/games`, `GET /api/games/{game}`, `GET /api/products` |
| Validasi akun | `POST /api/games/{game}/validate-account` |
| Buat order | `POST /api/orders` |
| Lihat riwayat order | `GET /api/orders`, `GET /api/orders/{order}` |
| Inisiasi pembayaran | `POST /api/payments` |
| Webhook pembayaran | `POST /api/webhooks/payment` |