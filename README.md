# HydroDash

## 1. Tajuk Projek & Ringkasan
**HydroDash**
HydroDash merupakan sebuah sistem pemantauan kualiti/kuantiti air (dan hidroponik) yang menerima data ukuran daripada peranti IoT (seperti ESP32) secara langsung. Sistem ini menyediakan satu papan pemuka interaktif dengan sistem pelaporan bersepadu untuk kemudahan analisis dan pengekstrakan data.

## 2. Teknologi yang Digunakan (Tech Stack)
- **Frontend**: Laravel Blade, Tailwind CSS 4, Axios, Vite
- **Backend**: PHP 8.2+, Laravel (v12), domPDF (barryvdh/laravel-dompdf)
- **Pangkalan Data & Storage**: SQLite / MySQL, storan fail Laravel.
- **Perkakasan / IoT**: Integrasi sistem menerima data secara berterusan (contoh dari mikropengawal ESP32).

## 3. Ciri-Ciri Utama & Logik Perniagaan
- **Penerimaan Data IoT Masa Nyata (Real-Time Data)**: Keupayaan untuk menerima bacaan data melalui laluan API/Web khusus (`/terima-data`).
- **Papan Pemuka Berpusat (Dashboard)**: Memaparkan data ukuran semasa yang sentiasa dikemas kini.
- **Sistem Laporan Bersepadu (Unified Report System)**: Boleh menapis dan melihat laporan mengikut kriteria: Harian, Bulanan, Tahunan, dan Tempoh kustom.
- **Eksport Maklumat**: Membolehkan data dieksport ke dalam format PDF (`/report/pdf`) dan format lain yang sesuai (`/report/export`).

## 4. Struktur Direktori Projek
- `app/`: Menempatkan logik Controller (`DashboardController`, `ReportController`) dan pengurusan Model aplikasi.
- `routes/`: Mengandungi definisi laluan seperti `web.php` untuk interaksi pengguna/ESP32, dan `api.php` untuk AJAX request (`/get-latest-hydro`).
- `resources/`: Mengandungi paparan (`views`) UI papan pemuka serta laporan.
- `public/`: Direktori awam untuk memuatkan aset statik web.
- `database/`: Fail untuk pengurusan pangkalan data (migrasi, seeder).

## 5. Panduan Pemasangan & Cara Menjalankan Projek
Arahan untuk melaksanakan aplikasi di pelayan tempatan:

1. **Pemasangan Dependensi**:
   ```bash
   composer install
   npm install
   ```
2. **Pembolehubah Persekitaran (Environment Variables)**:
   Buat salinan tetapan dan tetapkan sambungan pangkalan data:
   ```bash
   cp .env.example .env
   ```
3. **Persediaan Pangkalan Data & Kunci Keselamatan**:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
4. **Jalankan Pembangunan**:
   Buka dua terminal berasingan untuk menjalankan aplikasi:
   ```bash
   npm run dev
   php artisan serve
   ```

## 6. Endpoint API / Skema Pangkalan Data
Laluan dan endpoint utama yang boleh diakses:
- **Teras/IoT**:
  - `GET /` : Halaman Papan Pemuka
  - `POST /terima-data` : Endpoint bagi ESP32 menghantar data sensor.
  - `GET /api/get-latest-hydro` : Laluan AJAX bagi mendapatkan data terbaru secara automatik.
- **Laporan**:
  - `GET /report` : Memaparkan ringkasan data mengikut jenis laporan (type=daily/monthly/yearly).
  - `GET /report/export` : Mengeksport data sebagai fail (seperti CSV/Excel).
  - `GET /report/pdf` : Menjana dan memuat turun dokumen PDF laporan.
