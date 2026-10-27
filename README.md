# HydroDash

## 1. Judul Proyek & Ringkasan
**HydroDash**
HydroDash merupakan sebuah sistem pemantauan kualitas/kuantitas air (dan hidroponik) yang menerima data pengukuran dari perangkat IoT (seperti ESP32) secara *real-time*. Sistem ini menyediakan sebuah dashboard interaktif dengan sistem pelaporan terpadu untuk kemudahan analisis dan ekstraksi data.

## 2. Teknologi yang Digunakan (Tech Stack)
- **Frontend**: Laravel Blade, Tailwind CSS 4, Axios, Vite
- **Backend**: PHP 8.2+, Laravel (v12), domPDF (barryvdh/laravel-dompdf)
- **Database & Storage**: SQLite / MySQL, penyimpanan file Laravel.
- **Perangkat Keras / IoT**: Integrasi sistem yang menerima data secara berkelanjutan (contoh dari mikrokontroler ESP32).

## 3. Fitur Utama & Logika Bisnis
- **Penerimaan Data IoT Masa Nyata (Real-Time Data)**: Kemampuan untuk menerima pembaruan data melalui *routes* API/Web khusus (`/terima-data`).
- **Dashboard Terpusat**: Menampilkan data pengukuran terkini yang selalu diperbarui.
- **Sistem Laporan Terpadu (Unified Report System)**: Mampu memfilter dan melihat laporan berdasarkan kriteria: Harian, Bulanan, Tahunan, dan Periode kustom.
- **Export Informasi**: Memungkinkan data diekspor ke dalam format PDF (`/report/pdf`) dan format lain yang sesuai (`/report/export`).

## 4. Struktur Direktori Proyek
- `app/`: Menempatkan logika Controller (`DashboardController`, `ReportController`) dan pengelolaan Model aplikasi.
- `routes/`: Berisi definisi *routes* seperti `web.php` untuk interaksi pengguna/ESP32, dan `api.php` untuk AJAX request (`/get-latest-hydro`).
- `resources/`: Berisi tampilan (Blade template) antarmuka dashboard serta laporan.
- `public/`: Direktori publik untuk memuat aset statis web.
- `database/`: File untuk manajemen database (migration, seeder).

## 5. Panduan Instalasi & Cara Menjalankan Proyek
Instruksi untuk menjalankan aplikasi di server lokal:

1. **Instalasi *Dependencies***:
   ```bash
   composer install
   npm install
   ```
2. **Environment Variables**:
   Buat salinan konfigurasi dan tetapkan koneksi database:
   ```bash
   cp .env.example .env
   ```
3. **Persiapan Database & Security Key**:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
4. **Jalankan *Development***:
   Buka dua terminal terpisah untuk menjalankan aplikasi:
   ```bash
   npm run dev
   php artisan serve
   ```

## 6. Endpoint API / Skema Database
Daftar *routes* dan endpoint utama yang dapat diakses:
- **Inti/IoT**:
  - `GET /` : Halaman Dashboard
  - `POST /terima-data` : Endpoint bagi ESP32 untuk mengirimkan data sensor.
  - `GET /api/get-latest-hydro` : *Routes* AJAX untuk mengambil data terbaru secara otomatis.
- **Laporan**:
  - `GET /report` : Menampilkan ringkasan data berdasarkan jenis laporan (type=daily/monthly/yearly).
  - `GET /report/export` : Meng-*export* data sebagai file (seperti CSV/Excel).
  - `GET /report/pdf` : Membuat (*generate*) dan mengunduh dokumen PDF laporan.

## 7. Autentikasi Perangkat IoT (ESP32)
Mulai fase hardening pertama, endpoint `POST /terima-data` dan `POST /api/terima-data` **wajib mengirim device token**. Token diatur di file `.env`:

```env
IOT_DEVICE_TOKEN=
```

Contoh pengiriman data dari sketch ESP32 (Arduino):

```cpp
HTTPClient http;
http.begin(client, "http://hydrodash.test/terima-data"); // atau http://IP-SERVER/terima-data
http.addHeader("Content-Type", "application/x-www-form-urlencoded");
http.addHeader("X-Device-Token", "hd_TOKEN_ANDA");       // <-- WAJIB: token device

String payload =
    "ph=" + String(ph, 2) +
    "&tds=" + String(tds, 0) +
    "&suhu=" + String(suhu, 2) +
    "&status_pompa_ph=" + String(pompaPh ? "ON" : "OFF") +     // opsional
    "&status_pompa_tds=" + String(pompaTds ? "ON" : "OFF") +   // opsional
    "&status_pendingin=" + String(pendingin ? "ON" : "OFF");   // opsional

int code = http.POST(payload);
// 201 = data tersimpan, 401 = token salah/tidak ada, 400 = nilai tidak valid
http.end();
```

Alternatif tanpa header: tambahkan `&token=hd_TOKEN_ANDA` ke payload. Batas validasi: `ph` 0–14, `tds` 0–5000 PPM, `suhu` −10–60 °C; field `status_*` hanya menerima `ON`/`OFF`. Setelah mengubah `IOT_DEVICE_TOKEN`, jalankan `php artisan config:clear`.

## 8. Pemeliharaan Data (Prune Otomatis)
Data sensor bertumbuh terus (±6 juta baris/tahun bila dikirim tiap 5 detik). Perintah `sensors:prune` menghapus data yang lebih tua dari batas retensi (`config/hydro.php` → `prune.retention_days`, default 365 hari):

```bash
php artisan sensors:prune            # pakai default config
php artisan sensors:prune --days=90  # batas retensi kustom
```

Jadwal otomatis (harian pukul 03:00) sudah terdaftar di `routes/console.php`. Di Windows, jadwal Laravel butuh salah satu dari:

```bash
php artisan schedule:work    # proses background; jalankan saat development
```

atau daftarkan **Task Scheduler** (setiap menit) yang mengeksekusi `php artisan schedule:run` di folder proyek. Setelah mengubah `IOT_DEVICE_TOKEN`, jalankan `php artisan config:clear`.
