# Materi Presentasi SIDONA

Naskah untuk presentasi tugas kuliah. Baca dari atas ke bawah, urutannya dirancang supaya alurnya natural: pembukaan, konsep, bukti pemenuhan ketentuan, keunggulan, demo, lalu antisipasi pertanyaan.

---

## 0. Kalimat Pembuka

Gunakan salah satu, sesuaikan gaya bicara sendiri:

> "Sebelum masuk ke detail, saya mau luruskan satu hal dulu: sistem yang saya bangun ini bukan sistem audit. Fitur utamanya adalah galang dana untuk program sosial. Tapi yang membuatnya menarik untuk dibahas di mata kuliah ini adalah, sistem ini saya rancang supaya *bisa dipakai untuk* audit, lewat jejak transaksi yang lengkap, log yang tidak bisa dimanipulasi diam-diam, dan pemisahan tugas antar peran."

Ini penting disampaikan di awal karena langsung menjawab pertanyaan yang mungkin muncul: "kok judulnya donasi tapi ini tugas audit?"

---

## 1. Latar Belakang dan Alasan Pemilihan Topik

- Platform donasi secara alami membutuhkan kepercayaan publik: donatur perlu yakin uangnya tidak diselewengkan. Karena itu kebutuhan audit trail masuk akal secara bisnis, bukan tempelan fitur.
- Hampir semua platform donasi menghadapi isu kepercayaan soal transparansi dana. SIDONA mencoba menjawabnya dari sisi teknis: bagaimana sebuah sistem informasi bisa **membuktikan** datanya tidak diutak-atik.
- Fokus tugas ini bukan membangun fitur audit generik, tapi menunjukkan bagaimana prinsip audit sistem informasi (jejak transaksi, pemisahan tugas, deteksi anomali, integritas data) diterapkan langsung ke dalam desain aplikasi bisnis.

---

## 2. Gambaran Umum Sistem

- **Nama**: SIDONA, Sistem Informasi Donasi dan Audit.
- **Domain**: galang dana berbasis program. Donatur menyumbang ke program tertentu, dana disalurkan bertahap lewat persetujuan dua orang.
- **Pengunjung tanpa akun** bisa berdonasi, mengajukan program (dengan konfirmasi email), mengirim tiket bantuan, dan mengecek kuitansi atau status dengan kode.
- **Empat peran staf**:
  - **Bendahara**: membuat dan mengubah program, mengajukan penyaluran dana, membalas tiket.
  - **Admin**: menyetujui atau menolak pengajuan program dan penyaluran dana, kelola program, membalas tiket.
  - **Auditor**: akses baca ke seluruh sistem, plus halaman audit, anomali, dan laporan.
  - **Super Admin**: semua kewenangan staf, plus kelola pengguna (buat akun, ubah peran, nonaktifkan).
- **Tech stack**: Laravel 13, Livewire 3, Tailwind CSS 4, SQLite, Pest (36 file test).
- **Keterbukaan soal batas**: pembayaran saat ini **simulasi**. Tidak ada uang yang berpindah, dan halaman bayar menyatakannya terang-terangan. Alur setelah pembayaran terkonfirmasi sudah lengkap, dan titik penyambung ke gateway sungguhan sudah disiapkan.

---

## 3. Bukti Pemenuhan 10 Ketentuan Wajib

| No | Ketentuan | Cara dipenuhi |
|---|---|---|
| 1 | Login | Laravel session auth di `/login` (tidak ditautkan di situs publik) untuk semua peran staf |
| 2 | Minimal 2 role | Empat peran: Bendahara, Admin, Auditor, Super Admin, masing-masing kewenangan berbeda |
| 3 | Database | SQLite |
| 4 | Minimal 3 tabel utama | `campaigns`, `donations`, `disbursements`, ditambah `tickets` dan `ticket_messages` |
| 5 | Minimal 1 transaksi | Donasi masuk, dan pengajuan dan persetujuan penyaluran dana (maker-checker) |
| 6 | Validasi input | Nominal donasi, format nama dan kontak, rekening, saldo tersedia, masa program, alasan wajib saat menolak, batas laju dan kolom jebakan bot pada form publik |
| 7 | Laporan | Laporan donasi, penyaluran, ringkasan saldo, viewer log aktivitas dan login, semua bisa diunduh sebagai PDF |
| 8 | Login log | Tabel `login_logs` (waktu, IP, perangkat, hasil), dengan penanda pola gagal berturut-turut |
| 9 | Activity log | Tabel `activity_logs` dengan hash chain untuk mendeteksi manipulasi |
| 10 | Data uji | Seeder: 6 akun demo, 6 program berfoto asli, puluhan donasi dan penyaluran, riwayat login, pengajuan program |

Sampaikan singkat: "Semua 10 ketentuan wajib terpenuhi, dan saya akan tunjukkan beberapa di antaranya lewat demo."

---

## 4. Fitur Unggulan (Bagian Paling Penting)

Jelaskan satu per satu dan tekankan *mengapa* tiap fitur relevan dengan konsep audit.

### 4.1 Donasi tanpa verifikasi manual, kendali dipindah ke tempat yang tepat

- Donasi sah otomatis begitu pembayaran terkonfirmasi, tanpa antrean persetujuan staf. Itu sengaja: donatur tidak perlu menunggu manusia, dan staf tidak lagi menjadi titik rawan kesalahan atau kecurangan di sisi masuknya uang.
- Kendali tidak hilang, melainkan pindah ke sisi **keluarnya** uang (persetujuan dua orang), ke **log berantai**, dan ke **deteksi anomali**.
- Tiap pembayaran tercatat sebagai `donation.paid` oleh "Sistem (otomatis)".

### 4.2 Activity Log dengan Hash Chain

- Setiap aksi penting dicatat sebagai satu baris di `activity_logs`. Hash tiap baris dihitung dari datanya ditambah hash baris sebelumnya, prinsip yang sama dengan blockchain.
- Halaman khusus Auditor menghitung ulang seluruh rantai. Hasilnya menyebut jumlah catatan yang cocok, atau persis di catatan mana dan oleh siapa rantai pertama kali terputus.
- Di homepage publik ada "pita buku kas": catatan asli terbaru (jenis kejadian dan waktu, tanpa data pribadi). Itu bukti, bukan klaim.
- **Kenapa penting buat audit**: auditor selalu bertanya "bagaimana saya tahu log ini tidak dipalsukan setelah kejadian". Hash chain menjawabnya secara teknis.

### 4.3 Pemisahan Tugas pada Penyaluran Dana (Maker-Checker)

- Yang mengajukan penyaluran tidak boleh menyetujuinya, **termasuk Super Admin**. Ditegakkan di backend, bukan hanya disembunyikan di tampilan.
- Saat persetujuan, saldo dicek ulang dengan penguncian transaksi supaya dua pengajuan serentak tidak membuat saldo minus.
- Tabel penyaluran menunjukkan **dampak ke saldo** sebelum diputuskan: saldo sekarang dan sesudahnya.
- Prinsip ini adalah *segregation of duties*, materi inti pengendalian internal.

### 4.4 Dashboard Deteksi Anomali

- Tiga jenis temuan otomatis: donasi nominal jauh di atas rata-rata program, penyaluran disetujui kurang dari 60 detik setelah diajukan, dan tiga kali atau lebih login gagal dalam 15 menit.
- Auditor membuka Detail tiap temuan, lalu **Tandai diperiksa**. Penandaan tercatat di log dengan nama pemeriksa dan waktunya. Jadi pemeriksaan temuan pun bisa diaudit.

### 4.5 Laporan PDF Berchecksum

- Laporan donasi, penyaluran, dan saldo diunduh sebagai PDF dengan checksum SHA256 di footer. Halaman Cek Keaslian membuktikan PDF belum diedit dan menunjukkan siapa pembuat aslinya.

### 4.6 Pengajuan program oleh publik, dengan penjagaan

- Tamu bisa mengajukan program, tetapi tidak langsung tayang: email pengaju harus terkonfirmasi dulu, baru admin bisa menyetujui. Ada batas laju dan kolom jebakan bot.
- Pengaju mendapat kode pelacakan dan, setelah disetujui, tautan pantau pribadi yang menampilkan donasi dan penyaluran programnya (baca saja). Tanpa akun dan tanpa kata sandi baru.

### 4.7 Tiket bantuan dan kelola pengguna

- Tiket bantuan punya balasan otomatis yang membaca status kode donasi atau pengajuan, percakapan yang diperbarui otomatis, dan pelacakan lewat kode plus email.
- Super Admin mengelola akun staf: peran, kata sandi, dan penonaktifan (akun tidak dihapus, supaya jejak audit tetap utuh).

---

## 5. Skrip Demo Langsung

Siapkan: `php artisan migrate:fresh --seed`, lalu `php artisan serve`. Siapkan beberapa tab untuk peran berbeda (kata sandi semua akun `password`).

**Langkah 1, sebagai tamu**
> "Saya donatur tanpa akun."
- Buka `/program`, pilih program, pilih nominal, isi data, centang persetujuan, lanjut bayar, tekan simulasi.
> "Donasi langsung sah dan kuitansi terbit. Tidak ada yang perlu menyetujui. Pembayaran di sini simulasi, tapi alur setelahnya sama persis dengan gateway sungguhan."
- Kirim satu tiket di `/bantuan` dan tunjukkan balasan otomatisnya.

**Langkah 2, sebagai Bendahara** (`bendahara1@sidona.test`)
- Buka Penyaluran Dana, ajukan penyaluran dari satu program yang punya saldo.

**Langkah 3, sebagai Admin** (`admin@sidona.test`)
- Buka Penyaluran Dana, tunjukkan kolom "Dampak ke saldo", dan setujui pengajuan Bendahara.
> "Kalau saya ajukan sendiri lalu coba setujui sendiri, sistem menolak. Itu pemisahan tugas."
- Buka Program Donasi, setujui satu pengajuan program dari tamu, dan balas tiket di Tiket Bantuan.

**Langkah 4, sebagai Auditor** (`auditor1@sidona.test`)
- Buka Dashboard Anomali, tunjukkan tiga jenis temuan, buka Detail, dan tandai satu diperiksa.
- Buka Verifikasi Integritas, klik Verifikasi: rantai utuh.

**Langkah 5, demo manipulasi data (penutup yang dramatis)**
> "Sekarang saya simulasikan orang yang mengubah log langsung di database, melewati aplikasi."
- Terminal: `php artisan demo:tamper-log`.
- Kembali ke Verifikasi Integritas dan klik lagi.
> "Sistem langsung menyebut catatan nomor berapa yang tidak cocok, kejadiannya, waktunya, dan pelakunya. Inilah maksud saya sistem ini bisa dipakai untuk audit: bukan cuma mencatat, tapi bisa membuktikan keutuhan datanya."

**Langkah 6, laporan dan super admin**
- Unduh PDF dari Ringkasan Saldo, lalu unggah di Cek Keaslian Laporan.
- Login `superadmin@sidona.test`, buka Kelola Pengguna, nonaktifkan satu akun, dan coba masuk dengan akun itu.

---

## 6. Antisipasi Pertanyaan Dosen

**"Kenapa tidak ada verifikasi donasi oleh admin atau bendahara?"**
> "Itu keputusan desain. Konfirmasi pembayaran otomatis dari gateway lebih cepat bagi donatur dan menghilangkan satu titik di mana staf bisa salah atau curang. Kendali tidak hilang, tapi pindah ke tiga tempat: persetujuan dua orang untuk uang keluar, hash chain untuk membuktikan keutuhan catatan, dan deteksi anomali untuk nominal yang janggal."

**"Pembayarannya sungguhan?"**
> "Belum, dan itu dinyatakan jelas di halaman bayar. Pembayaran saat ini simulasi. Seluruh alur setelah konfirmasi sudah lengkap dan teruji, dan titik penyambung ke Midtrans atau Xendit sudah disiapkan, jadi memasang gateway tinggal memanggil fungsi konfirmasi yang sama."

**"Kenapa tidak memakai package activity log seperti Spatie?"**
> "Package itu bagus untuk mencatat, tapi tidak dirancang untuk membuktikan keutuhan data setelah dicatat. Saya membangun hash chain sendiri supaya bisa mendemonstrasikan konsep integritas data secara langsung."

**"Kalau penyerang punya akses penuh ke server dan tahu cara kerja hash chain, bukankah bisa dihitung ulang semuanya?"**
> "Betul, itu keterbatasan yang jujur saya akui. Hash chain melindungi dari manipulasi diam-diam yang parsial, bukan dari penyerang dengan akses penuh yang menghitung ulang seluruh rantai. Untuk skenario itu perlu penyimpanan log independen di luar sistem atau tanda tangan digital eksternal, yang di luar cakupan tugas ini tapi bisa menjadi pengembangan lanjutan."

**"Bagaimana keamanan data donatur?"**
> "Sistem tidak meminta dan tidak menyimpan nomor kartu atau data login bank. Yang disimpan hanya nama, kontak, nominal, metode, dan waktu, dan itu dijelaskan di Kebijakan Privasi. Donatur bisa menyamarkan nama di halaman publik, sementara nama asli hanya terlihat staf. Kata sandi disimpan ter-hash, dan akun staf bisa dinonaktifkan oleh Super Admin."

**"Super Admin punya semua hak, apakah tidak berbahaya?"**
> "Itu risiko yang saya batasi. Super Admin tetap tidak bisa menyetujui penyaluran yang ia ajukan sendiri dan tidak bisa menghapus program yang sudah punya riwayat uang. Semua tindakannya, termasuk kelola pengguna, tercatat di log berantai yang bisa diperiksa Auditor."

**"Kenapa pengaju program tidak perlu akun?"**
> "Supaya hambatan masuknya rendah, tetapi tetap ada penjagaan: email harus terkonfirmasi sebelum admin bisa menyetujui, ada batas laju dan jebakan bot, dan pemantauan lewat tautan pribadi yang hanya baca. Mengubah program tetap lewat admin."

**"Apakah bisa dipakai di dunia nyata?"**
> "Arsitekturnya bisa dikembangkan ke sana, tetapi ada hal yang jujur belum ada: gateway pembayaran sungguhan, pengiriman email sungguhan (saat ini ditulis ke log), dan koneksi real-time sungguhan. Percakapan tiket diperbarui lewat polling beberapa detik, bukan websocket."

**"Kenapa topik donasi?"**
> "Karena donasi punya kebutuhan transparansi yang nyata, jadi audit trail-nya masuk akal secara bisnis, tidak terasa dipaksakan."

---

## 7. Penutup

> "Jadi kesimpulannya, SIDONA adalah sistem donasi yang fitur utamanya galang dana dan transaksi, tetapi dirancang dengan prinsip audit sistem informasi sejak awal: jejak transaksi lengkap, pemisahan tugas, deteksi anomali otomatis, dan yang paling utama, mekanisme untuk membuktikan bahwa data historisnya belum pernah diutak-atik. Terima kasih."
