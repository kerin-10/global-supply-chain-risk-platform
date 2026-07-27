# 🌍 SupplyRisk - Global Shipping Risk Intelligence Platform

SupplyRisk merupakan aplikasi berbasis Laravel yang dikembangkan untuk membantu memantau risiko pengiriman internasional secara real-time. Sistem ini mengintegrasikan berbagai sumber data eksternal seperti informasi negara, cuaca, ekonomi, pelabuhan, berita global, serta nilai tukar mata uang sehingga pengguna dapat melakukan analisis risiko pengiriman secara lebih akurat.

---

# Fitur Utama

- Dashboard Monitoring Global
- Monitoring Data Negara
- Monitoring Cuaca Global
- Monitoring Pelabuhan Global
- Monitoring Nilai Tukar Mata Uang
- Monitoring Berita Global
- Analisis Sentimen Berita
- Perhitungan Risiko Negara
- Visualisasi Data
- Perbandingan Negara
- Watchlist Negara
- Manajemen Artikel
- Manajemen Pengguna
- Sinkronisasi Data API

---

# Teknologi yang Digunakan

- Laravel 12
- PHP 8.2+
- MySQL
- Bootstrap 5
- Chart.js
- Leaflet.js
- Axios
- TomSelect
- Composer
- Node.js

---

# API yang Digunakan

| API | Fungsi |
|------|---------|
| REST Countries API | Data Negara |
| Open-Meteo API | Data Cuaca |
| World Bank API | Data Ekonomi |
| GNews API | Berita Global |
| World Port Index | Data Pelabuhan |
| Exchange Rate API | Nilai Tukar Mata Uang |

---

# Persyaratan Sistem

- PHP 8.2 atau lebih baru
- Composer
- Node.js
- MySQL
- Git
- Koneksi Internet

---

# Cara Menggunakan Project dari GitHub

## 1. Clone Repository

```bash
git clone https://github.com/USERNAME/NAMA-REPOSITORY.git
```

Masuk ke folder project

```bash
cd NAMA-REPOSITORY
```

---

## 2. Install Dependency Laravel

```bash
composer install
```

---

## 3. Install Dependency Frontend

```bash
npm install
```

---

## 4. Copy File Environment

Windows

```bash
copy .env.example .env
```

Linux / Mac

```bash
cp .env.example .env
```

---

## 5. Generate Laravel Key

```bash
php artisan key:generate
```

---

## 6. Buat Database

Misalnya

```
supplyrisk
```

---

## 7. Atur Database

Buka file

```
.env
```

Lalu ubah

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supplyrisk
DB_USERNAME=root
DB_PASSWORD=
```

---

## 8. Jalankan Migration

```bash
php artisan migrate
```

Jika project memiliki Seeder

```bash
php artisan db:seed
```

---

## 9. Build Asset

Development

```bash
npm run dev
```

Production

```bash
npm run build
```

---

## 10. Jalankan Project

```bash
php artisan serve
```

Buka browser

```
http://127.0.0.1:8000
```

---

# Konfigurasi API

Masukkan API Key pada file

```
.env
```

Contoh

```env
GNEWS_API_KEY=xxxxxxxxxxxxxxxx

EXCHANGE_RATE_API_KEY=xxxxxxxxxxxxxxxx
```

Pastikan seluruh API dapat diakses sebelum melakukan sinkronisasi data.

---

# Sinkronisasi Data

Sebelum menggunakan sistem, lakukan sinkronisasi agar data yang tersimpan berasal dari API terbaru.

## Sinkronisasi Data Negara ✅

Mengambil data dari REST Countries API

```bash
php artisan countries:sync
```

Data yang diperbarui

- Nama Negara
- ISO2
- ISO3
- Populasi
- Mata Uang
- Bahasa
- Ibu Kota
- Luas Wilayah
- Koordinat
- Bendera

---

## Sinkronisasi Data Cuaca

Melalui Website

```
Dashboard
↓
Pilih Negara
↓
Klik Sinkronisasi
```

Data yang diperbarui

- Suhu
- Curah Hujan
- Kelembaban
- Kecepatan Angin
- Risiko Badai

---

## Sinkronisasi Data Ekonomi

Melalui Website

```
Dashboard
↓
Pilih Negara
↓
Klik Sinkronisasi
```

Data yang diperbarui

- GDP
- Inflasi
- Populasi
- Nilai Ekspor
- Nilai Impor

---

## Sinkronisasi Data Berita

Melalui Website

```
Dashboard
↓
Berita
↓
Pilih Negara
↓
Klik Muat & Analisis Berita
```

Data yang diperbarui

- Judul Berita
- Isi Berita
- Analisis Sentimen
- Skor Positif
- Skor Negatif

---

## Sinkronisasi Data Pelabuhan

Melalui Website

```
Dashboard
↓
Pelabuhan
↓
Klik Sinkronisasi Pelabuhan
```

Data yang diperbarui

- Nama Pelabuhan
- Nomor WPI
- Lokasi Pelabuhan
- Kemacetan Pelabuhan

---

# Sinkronisasi Melalui Terminal

**Command yang sudah tersedia pada proyek saat ini:**

```bash
php artisan countries:sync
```

> Jika nanti ditambahkan Artisan Command untuk sinkronisasi data lainnya, pengguna dapat menjalankannya melalui terminal, misalnya:

```bash
php artisan weather:sync
php artisan economy:sync
php artisan ports:sync
php artisan news:sync
php artisan sync:all
```

> **Catatan:** Command di atas baru dapat digunakan setelah dibuat pada proyek. Jika belum tersedia, gunakan fitur sinkronisasi melalui halaman web.

---

# Struktur Folder

```
app/
│
├── Console/
├── Http/
├── Models/
├── Services/
├── Providers/
│
resources/
│
├── views/
├── css/
├── js/
│
routes/
│
├── web.php
├── api.php
│
public/
storage/
database/
```

---

# Login

Administrator

```
Email    :
Password :
```

User

```
Email    :
Password :
```

---

# Catatan

- Pastikan MySQL aktif.
- Pastikan koneksi internet tersedia.
- Pastikan API Key sudah benar.
- Jalankan `php artisan countries:sync` sebelum menggunakan sistem untuk pertama kali.
- Sinkronisasi data lainnya dapat dilakukan melalui halaman web.

---

# Pengembang

**SupplyRisk - Global Shipping Risk Intelligence Platform**

Dikembangkan menggunakan **Laravel Framework** sebagai media pembelajaran dan implementasi sistem monitoring risiko pengiriman global berbasis data real-time.
