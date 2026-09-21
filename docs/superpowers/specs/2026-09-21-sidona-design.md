# SIDONA — Sistem Informasi Donasi dan Audit

**Tanggal**: 2026-09-21
**Status**: Disetujui, siap masuk tahap perencanaan implementasi
**Konteks**: Tugas kuliah. Fitur utama sistem adalah pengelolaan donasi (bukan audit itu sendiri), tapi sistem dirancang agar bisa dipakai sebagai alat bantu audit lewat jejak transaksi yang lengkap, log yang tidak bisa dimanipulasi diam diam, dan pemisahan tugas antar role.

## 1. Ringkasan

SIDONA adalah platform donasi untuk program/campaign (misal donasi bencana, donasi pendidikan) di mana donatur bisa menyumbang tanpa akun (guest). Yang membuat sistem ini "bisa dipakai untuk audit" adalah:

1. Pemisahan tugas (segregation of duties) antar tiga role internal: Bendahara mengajukan, Admin menyetujui, Auditor mengawasi tanpa bisa mengubah apa pun.
2. Activity log berbentuk hash chain yang bisa diverifikasi keutuhannya kapan saja.
3. Laporan yang bisa diexport dengan checksum untuk membuktikan dokumen tidak diubah setelah dicetak.
4. Dashboard anomali yang menandai transaksi/aktivitas mencurigakan secara otomatis.

## 2. Arsitektur & Tech Stack

- **Framework**: Laravel 11
- **Frontend**: Livewire 3 + Alpine.js, styling Tailwind CSS custom (tanpa starter kit Jetstream/Breeze mentah)
- **Database**: MySQL/PostgreSQL untuk production, SQLite untuk development lokal
- **Auth**: Laravel built in session auth. Role disimpan sebagai kolom enum `role` di tabel `users` (`bendahara`, `admin`, `auditor`) — tidak pakai package RBAC pihak ketiga karena role tetap dan jumlahnya cuma tiga, sistem permission dinamis akan berlebihan untuk scope ini.
- **Otorisasi**: Laravel Policy + middleware per role. Semua route yang diakses Auditor hanya `GET`; policy menolak semua aksi tulis dari role Auditor di level backend, bukan cuma disembunyikan di UI.
- **PDF**: `barryvdh/laravel-dompdf`
- **Icon**: Blade Icons + set Lucide (SVG), konsisten di semua browser/OS, tanpa emoji sebagai ikon fungsional.

## 3. Role & Alur Pengguna

| Role | Login? | Kewenangan |
|---|---|---|
| Donatur (guest) | Tidak | Lihat program aktif, isi form donasi, cek status donasi lewat kode referensi |
| Bendahara | Ya | Verifikasi/tolak donasi masuk, ajukan penyaluran dana (maker) |
| Admin | Ya | Setujui/tolak pengajuan penyaluran dana (checker), kelola program, kelola akun user internal |
| Auditor | Ya | Read only penuh: laporan, activity log, login log, dashboard anomali, verifikasi integritas hash chain |

Aturan pemisahan tugas: pengaju penyaluran dana (Bendahara) dan penyetuju (Admin) tidak boleh orang yang sama secara sistemik — ini bukan cuma label role, tapi ditegakkan di level policy approve.

## 4. Model Data

### Tabel utama (data transaksi inti)

1. **campaigns** — program donasi: nama, deskripsi, target dana, tanggal mulai/selesai, status (aktif/selesai)
2. **donations** — donasi masuk: campaign_id, nama donatur, kontak, nominal, bukti transfer (file), status (pending/terverifikasi/ditolak), kode referensi, diverifikasi oleh siapa & kapan, alasan penolakan
3. **disbursements** — penyaluran dana: campaign_id, jumlah, keterangan penggunaan, diajukan oleh (Bendahara) & kapan, status (diajukan/disetujui/ditolak), disetujui oleh (Admin) & kapan, alasan penolakan

Saldo program dihitung on the fly (donasi terverifikasi dikurangi penyaluran disetujui), bukan kolom tersimpan terpisah, untuk menghindari data yang bisa tidak konsisten.

### Tabel pendukung (memenuhi ketentuan wajib #3, #8, #9)

4. **users** — akun internal, kolom `role` enum
5. **login_logs** — setiap percobaan login (sukses/gagal): user, IP, user agent, waktu
6. **activity_logs** — setiap aksi penting: user, jenis aksi, tabel & record terkait, nilai lama/baru (JSON), `prev_hash`, `hash`
7. **report_exports** — riwayat export laporan PDF: siapa, kapan, checksum SHA256 file

## 5. Transaksi Utama

### Transaksi 1: Donasi masuk

1. Donatur isi form di halaman program → status `pending`
2. Validasi: nominal harus angka lebih dari 0 (minimal Rp 10.000), nama & kontak wajib diisi, bukti transfer wajib diunggah (format jpg/png/pdf, maksimal 2MB), program harus berstatus aktif dan belum melewati tanggal selesai
3. Bendahara membuka daftar donasi pending, memeriksa bukti transfer, memilih Terverifikasi atau Ditolak (wajib mengisi alasan jika ditolak)
4. Setiap perubahan status tercatat otomatis ke `activity_logs`

### Transaksi 2: Penyaluran dana

1. Bendahara mengajukan penyaluran dari program tertentu, mengisi jumlah dan keterangan penggunaan
2. Validasi: jumlah tidak boleh melebihi saldo program yang tersedia (dihitung real time menggunakan DB transaction dengan lock untuk mencegah race condition), program harus memiliki saldo terverifikasi lebih dari 0
3. Status `diajukan` → Admin meninjau → Disetujui atau Ditolak (wajib alasan jika ditolak)
4. Tercatat ke `activity_logs`, termasuk identitas pengaju dan penyetuju secara terpisah

## 6. Fitur Audit

### 6.1 Hash chain integrity log

Setiap baris di `activity_logs` menyimpan:
- `payload` (JSON berisi siapa, aksi apa, data lama/baru)
- `prev_hash` (hash baris sebelumnya di tabel yang sama)
- `hash` = SHA256(`prev_hash` + `payload` + timestamp)

Halaman **Verifikasi Integritas** (khusus Auditor) memiliki tombol yang menghitung ulang seluruh chain dari baris pertama sampai terakhir dan membandingkannya dengan hash tersimpan. Jika ada satu baris yang datanya diubah langsung lewat database (di luar aplikasi), chain akan terputus mulai dari baris tersebut, dan sistem menunjukkan baris mana yang mencurigakan.

**Skenario demo**: artisan command `php artisan demo:tamper-log` yang sengaja mengubah satu baris log langsung di database (melewati aplikasi), khusus untuk didemonstrasikan — jalankan command, lalu tunjukkan halaman Verifikasi Integritas langsung mendeteksi ketidaksesuaian.

### 6.2 Export laporan PDF dengan checksum

Laporan (rekap donasi, rekap penyaluran) diexport ke PDF. Sistem menghitung SHA256 dari isi PDF, checksum ditampilkan di footer PDF dan disimpan di tabel `report_exports`. Auditor dapat mengunggah ulang PDF ke halaman "Cek Keaslian Laporan" untuk memverifikasi checksum masih cocok.

### 6.3 Dashboard anomali

Auditor dashboard menampilkan flag otomatis untuk:
- Donasi dengan nominal jauh di atas rata rata (lebih dari rata rata ditambah dua kali standar deviasi dari donasi di program yang sama)
- Percobaan login gagal berturut turut (tiga kali atau lebih dalam 15 menit) dari `login_logs`
- Penyaluran dana yang disetujui dalam waktu sangat cepat dari waktu pengajuan (kurang dari 1 menit — indikasi peninjauan terburu buru)

### 6.4 Audit trail viewer

Tabel activity log dan login log yang bisa difilter (per user, per rentang tanggal, per jenis aksi, per tabel terkait). Klik satu baris membuka detail nilai sebelum/sesudah dalam format yang mudah dibaca, bukan JSON mentah.

## 7. Laporan

- Rekap donasi per program (grafik batang dan tabel, filter tanggal dan status)
- Rekap penyaluran dana per program
- Ringkasan saldo tiap program
- Laporan aktivitas dan login (dari audit trail viewer)
- Semua laporan bisa diexport PDF berchecksum

## 8. Bahasa Desain

Ciri khas desain buatan AI yang generik biasanya berupa gradient ungu/biru neon, glassmorphism, kartu melayang dengan shadow berlebihan, dan font sans generik. SIDONA memakai arah sebaliknya yang justru selaras dengan tema audit: **gaya dokumen/ledger resmi**.

- Palet warna terbatas dan tenang: navy tua, hijau tinta (status positif), krem/off white sebagai warna latar (bukan putih polos), merah bata (status ditolak/anomali)
- Heading memakai font serif, angka dan kode referensi/hash memakai font monospace
- Tabel dengan garis tegas (bordered), bukan kartu melayang dengan shadow blur
- Status donasi/penyaluran ditampilkan seperti stempel (badge kotak dengan border, bukan pill gradient)
- Dashboard Auditor dirancang terasa seperti membaca laporan audit sungguhan, bukan dashboard SaaS kekinian
- Seluruh teks UI (judul, label, kalimat) dihindarkan dari tanda hubung (-). Aturan ini hanya berlaku pada teks/copy; URL, route, dan nama file tetap boleh memakai dash sesuai konvensi teknis (kebab case).
- Ikon fungsional memakai set SVG (Lucide), tanpa emoji.

## 9. Data Uji (Seeder)

- 1 admin, 2 bendahara, 2 auditor (password sama untuk keperluan demo)
- 6 sampai 8 program donasi (campuran status aktif dan selesai)
- 40 sampai 60 donasi tersebar di berbagai status (pending/terverifikasi/ditolak), termasuk beberapa dengan nominal ekstrem untuk memicu flag anomali
- 10 sampai 15 pengajuan penyaluran dana di berbagai status
- Beberapa entri login gagal berturut turut (memicu flag anomali login) dan riwayat login sukses
- Activity log terbentuk otomatis dari proses seeding di atas, sehingga hash chain sudah memiliki isi sejak awal

## 10. Pemetaan ke Ketentuan Wajib

| No | Komponen | Pemenuhan |
|---|---|---|
| 1 | Login | Laravel session auth untuk Bendahara/Admin/Auditor |
| 2 | Pengguna (min 2 role) | 3 role: Bendahara, Admin, Auditor |
| 3 | Database | MySQL/PostgreSQL |
| 4 | Data utama (min 3 tabel) | campaigns, donations, disbursements |
| 5 | Transaksi | Donasi masuk, penyaluran dana |
| 6 | Validasi | Validasi nominal, format file, saldo, tanggal program, dsb (lihat bagian 5) |
| 7 | Laporan | Bagian 7 |
| 8 | Login log | Tabel login_logs |
| 9 | Activity log | Tabel activity_logs dengan hash chain |
| 10 | Data uji | Seeder, bagian 9 |

## 11. Di Luar Cakupan (Out of Scope)

- Payment gateway otomatis (verifikasi transfer tetap manual oleh Bendahara)
- Notifikasi email/SMS ke donatur
- Multi organisasi/tenant (SIDONA melayani satu organisasi)
- Sistem permission dinamis/role kustom di luar tiga role tetap
