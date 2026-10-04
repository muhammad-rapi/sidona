# Panduan Penggunaan SIDONA

Sistem Informasi Donasi dan Audit. Fitur utamanya adalah galang dana untuk program sosial, dan yang membuat sistem ini "bisa dipakai untuk audit" adalah jejak transaksi lengkap, log berantai yang tidak bisa diubah diam-diam, dan pemisahan tugas antar peran. Dokumen ini menjelaskan cara memasang, menjalankan, dan mendemokan seluruh fiturnya.

Diagram alur kerja tiap fitur ada di [`ALUR_KERJA.md`](ALUR_KERJA.md). Ringkasan untuk presentasi ada di [`MATERI_PRESENTASI.md`](MATERI_PRESENTASI.md). Konteks produk ada di [`PRODUCT.md`](../PRODUCT.md) dan sistem desainnya di [`DESIGN.md`](../DESIGN.md).

> **Penting:** pembayaran donasi saat ini **simulasi**. Halaman bayar menampilkan QRIS, Virtual Account, atau e-wallet contoh, dan tombol "Simulasikan pembayaran berhasil" menggantikan notifikasi dari bank. Tidak ada uang yang berpindah. Titik penyambung ke gateway sungguhan ada di `app/Services/DonationPayment.php`.

---

## 1. Ringkasan Teknis

| Komponen | Pilihan |
|---|---|
| Framework | Laravel 13 |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS 4 |
| Database | SQLite (default, tanpa server database terpisah) |
| PDF laporan | barryvdh/laravel-dompdf |
| Testing | Pest 4 (36 file test di `tests/Feature`) |
| Bahasa aplikasi | Indonesia |
| PHP minimum | 8.3 (disarankan 8.4) |
| Huruf | Big Shoulders Display dan Hanken Grotesk, dimuat dari Google Fonts |

---

## 2. Instalasi

### 2.1 Persyaratan

- PHP 8.3 atau lebih baru beserta Composer
- Node.js beserta npm

### 2.2 Setup

```bash
composer install
composer run setup
php artisan storage:link
```

`composer run setup` menginstal dependency, menyalin `.env`, membuat `APP_KEY`, menjalankan migrasi, dan membangun asset. Perintah `storage:link` dijalankan terpisah dan **wajib**: tanpanya foto sampul dan galeri program tidak tampil di browser.

Ganti nama aplikasi di `.env` bila perlu (`APP_NAME=SIDONA`) dan pastikan `APP_URL` sesuai alamat yang dipakai, karena tautan di email dibuat dari nilai itu.

### 2.3 Email

`MAIL_MAILER` default-nya `log`: email tidak terkirim sungguhan, hanya tertulis di `storage/logs/laravel.log`. Itu cukup untuk demo. Email yang dikirim sistem:

- kuitansi setelah donasi berhasil (bila kontak donatur berupa email),
- tautan konfirmasi email pengaju program,
- keputusan atas pengajuan program (disetujui beserta tautan pantau, atau ditolak beserta alasan),
- konfirmasi tiket, balasan staf, dan tautan tiket yang dikirim ulang.

Untuk email sungguhan, atur konfigurasi mail di `.env`.

### 2.4 Menjalankan aplikasi

```bash
composer run dev
```

Atau terpisah: `php artisan serve` dan `npm run dev`.

---

## 3. Data Uji dan Akun Demo

```bash
php artisan migrate:fresh --seed
```

Semua akun memakai kata sandi `password`:

| Email | Nama | Peran |
|---|---|---|
| `superadmin@sidona.test` | Super Admin | Super Admin |
| `admin@sidona.test` | Admin SIDONA | Admin |
| `bendahara1@sidona.test` | Bendahara Satu | Bendahara |
| `bendahara2@sidona.test` | Bendahara Dua | Bendahara |
| `auditor1@sidona.test` | Auditor Satu | Auditor |
| `auditor2@sidona.test` | Auditor Dua | Auditor |

Seeder juga membuat:

- 6 program donasi (4 aktif, 2 selesai) lengkap dengan deskripsi berbahasa Indonesia, PIC, dan **foto asli berlisensi bebas** dari Wikimedia Commons (kredit pembuat tertera di keterangan foto; sumbernya di `database/seeders/sample-photos/credits.json`)
- 6 sampai 10 donasi per program, sekitar 90% berhasil dan sisanya menunggu pembayaran, termasuk beberapa nominal ekstrem yang memicu deteksi anomali
- 2 sampai 4 pengajuan penyaluran per program dengan berbagai status, termasuk satu yang disetujui sangat cepat (di bawah 60 detik)
- riwayat login semua akun, termasuk 3 percobaan gagal beruntun untuk `bendahara1@sidona.test` dalam 15 menit
- 3 pengajuan program dari tamu: dua menunggu tinjauan (email sudah terkonfirmasi) dan satu ditolak

---

## 4. Alur untuk Pengunjung (Tanpa Login)

Navbar publik berisi Program, Ajukan Program, FAQ, dan Cek Donasi. Tautan login staf sengaja tidak ditampilkan.

| Halaman | Alamat |
|---|---|
| Program aktif (dengan pencarian dan FAQ ringkas) | `/program` |
| Detail program, galeri, donatur terakhir, form donasi | `/program/{id}` |
| Halaman bayar | `/donasi/{kode}/bayar` |
| Kuitansi dan status donasi | `/donasi/{kode}` |
| Cek donasi, pengajuan, atau tiket dengan kode | `/donasi/cek` |
| Ajukan program | `/ajukan-program` |
| Status pengajuan (kode PRG) | `/ajukan-program/{kode}` |
| Pantau program (tautan pribadi) | `/pantau/{token}` |
| Hubungi kami (tiket) | `/bantuan` |
| Lacak tiket | `/bantuan/lacak` |
| Percakapan tiket (tautan pribadi) | `/bantuan/{token}` |
| FAQ, Syarat dan Ketentuan, Kebijakan Privasi | `/faq`, `/syarat-ketentuan`, `/kebijakan-privasi` |

### 4.1 Berdonasi

1. Buka program, pilih nominal (chip atau nominal lain, Rp 10.000 sampai Rp 1.000.000.000), isi nama dan email atau WhatsApp, pilih QRIS, Virtual Account, atau e-wallet, dan centang persetujuan syarat dan kebijakan privasi. Nama bisa disamarkan menjadi "Hamba Allah".
2. Halaman bayar menampilkan program, atas nama, dan kode donasi (`DON-xxxxxxxx`). Tekan "Simulasikan pembayaran berhasil". Halaman juga memeriksa status tiap 5 detik, jadi pembayaran yang terkonfirmasi dari luar langsung membawa donatur ke kuitansi.
3. Donasi langsung berstatus **Berhasil**, kuitansi terbit, dan email kuitansi dikirim bila kontak berupa email. **Tidak ada verifikasi manual dan tidak ada upload bukti transfer.**
4. Kuitansi bisa dibuka lagi kapan saja lewat Cek Donasi dengan kode DON.

### 4.2 Mengajukan program

1. Isi cerita program, target, lama penggalangan (14, 30, 60, atau 90 hari), rekening penerima (bank dipilih dari daftar), nama, **email**, dan WhatsApp (opsional). Dibatasi 3 pengajuan per jam per perangkat.
2. Konfirmasi email lewat tautan yang dikirim. Admin baru bisa menyetujui setelah email terkonfirmasi (admin tetap bisa menolak).
3. Kode pengajuan `PRG-xxxxxxxx` dipakai di Cek Donasi untuk melihat status.
4. Setelah disetujui, email keputusan memuat **tautan pantau pribadi** (`/pantau/{token}`): dana terkumpul, donatur, penyaluran yang disetujui, dan saldo, hanya baca. Mengubah isi program dilakukan lewat admin.

### 4.2 Tiket bantuan

Isi form di `/bantuan`. Tiket langsung mendapat balasan otomatis (konfirmasi, status terkini dari kode DON atau PRG yang disebut, dan petunjuk sesuai kategori), lalu staf membalas. Pengirim membuka percakapan lewat tautan pribadi di email, atau lewat **Lacak tiket** (kode + email, tautan dikirim ulang ke email itu). Percakapan diperbarui otomatis tiap beberapa detik.

---

## 5. Peran Staf

Staf masuk lewat alamat `/login` (tidak ditautkan di situs publik). Semua halaman staf memakai sidebar. Menu **Tiket Bantuan** menampilkan jumlah tiket terbuka.

### 5.1 Bendahara

- Membuat dan mengubah program (nama, deskripsi, foto sampul dan galeri, PIC, target, rekening, masa).
- Mengajukan penyaluran dana dari halaman Penyaluran Dana. Jumlah tidak boleh melebihi saldo program.
- Membalas tiket.
- Tidak bisa menyetujui penyaluran, menghapus program, atau membuka halaman audit dan laporan.

### 5.2 Admin

- Semua yang bisa dilakukan Bendahara untuk program, ditambah menghapus program (hanya bila belum punya donasi atau penyaluran).
- Menyetujui atau menolak **pengajuan program dari tamu** di Program Donasi (setujui hanya bila email pengaju terkonfirmasi, tolak wajib beserta alasan).
- Menyetujui atau menolak **penyaluran dana**. Admin tidak bisa memutuskan pengajuannya sendiri. Sistem menegakkan ini di backend dan mengecek ulang saldo saat persetujuan diproses.
- Membalas tiket.

### 5.3 Auditor

Hampir sepenuhnya baca saja, dengan akses eksklusif ke:

- **Verifikasi Integritas** (`/audit/integritas`): hitung ulang seluruh rantai hash. Hasilnya menyebut jumlah catatan yang cocok, atau di mana dan oleh siapa rantai pertama kali terputus.
- **Log Aktivitas** (`/audit/aktivitas`): nama kejadian dalam bahasa Indonesia, objek, pelaku, dan kolom rantai (hash sebelumnya ke hash catatan). Filter jenis aksi, pelaku, dan tanggal. Bisa diunduh sebagai PDF berchecksum.
- **Log Login** (`/audit/login`): ringkasan hari ini, tanda pola gagal berturut-turut, dan perangkat. Bisa diunduh sebagai PDF.
- **Dashboard Anomali** (`/audit/anomali`): donasi nominal ekstrem, penyaluran disetujui terlalu cepat, dan login gagal berturut-turut. Tiap temuan punya Detail, lalu tombol **Tandai diperiksa** (tercatat di log). Auditor adalah satu-satunya peran baca yang bisa menandai.
- **Laporan** (`/laporan/donasi`, `/laporan/penyaluran`, `/laporan/saldo`): dengan ringkasan hasil filter dan PDF berchecksum SHA256.
- **Cek Keaslian Laporan** (`/laporan/cek-keaslian`): unggah PDF, sistem menghitung ulang checksum dan menunjukkan siapa pembuat aslinya.
- Tiket bantuan hanya bisa dibaca, tidak dibalas.

### 5.4 Super Admin

Memiliki **semua kewenangan staf** ditambah halaman **Kelola Pengguna** (`/pengguna`): membuat akun, mengubah nama, email, dan peran, mengatur ulang kata sandi, dan menonaktifkan atau mengaktifkan akun. Akun tidak dihapus supaya jejak audit tetap utuh. Akun yang dinonaktifkan tidak bisa masuk, dan sesinya diakhiri pada permintaan berikutnya. Super Admin tidak bisa mengubah peran atau menonaktifkan akunnya sendiri.

Dua aturan integritas tetap berlaku untuk Super Admin: tidak bisa menyetujui penyaluran yang ia ajukan sendiri, dan tidak bisa menghapus program yang sudah punya riwayat uang.

### 5.5 Semua staf

- **Ringkasan** (`/dashboard`): saldo yang bisa disalurkan, antrean "Menunggu keputusan" (pengajuan program, penyaluran, tiket terbuka), program berjalan dengan bar progres, dan donasi terbaru.
- **Riwayat Donasi** (`/riwayat-donasi`): donasi dikelompokkan per hari dengan total harian, pencarian, dan Detail (kontak, metode bayar, jejak audit). Halaman ini hanya untuk memantau.
- **Profil** (`/profil`): ubah nama dan email, ganti kata sandi, lihat aktivitas login terakhir.

---

## 6. Cara Demo Fitur Pembeda

Menunjukkan bahwa rantai log terjaga:

```bash
php artisan demo:tamper-log
```

Perintah ini mengubah baris log terbaru langsung lewat database (melewati aplikasi). Setelah itu buka `/audit/integritas` sebagai Auditor dan klik Verifikasi: sistem menunjukkan rantai terputus pada baris tersebut.

---

## 7. Skema Database

**Data transaksi inti**
- `campaigns`: program (nama, deskripsi, sampul, target, rekening, masa, status Menunggu tinjauan, Aktif, Selesai, atau Ditolak, PIC, data pengaju dan verifikasi emailnya, token pantau)
- `campaign_photos`: galeri foto program
- `donations`: donasi (kode DON, donatur, anonim, nominal, metode, status, waktu bayar)
- `disbursements`: penyaluran dana (jumlah, keterangan, pengaju, penyetuju, status)

**Dukungan**
- `users` (peran dan status aktif), `login_logs`, `activity_logs` (hash chain), `report_exports`, `anomaly_reviews`
- `tickets` dan `ticket_messages` (termasuk pesan balasan otomatis)

---

## 8. Menjalankan Test

```bash
composer test
```

Atau langsung: `./vendor/bin/pest tests/Feature`. Jika output kosong atau terpotong di sebagian lingkungan, jalankan dengan `PAO_DISABLE=1 ./vendor/bin/pest tests/Feature`. Test mencakup peran dan kebijakan akses, alur donasi dan pembayaran, pengajuan program dan verifikasi emailnya, penyaluran, tiket, kelola pengguna, hash chain, anomali, laporan berchecksum, dan validasi form.

---

## 9. Ringkasan Peta Halaman Staf

| Halaman | Alamat | Siapa yang bisa akses |
|---|---|---|
| Ringkasan | `/dashboard` | Semua staf |
| Profil | `/profil` | Semua staf |
| Program Donasi | `/campaigns` | Semua staf |
| Tambah atau ubah program | `/campaigns/create`, `/campaigns/{id}/edit` | Bendahara, Admin, Super Admin |
| Riwayat Donasi | `/riwayat-donasi` | Semua staf |
| Penyaluran Dana | `/penyaluran` | Semua staf (ajukan: Bendahara dan Super Admin) |
| Ajukan penyaluran | `/campaigns/{id}/penyaluran/ajukan` | Bendahara, Super Admin |
| Tiket Bantuan | `/tiket`, `/tiket/{id}` | Semua staf (membalas: Bendahara, Admin, Super Admin) |
| Kelola Pengguna | `/pengguna` | Super Admin |
| Verifikasi Integritas, Log Aktivitas, Log Login, Anomali | `/audit/...` | Auditor, Super Admin |
| Laporan dan Cek Keaslian | `/laporan/...` | Auditor, Super Admin |

---

## 10. Alur Demo Cepat (untuk presentasi)

1. `php artisan migrate:fresh --seed`, lalu `php artisan serve`.
2. Sebagai tamu: buka `/program`, donasi ke satu program, tekan simulasi pembayaran, tunjukkan kuitansi langsung terbit **tanpa menunggu persetujuan siapa pun**.
3. Sebagai tamu: ajukan program lewat `/ajukan-program`, tunjukkan kode PRG, lalu buka `/bantuan` dan kirim tiket (perhatikan balasan otomatisnya).
4. Login `bendahara1@sidona.test`: ajukan penyaluran dana dari halaman Penyaluran Dana.
5. Login `admin@sidona.test`: tunjukkan kolom "Dampak ke saldo", setujui penyaluran dari bendahara, lalu tunjukkan admin tidak bisa memutuskan pengajuannya sendiri. Setujui satu pengajuan program, dan balas tiket.
6. Login `auditor1@sidona.test`: buka Dashboard Anomali (tiga jenis temuan dari data uji), tandai satu diperiksa, lalu Verifikasi Integritas (rantai utuh).
7. Jalankan `php artisan demo:tamper-log`, ulangi Verifikasi Integritas: rantai terputus dan baris pertama yang bermasalah ditunjukkan.
8. Buka Laporan Donasi, unduh PDF, lalu unggah ke Cek Keaslian Laporan.
9. Login `superadmin@sidona.test`: tunjukkan Kelola Pengguna, nonaktifkan satu akun, dan coba masuk dengan akun itu.
