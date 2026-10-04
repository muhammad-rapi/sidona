# Materi Presentasi SIDONA

Naskah lengkap untuk presentasi tugas kuliah. Baca dari atas ke bawah, urutannya sudah dirancang supaya alurnya natural: pembukaan, konsep, bukti pemenuhan ketentuan, keunggulan, demo, lalu antisipasi pertanyaan.

---

## 0. Kalimat Pembuka

Gunakan salah satu, sesuaikan gaya bicara sendiri:

> "Sebelum masuk ke detail, saya mau luruskan satu hal dulu: sistem yang saya bangun ini bukan sistem audit. Fitur utamanya adalah pengelolaan donasi. Tapi yang membuatnya menarik untuk dibahas di mata kuliah ini adalah, sistem ini saya rancang supaya *bisa dipakai untuk* audit, lewat jejak transaksi yang lengkap, log yang tidak bisa dimanipulasi diam diam, dan pemisahan tugas antar peran."

Ini penting disampaikan di awal karena langsung menjawab pertanyaan yang mungkin muncul di kepala dosen: "kok judulnya donasi tapi ini tugas audit?"

---

## 1. Latar Belakang dan Alasan Pemilihan Topik

Sampaikan poin poin ini (boleh dengan kalimat sendiri):

- Sistem donasi dipilih karena secara alami membutuhkan kepercayaan publik: donatur perlu yakin uangnya tidak diselewengkan. Ini membuat kebutuhan audit trail jadi masuk akal secara bisnis, bukan sekadar tempelan fitur.
- Kasus nyata: hampir semua platform donasi (Kitabisa, BAZNAS, dsb) menghadapi isu kepercayaan publik soal transparansi dana. SIDONA mencoba menjawab itu dari sisi teknis: bagaimana caranya sebuah sistem informasi bisa membuktikan datanya tidak diutak atik.
- Fokus tugas ini bukan membangun fitur audit generik, tapi mendemonstrasikan bagaimana prinsip prinsip audit sistem informasi (jejak transaksi, pemisahan tugas, deteksi anomali, integritas data) diterapkan langsung ke dalam desain sebuah aplikasi bisnis.

---

## 2. Gambaran Umum Sistem

- **Nama**: SIDONA, Sistem Informasi Donasi dan Audit.
- **Domain**: platform donasi berbasis program/campaign (donatur menyumbang ke program tertentu, dana disalurkan bertahap sesuai kebutuhan program).
- **Tiga peran pengguna internal**:
  - **Bendahara**: mengelola program, memverifikasi donasi masuk, mengajukan penyaluran dana.
  - **Admin**: mengelola program, menyetujui atau menolak pengajuan penyaluran dana.
  - **Auditor**: akses baca saja ke seluruh sistem, plus akses eksklusif ke halaman audit dan laporan.
- **Donatur** tidak perlu akun, bisa langsung menyumbang lewat halaman publik dan mengecek status donasinya lewat kode referensi.
- **Tech stack**: Laravel 13, Livewire 3, Tailwind CSS 4, database SQLite, testing pakai Pest (28 file test).

---

## 3. Bukti Pemenuhan 10 Ketentuan Wajib

Sampaikan ini sebagai checklist tegas, satu per satu, biar dosen langsung bisa mencoret di lembar penilaiannya:

| No | Ketentuan | Cara dipenuhi |
|---|---|---|
| 1 | Login | Laravel session auth, halaman `/login` untuk Bendahara, Admin, Auditor |
| 2 | Minimal 2 role | Tiga role: Bendahara, Admin, Auditor, masing masing kewenangan berbeda |
| 3 | Database | SQLite |
| 4 | Minimal 3 tabel utama | `campaigns`, `donations`, `disbursements` |
| 5 | Minimal 1 transaksi | Dua transaksi: donasi masuk, dan pengajuan/persetujuan penyaluran dana |
| 6 | Validasi input | Validasi nominal, format bukti transfer, saldo tersedia, tanggal program, alasan wajib saat menolak |
| 7 | Laporan | Laporan donasi, laporan penyaluran, ringkasan saldo, viewer log aktivitas dan login, semua bisa diexport PDF |
| 8 | Login log | Tabel `login_logs`, mencatat setiap percobaan login berhasil maupun gagal |
| 9 | Activity log | Tabel `activity_logs`, dengan tambahan hash chain untuk deteksi manipulasi |
| 10 | Data uji | Seeder otomatis: 5 akun demo, 6 program, puluhan donasi dan penyaluran dana, riwayat login |

Sampaikan singkat: "Semua 10 ketentuan wajib terpenuhi, dan saya akan tunjukkan beberapa di antaranya langsung lewat demo."

---

## 4. Fitur Unggulan (Bagian Paling Penting)

Ini bagian yang membedakan SIDONA dari tugas kuliah kebanyakan. Jelaskan satu per satu, dan tekankan *mengapa* tiap fitur relevan dengan konsep audit, bukan cuma "keren keren an teknis".

### 4.1 Activity Log dengan Hash Chain

- Setiap aksi penting (buat program, verifikasi donasi, setujui penyaluran) dicatat sebagai satu baris di `activity_logs`.
- Setiap baris menyimpan hash dari dirinya sendiri, yang dihitung dari data baris itu ditambah hash baris sebelumnya. Ini prinsip yang sama dipakai blockchain untuk membuat rantai data yang tidak bisa diubah tanpa ketahuan.
- Ada halaman khusus Auditor untuk menghitung ulang seluruh chain dan membuktikan apakah datanya masih utuh atau pernah diutak atik.
- **Kenapa ini penting buat audit**: auditor sungguhan selalu mempertanyakan "bagaimana saya tahu log ini tidak dipalsukan setelah kejadian". Hash chain menjawab pertanyaan itu secara teknis, bukan cuma janji.

### 4.2 Pemisahan Tugas pada Persetujuan Dana (Maker Checker)

- Bendahara yang mengajukan penyaluran dana tidak bisa menjadi orang yang menyetujuinya sendiri, walaupun dia login sebagai Admin di akun lain.
- Ini prinsip **segregation of duties**, salah satu materi inti pengendalian internal dalam audit sistem informasi, dan sistem menegakkannya di level backend (bukan cuma disembunyikan di tampilan).

### 4.3 Dashboard Deteksi Anomali

- Sistem otomatis menandai tiga jenis kejanggalan: donasi dengan nominal jauh di atas rata rata program, penyaluran dana yang disetujui kurang dari 60 detik setelah diajukan (indikasi tidak ditinjau sungguh sungguh), dan tiga kali atau lebih percobaan login gagal dalam 15 menit.
- Ini menunjukkan sistem tidak cuma pasif mencatat, tapi aktif membantu auditor menemukan hal yang perlu diperiksa lebih lanjut.

### 4.4 Laporan PDF Berchecksum

- Laporan keuangan (donasi, penyaluran, saldo) bisa diexport ke PDF yang menyertakan checksum SHA256 di footernya.
- Ada halaman khusus untuk mengunggah ulang PDF tersebut dan memverifikasi apakah checksum-nya masih cocok, membuktikan file itu belum diedit sejak pertama kali diunduh.

---

## 5. Skrip Demo Langsung

Siapkan dulu: jalankan `php artisan migrate:fresh --seed` sebelum presentasi supaya data bersih. Buka browser dengan beberapa tab siap login sebagai role berbeda.

Ikuti urutan ini sambil bicara, jangan cuma klik diam:

**Langkah 1, sebagai Bendahara** (`bendahara1@sidona.test` / `password`)
> "Saya login sebagai Bendahara. Di sini saya bisa melihat donasi yang masuk dan menunggu verifikasi."
- Buka `/donasi`, tunjukkan satu donasi berstatus menunggu, klik verifikasi.
> "Begitu saya verifikasi, sistem otomatis mencatat aksi ini ke activity log, dan kalau kontak donatur berupa email, sistem kirim notifikasi otomatis."
- Buka `/campaigns/{id}/penyaluran/ajukan`, ajukan satu penyaluran dana.

**Langkah 2, sebagai Admin** (`admin@sidona.test` / `password`)
> "Sekarang saya login sebagai Admin untuk menyetujui pengajuan penyaluran dana tadi."
- Buka `/penyaluran`, setujui pengajuan dari Bendahara.
> "Yang menarik, kalau saya coba ajukan penyaluran dana sendiri sebagai Admin, lalu coba setujui sendiri..."
- Tunjukkan tombol setuju tidak muncul atau ditolak sistem untuk pengajuan milik sendiri.
> "...sistem menolak. Ini penerapan pemisahan tugas yang saya jelaskan tadi."

**Langkah 3, sebagai Auditor** (`auditor1@sidona.test` / `password`)
> "Sekarang saya login sebagai Auditor, yang aksesnya read only, tapi punya halaman khusus untuk audit."
- Buka `/audit/anomali`, tunjukkan tiga anomali yang sudah otomatis terdeteksi dari data uji.
- Buka `/audit/integritas`, klik Verifikasi, tunjukkan status chain valid.

**Langkah 4, demo manipulasi data (bagian paling dramatis, simpan untuk penutup demo)**
> "Sekarang saya akan simulasikan kalau ada orang yang mengubah data log langsung lewat database, melewati aplikasi."
- Buka terminal, jalankan `php artisan demo:tamper-log`.
> "Perintah ini sengaja mengubah satu baris log paling baru langsung di database."
- Kembali ke browser, buka lagi `/audit/integritas`, klik Verifikasi.
> "Dan sistem langsung mendeteksi ada baris yang datanya sudah tidak cocok dengan hash-nya. Inilah yang saya maksud sistem ini bisa dipakai untuk audit: bukan cuma mencatat, tapi bisa membuktikan keutuhan datanya sendiri."

**Langkah 5, laporan (penutup demo)**
- Buka salah satu laporan, misalnya `/laporan/saldo`, export ke PDF.
- Buka `/laporan/cek-keaslian`, unggah PDF yang baru diunduh, tunjukkan hasilnya cocok/asli.

---

## 6. Antisipasi Pertanyaan Dosen

**"Kenapa bukan pakai package activity log yang sudah ada, seperti Spatie Activitylog?"**
> "Package seperti itu memang bagus untuk mencatat, tapi tidak dirancang untuk membuktikan keutuhan data setelah dicatat. Saya sengaja membangun mekanisme hash chain sendiri supaya bisa mendemonstrasikan konsep integritas data secara langsung, bukan hanya memakai fitur jadi."

**"Kalau orang yang menyerang punya akses penuh ke server dan tahu cara kerja hash chain-nya, bukannya bisa dihitung ulang semua dan disesuaikan?"**
> "Betul, itu keterbatasan yang jujur saya akui. Hash chain melindungi dari manipulasi diam diam yang parsial atau tidak disadari, bukan dari penyerang yang punya akses penuh ke server dan waktu untuk menghitung ulang seluruh chain. Solusi untuk skenario itu butuh penyimpanan log di luar sistem yang independen, atau tanda tangan digital eksternal, yang di luar cakupan tugas ini tapi bisa jadi pengembangan lanjutan."

**"Kenapa pilih topik donasi, bukan yang lain?"**
> "Karena topik donasi punya kebutuhan transparansi yang nyata di dunia nyata, jadi kebutuhan audit trail-nya tidak terasa dipaksakan, melainkan memang masuk akal secara bisnis."

**"Bagaimana keamanan data donatur, misalnya bukti transfer yang diupload?"**
> "File disimpan di disk penyimpanan Laravel dengan validasi tipe dan ukuran file saat upload. Untuk cakupan tugas ini saya fokus ke integritas data transaksi dan log, sementara enkripsi penyimpanan file bisa jadi pengembangan lanjutan."

**"Apakah sistem ini bisa dipakai di dunia nyata / production?"**
> "Secara arsitektur bisa dikembangkan ke sana, tapi untuk tugas ini saya fokus mendemonstrasikan konsep dan penerapannya, bukan kesiapan production seperti skalabilitas, gateway pembayaran otomatis, atau notifikasi real time."

---

## 7. Penutup

> "Jadi kesimpulannya, SIDONA adalah sistem donasi yang fitur utamanya pengelolaan program dan transaksi donasi, tapi dirancang dengan prinsip prinsip audit sistem informasi sejak awal: jejak transaksi yang lengkap, pemisahan tugas, deteksi anomali otomatis, dan yang paling utama, mekanisme untuk membuktikan bahwa data historisnya belum pernah diutak atik. Terima kasih."
