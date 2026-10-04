# Panduan Penggunaan SIDONA

Sistem Informasi Donasi dan Audit. Fitur utamanya adalah pengelolaan donasi untuk program/campaign, dan yang membuat sistem ini "bisa dipakai untuk audit" adalah jejak transaksi lengkap, log yang tidak bisa dimanipulasi diam diam, dan pemisahan tugas antar role. Dokumen ini menjelaskan cara memasang, menjalankan, dan mendemokan seluruh fiturnya.

Rujukan desain awal ada di [`docs/superpowers/specs/2026-09-21-sidona-design.md`](superpowers/specs/2026-09-21-sidona-design.md). Rincian tiap tahap pembangunan ada di [`docs/superpowers/plans/`](superpowers/plans/). Diagram alur kerja tiap fitur (donasi, penyaluran dana, hash chain, deteksi anomali, laporan berchecksum) ada di [`ALUR_KERJA.md`](ALUR_KERJA.md).

---

## 1. Ringkasan Teknis

| Komponen | Pilihan |
|---|---|
| Framework | Laravel 13 |
| Frontend | Livewire 3, Tailwind CSS 4 |
| Database | SQLite (default, tanpa server database terpisah) |
| PDF laporan | barryvdh/laravel-dompdf |
| Testing | Pest 4 |
| Bahasa aplikasi | Indonesia (`APP_LOCALE=id`) |
| PHP minimum | 8.3 (disarankan 8.4) |

---

## 2. Instalasi

### 2.1 Persyaratan

- PHP 8.3 atau lebih baru beserta Composer
- Node.js beserta npm

### 2.2 Setup otomatis

Project ini sudah menyediakan Composer script `setup` yang mengerjakan semua langkah dasar sekaligus: install dependency, salin `.env`, generate `APP_KEY`, migrasi database, install dependency JS, dan build asset.

```bash
composer install
composer run setup
```

### 2.3 Langkah tambahan yang wajib dilakukan manual

Script `setup` di atas **tidak** menjalankan dua perintah berikut, padahal keduanya dibutuhkan supaya fitur upload berjalan normal:

```bash
php artisan storage:link
```

Perintah ini wajib supaya foto sampul program donasi dan bukti transfer donasi bisa diakses lewat browser (keduanya disimpan di disk `public`).

Disarankan juga mengganti nama aplikasi di `.env`, karena defaultnya masih `APP_NAME=Laravel`:

```
APP_NAME=SIDONA
```

### 2.4 Email

`MAIL_MAILER` di `.env` default nya `log`, artinya email verifikasi donasi (lihat bagian 5) tidak benar benar terkirim, hanya tercatat di `storage/logs/laravel.log`. Ini sudah cukup untuk demo. Kalau ingin email sungguhan terkirim, ganti konfigurasi mail di `.env` sesuai provider yang dipakai.

### 2.5 Menjalankan aplikasi

```bash
composer run dev
```

Perintah ini menjalankan server Laravel dan proses build Vite sekaligus. Kalau mau menjalankan manual secara terpisah:

```bash
php artisan serve
npm run dev
```

---

## 3. Data Uji dan Akun Demo

Isi database dengan data uji:

```bash
php artisan migrate:fresh --seed
```

Seeder membuat 5 akun (password sama untuk semua, yaitu `password`):

| Email | Nama | Role |
|---|---|---|
| `admin@sidona.test` | Admin SIDONA | Admin |
| `bendahara1@sidona.test` | Bendahara Satu | Bendahara |
| `bendahara2@sidona.test` | Bendahara Dua | Bendahara |
| `auditor1@sidona.test` | Auditor Satu | Auditor |
| `auditor2@sidona.test` | Auditor Dua | Auditor |

Seeder juga membuat:
- 6 program donasi (4 berstatus aktif, 2 selesai)
- 6 sampai 10 donasi per program dengan berbagai status (menunggu, terverifikasi, ditolak), termasuk beberapa donasi dengan nominal ekstrem yang sengaja dipasang untuk memicu deteksi anomali
- 2 sampai 4 pengajuan penyaluran dana per program dengan berbagai status, termasuk satu persetujuan yang sengaja disetujui sangat cepat (di bawah 60 detik) untuk memicu deteksi anomali
- Riwayat login untuk semua akun, termasuk 3 percobaan login gagal beruntun untuk `bendahara1@sidona.test` dalam rentang 15 menit, sengaja dipasang untuk memicu deteksi anomali

Ketiga skenario anomali di atas semuanya bisa langsung dilihat di halaman Dashboard Anomali (lihat bagian 6.4) begitu selesai seeding, tanpa perlu setup tambahan.

---

## 4. Alur untuk Donatur (Tanpa Login)

Siapa saja bisa mengakses halaman berikut tanpa perlu akun:

| Halaman | Alamat |
|---|---|
| Daftar program donasi aktif | `/program` |
| Detail program dan form donasi | `/program/{id}` |
| Cek status donasi lewat kode referensi | `/donasi/cek` |

Alur donasi:
1. Donatur membuka detail program, melihat progress dana terkumpul, target, dan rekening tujuan transfer.
2. Donatur mengisi form donasi (nama, kontak, nominal, waktu transfer, bukti transfer), lalu mendapat kode referensi unik.
3. Donatur bisa mengecek status donasinya kapan saja lewat halaman cek status memakai kode referensi tersebut.
4. Donasi berstatus "menunggu" sampai diverifikasi oleh Bendahara.

---

## 5. Alur untuk Bendahara

Login di `/login`. Setelah login, halaman utama Bendahara ada di `/campaigns` (daftar program), `/donasi` (daftar donasi), dan `/penyaluran` (daftar penyaluran dana).

**Kewenangan Bendahara:**
- Membuat dan mengubah program donasi (`/campaigns/create`, `/campaigns/{id}/edit`), termasuk mengatur target dana, periode, rekening tujuan, dan foto sampul.
- Memverifikasi atau menolak donasi yang berstatus menunggu di halaman `/donasi`. Menolak donasi wajib mengisi alasan penolakan.
- Begitu donasi diverifikasi, email pemberitahuan otomatis dikirim ke kontak donatur (kalau kontaknya berupa alamat email).
- Mengajukan penyaluran dana dari program (`/campaigns/{id}/penyaluran/ajukan`), dengan jumlah yang tidak boleh melebihi saldo program yang tersedia.

**Yang tidak bisa dilakukan Bendahara:** menyetujui pengajuan penyaluran dana (termasuk pengajuannya sendiri), menghapus program, dan mengakses seluruh halaman audit/laporan (khusus Auditor).

---

## 6. Alur untuk Admin

Login di `/login`. Admin berbagi halaman `/campaigns`, `/donasi`, dan `/penyaluran` yang sama dengan Bendahara.

**Kewenangan Admin:**
- Membuat, mengubah, dan menghapus program donasi. Penghapusan hanya bisa dilakukan selama program tersebut belum punya donasi atau penyaluran dana sama sekali.
- Menyetujui atau menolak pengajuan penyaluran dana di halaman `/penyaluran`. Menolak wajib mengisi alasan.

**Aturan pemisahan tugas (penting):** Admin **tidak bisa** menyetujui pengajuan penyaluran dana yang diajukan oleh dirinya sendiri. Sistem menegakkan ini di level backend (bukan cuma disembunyikan di tampilan), plus mengecek ulang saldo program yang tersedia saat persetujuan diproses, supaya dua pengajuan yang diproses bersamaan tidak membuat saldo minus.

---

## 7. Alur untuk Auditor

Login di `/login`. Auditor tidak bisa membuat, mengubah, memverifikasi, atau menyetujui apa pun di seluruh sistem, murni read only. Sebagai gantinya, Auditor punya akses eksklusif ke halaman berikut:

### 7.1 Verifikasi Integritas Log (`/audit/integritas`)

Menjalankan ulang perhitungan hash chain dari seluruh isi `activity_logs`, membandingkan dengan hash yang tersimpan. Kalau semua cocok, chain dinyatakan valid. Kalau ada satu baris yang datanya pernah diubah langsung lewat database (di luar aplikasi), chain akan terputus mulai dari baris tersebut dan sistem menunjukkan baris mana yang mencurigakan.

**Cara demo fitur ini:**

```bash
php artisan demo:tamper-log
```

Perintah ini sengaja mengubah baris activity log paling baru langsung lewat database, melewati aplikasi (jadi tidak ikut tercatat oleh mekanisme hash chain yang normal). Setelah menjalankan perintah ini, buka `/audit/integritas` sebagai Auditor, sistem akan langsung menunjukkan chain sudah rusak mulai dari baris yang diutak atik tadi.

### 7.2 Log Aktivitas (`/audit/aktivitas`)

Daftar seluruh activity log dengan filter jenis aksi, user, dan rentang tanggal. Bisa diexport ke PDF berchecksum.

### 7.3 Log Login (`/audit/login`)

Daftar seluruh percobaan login (berhasil maupun gagal), bisa diexport ke PDF berchecksum.

### 7.4 Dashboard Anomali (`/audit/anomali`)

Menandai otomatis:
- Donasi dengan nominal jauh di atas rata rata program yang sama (lebih dari rata rata ditambah dua kali standar deviasi)
- Pengajuan penyaluran dana yang disetujui kurang dari 60 detik setelah diajukan
- Percobaan login gagal tiga kali atau lebih dalam rentang 15 menit untuk email yang sama

### 7.5 Laporan (`/laporan/donasi`, `/laporan/penyaluran`, `/laporan/saldo`)

Tiga laporan keuangan: rekap donasi, rekap penyaluran dana, dan ringkasan saldo tiap program. Semuanya bisa diexport ke PDF (`/laporan/unduh/{kode}`) yang menyertakan checksum SHA256 di footernya.

### 7.6 Cek Keaslian Laporan (`/laporan/cek-keaslian`)

Auditor bisa mengunggah ulang file PDF laporan yang pernah diunduh, sistem menghitung ulang checksumnya dan membandingkan dengan yang tersimpan di database, sekaligus menunjukkan siapa yang awalnya membuat laporan tersebut. Kalau file PDF pernah diedit setelah diunduh, checksumnya tidak akan cocok lagi.

---

## 8. Skema Database

**Tabel utama (data transaksi inti):**
- `campaigns`: program donasi (nama, deskripsi, foto sampul, target dana, rekening tujuan, periode, status)
- `donations`: donasi masuk (kode referensi, data donatur, nominal, bukti transfer, waktu transfer, status, siapa yang memverifikasi)
- `disbursements`: penyaluran dana (jumlah, keterangan, siapa yang mengajukan, siapa yang menyetujui, status)

**Tabel pendukung:**
- `users`: akun internal beserta kolom role
- `login_logs`: setiap percobaan login, berhasil maupun gagal
- `activity_logs`: setiap aksi penting beserta hash chain (`prev_hash`, `hash`)
- `report_exports`: riwayat export laporan PDF beserta checksumnya

---

## 9. Menjalankan Test

```bash
composer test
```

Perintah ini setara dengan `php artisan config:clear` lalu `php artisan test`. Untuk menjalankan test tertentu saja:

```bash
php artisan test --filter=NamaTest
```

Ada 28 file test di `tests/Feature`, mencakup mulai dari role dan policy, alur donasi dan penyaluran dana, hash chain, deteksi anomali, sampai laporan berchecksum.

---

## 10. Ringkasan Peta Halaman

| Halaman | Alamat | Siapa yang bisa akses |
|---|---|---|
| Daftar program (publik) | `/program` | Semua orang |
| Detail program (publik) | `/program/{id}` | Semua orang |
| Cek status donasi | `/donasi/cek` | Semua orang |
| Login | `/login` | Tamu (belum login) |
| Daftar program (internal) | `/campaigns` | Bendahara, Admin, Auditor |
| Tambah/ubah program | `/campaigns/create`, `/campaigns/{id}/edit` | Bendahara, Admin |
| Daftar donasi | `/donasi` | Bendahara, Admin, Auditor |
| Ajukan penyaluran dana | `/campaigns/{id}/penyaluran/ajukan` | Bendahara |
| Daftar penyaluran dana | `/penyaluran` | Bendahara, Admin, Auditor |
| Verifikasi integritas | `/audit/integritas` | Auditor |
| Log aktivitas | `/audit/aktivitas` | Auditor |
| Log login | `/audit/login` | Auditor |
| Dashboard anomali | `/audit/anomali` | Auditor |
| Laporan donasi | `/laporan/donasi` | Auditor |
| Laporan penyaluran | `/laporan/penyaluran` | Auditor |
| Ringkasan saldo | `/laporan/saldo` | Auditor |
| Cek keaslian laporan | `/laporan/cek-keaslian` | Auditor |

---

## 11. Alur Demo Cepat (untuk presentasi ke dosen)

1. `php artisan migrate:fresh --seed`
2. Login sebagai `bendahara1@sidona.test`, tunjukkan alur memverifikasi donasi dan mengajukan penyaluran dana.
3. Login sebagai `admin@sidona.test`, tunjukkan bahwa penyaluran yang diajukan Bendahara di atas bisa disetujui, tapi coba ajukan penyaluran sendiri sebagai Admin lalu tunjukkan sistem menolak Admin menyetujui pengajuannya sendiri.
4. Login sebagai `auditor1@sidona.test`, buka Dashboard Anomali, tunjukkan tiga anomali yang sudah otomatis terdeteksi dari data uji.
5. Masih sebagai Auditor, buka Verifikasi Integritas, tunjukkan status log masih valid.
6. Logout, jalankan `php artisan demo:tamper-log` dari terminal.
7. Login lagi sebagai Auditor, buka Verifikasi Integritas, tunjukkan sistem sekarang mendeteksi baris log yang dirusak.
8. Buka salah satu laporan, export ke PDF, lalu buka halaman Cek Keaslian Laporan dan unggah ulang PDF tersebut untuk menunjukkan checksum-nya cocok.
