# Alur Kerja SIDONA

Dokumen ini memetakan alur kerja tiap fitur SIDONA dalam bentuk diagram. Untuk cara instalasi, akun demo, dan peta halaman, lihat [`PANDUAN_PENGGUNAAN.md`](PANDUAN_PENGGUNAAN.md).

Diagram memakai format Mermaid, otomatis tampil sebagai gambar di GitHub dan kebanyakan pratinjau markdown.

Satu catatan penting sebelum membaca: **pembayaran donasi saat ini masih simulasi** (tidak ada uang yang berpindah). Titik penyambung ke gateway sungguhan (Midtrans atau Xendit) sudah disiapkan di `DonationPayment::confirm()`, jadi alurnya sama persis ketika gateway dipasang nanti.

---

## 1. Peta Alur Keseluruhan Sistem

```mermaid
flowchart TD
    Donatur([Donatur / Tamu]) -->|pilih nominal, bayar| Donasi[(Donasi)]
    Donatur -->|cek kuitansi dengan kode DON| Donasi
    Donatur -->|tulis pesan| Tiket[(Tiket bantuan)]

    Pengaju([Pengaju program / Tamu]) -->|isi form, konfirmasi email| Pengajuan[(Pengajuan program)]
    Pengaju -->|pantau lewat tautan pribadi| Pengajuan

    Bendahara([Bendahara]) -->|kelola| Program[(Program Donasi)]
    Bendahara -->|ajukan| Penyaluran[(Penyaluran Dana)]
    Bendahara -->|balas| Tiket

    Admin([Admin]) -->|setujui / tolak| Pengajuan
    Admin -->|setujui / tolak| Penyaluran
    Admin -->|kelola, hapus bila belum ada uang| Program
    Admin -->|balas| Tiket

    SuperAdmin([Super Admin]) -->|semua kewenangan staf + kelola pengguna| Program

    Pengajuan -->|disetujui menjadi| Program
    Donasi -.tercatat.-> Log[(Log Aktivitas + Hash Chain)]
    Penyaluran -.tercatat.-> Log
    Program -.tercatat.-> Log
    Tiket -.tercatat.-> Log

    Auditor([Auditor]) -->|verifikasi keutuhan, baca log| Log
    Auditor -->|periksa temuan| Anomali[Dashboard Anomali]
    Auditor -->|unduh & cek keaslian| Laporan[Laporan PDF Berchecksum]
    Login[(Log Login)] -.dibaca.-> Anomali
    Log -.dibaca.-> Anomali
```

---

## 2. Alur Donasi (tanpa verifikasi manual)

Donasi sah **otomatis** begitu pembayaran terkonfirmasi. Tidak ada upload bukti transfer dan tidak ada antrean verifikasi oleh staf.

```mermaid
flowchart TD
    A([Donatur buka /program/id]) --> B[Pilih nominal, isi nama dan\nemail atau WhatsApp, pilih cara bayar,\ncentang syarat dan privasi]
    B --> C{Validasi}
    C -->|tidak valid| B
    C -->|valid| D[Donasi dibuat, status: Menunggu pembayaran\nkode DON-xxxxxxxx\nlog donation.created]
    D --> E[Halaman bayar:\nQRIS, Virtual Account, atau e-wallet]
    E --> F{Pembayaran terkonfirmasi?}
    F -->|ya: tombol simulasi sekarang,\nnotifikasi gateway nanti| G[DonationPayment::confirm\ndikunci transaksi DB, hanya sekali]
    F -->|belum| E
    E -.halaman memeriksa tiap 5 detik.-> F
    G --> H[Status: Berhasil, paid_at terisi\nlog donation.paid oleh Sistem]
    H --> I[Kuitansi terbit di /donasi/kode]
    H --> J{Kontak berupa email?}
    J -->|ya| K[Kirim email kuitansi]
    J -->|tidak| L[Lewati email]
    H --> M[Saldo program bertambah\ndan masuk laporan]
```

Catatan: donatur yang memilih anonim tampil sebagai "Hamba Allah" di halaman publik. Nama aslinya tetap tersimpan dan hanya terlihat staf.

---

## 3. Alur Pengajuan Program oleh Tamu

Tanpa akun dan tanpa kata sandi. Identitas pengaju dibuktikan lewat konfirmasi email, dan pemantauan lewat tautan pribadi.

```mermaid
flowchart TD
    A([Tamu buka /ajukan-program]) --> B[Isi cerita, target, lama penggalangan,\nrekening penerima, nama, email,\nWhatsApp opsional]
    B --> C{Validasi, batas 3 pengajuan per jam,\nkolom jebakan bot}
    C -->|tidak lolos| B
    C -->|lolos| D[Program dibuat status: Menunggu tinjauan\nkode PRG-xxxxxxxx\nlog campaign.proposed]
    D --> E[Email konfirmasi dikirim\ntautan bertanda tangan, berlaku 2 hari]
    E --> F{Pengaju menekan tautan?}
    F -->|ya| G[Email terkonfirmasi\nlog campaign.proposer_verified]
    F -->|belum| H[Admin belum bisa menyetujui,\nmasih bisa menolak]

    G --> I{Admin meninjau di Program Donasi}
    H --> I
    I -->|Setujui\nhanya jika email terkonfirmasi| J[Status: Aktif\nmasa dihitung dari hari persetujuan\nlog campaign.approved]
    I -->|Tolak + wajib alasan| K[Status: Ditolak\nlog campaign.rejected]

    J --> L[Email keputusan + tautan pantau pribadi /pantau/token]
    K --> M[Email keputusan berisi alasan]
    D -.kapan saja.-> N([Cek status di /donasi/cek dengan kode PRG])
```

Halaman pantau (`/pantau/token`) bersifat baca saja: dana terkumpul, jumlah donatur, donasi terbaru, penyaluran yang disetujui, dan saldo. Mengubah teks, foto, atau masa program dilakukan lewat admin.

---

## 4. Alur Pengajuan dan Persetujuan Penyaluran Dana

Prinsip pemisahan tugas (maker-checker): yang mengajukan tidak boleh menyetujui.

```mermaid
flowchart TD
    A([Bendahara atau Super Admin\nklik Ajukan penyaluran]) --> B{Program aktif dan\nsaldo lebih dari 0?}
    B -->|tidak| C[Program tidak muncul di pilihan]
    B -->|ya| D[Isi jumlah dan keterangan\nmin. 10 karakter]
    D --> E{Jumlah melebihi saldo?}
    E -->|ya| D
    E -->|tidak| F[Pengajuan status: Diajukan\nlog disbursement.submitted]

    F --> G[Admin buka Penyaluran Dana:\nkolom Dampak ke saldo menunjukkan\nsaldo sebelum dan sesudah]
    G --> H{Pengaju = yang login?}
    H -->|ya| I[Setujui / Tolak tidak tersedia,\nperlu admin lain]
    H -->|tidak| J{Keputusan}
    J -->|Setujui| K{Cek ulang saldo\nterkunci transaksi DB}
    K -->|cukup| L[Status: Disetujui\nlog disbursement.approved]
    K -->|tidak cukup| M[Ditolak sistem]
    J -->|Tolak + alasan| N[Status: Ditolak\nlog disbursement.rejected]
    L --> O[Saldo berkurang,\nmasuk Laporan Saldo]
```

Aturan ini berlaku untuk **semua** peran, termasuk Super Admin: tidak ada yang bisa menyetujui pengajuannya sendiri.

---

## 5. Alur Tiket Bantuan

```mermaid
flowchart TD
    A([Pengunjung buka /bantuan]) --> B[Isi nama, email, kategori,\nkode terkait opsional, judul, pesan]
    B --> C{Validasi, batas 5 pesan per jam,\nkolom jebakan bot}
    C -->|lolos| D[Tiket TKT-xxxxxxxx dibuat\nstatus: Terbuka\nlog ticket.created]
    D --> E[Balasan otomatis di percakapan:\nkonfirmasi, status terkini dari kode DON/PRG,\npetunjuk sesuai kategori]
    D --> F[Email berisi tautan pribadi /bantuan/token]
    E --> G[Tiket muncul di inbox staf dan\nantrean Menunggu keputusan di dashboard]
    G --> H{Staf membalas?\nBendahara, Admin, Super Admin}
    H -->|ya| I[Balasan masuk percakapan + email ke pengirim\nstatus: Menunggu pengaju atau Selesai\nlog ticket.replied]
    I --> J[Pengirim membalas lagi di halaman pribadi,\nyang selesai otomatis terbuka kembali]
    J -.diperbarui tiap 4 detik.-> H
    F -.lupa tautan.-> K([Lacak tiket: kode + email,\ntautan dikirim ulang ke email])
```

Auditor hanya bisa membaca tiket. Percakapan di sisi staf dan pengunjung diperbarui otomatis tiap beberapa detik (polling), bukan koneksi langsung.

---

## 6. Alur Login dan Pencatatan Login Log

```mermaid
flowchart TD
    A([Staf isi email dan kata sandi\ndi alamat /login]) --> B{Akun aktif dan\nkredensial benar?}
    B -->|akun dinonaktifkan| C[Pesan: akun dinonaktifkan,\nhubungi super admin]
    B -->|berhasil| D[Listener LogSuccessfulLogin\nlogin_logs status: success]
    B -->|gagal| E[Listener LogFailedLogin\nlogin_logs status: failed]
    D --> F[Dashboard dengan pesan: Berhasil masuk]
    E --> G[Pesan kesalahan di form]
    E --> H{3 atau lebih gagal dalam\n15 menit untuk email yang sama?}
    H -->|ya| I[Muncul di Dashboard Anomali\ndan ditandai di Log Login]
```

Tautan login staf sengaja **tidak** ditampilkan di situs publik; staf mengakses `/login` langsung. Akun yang dinonaktifkan super admin tidak bisa masuk, dan sesi yang sedang berjalan ikut diakhiri pada permintaan berikutnya.

---

## 7. Alur Activity Log dengan Hash Chain

Fitur pembeda utama SIDONA: setiap aksi penting terikat secara kriptografis ke aksi sebelumnya, sehingga manipulasi data lama bisa dideteksi.

### 7.1 Pencatatan (otomatis di setiap aksi tulis)

```mermaid
flowchart TD
    A([Aksi terjadi: donasi dibayar, program diubah,\npenyaluran disetujui, tiket dibalas, dan seterusnya]) --> B[AuditLogger::log]
    B --> C[Ambil hash baris terakhir\ndengan row lock]
    C --> D[prev_hash = hash terakhir\natau genesis hash]
    D --> E[hash = SHA256 dari\nprev_hash + data aksi]
    E --> F[Simpan baris baru:\naction, data lama/baru, prev_hash, hash]
```

Di homepage publik hanya jenis kejadian, waktu, dan sidik hash singkat yang ditampilkan (tanpa nama, kontak, atau nominal).

### 7.2 Verifikasi Integritas (`/audit/integritas`)

```mermaid
flowchart TD
    A([Auditor klik Verifikasi sekarang]) --> B[Ambil seluruh catatan berurutan]
    B --> C[Hitung ulang hash tiap baris]
    C --> D{Cocok dengan yang tersimpan?}
    D -->|semua cocok| E[Rantai utuh: semua N catatan cocok,\nwaktu dan nama pemeriksa]
    D -->|ada yang tidak cocok| F[Rantai terputus: nomor catatan pertama yang\nbermasalah, jenis kejadian, waktu, pelaku]
```

### 7.3 Skenario Demo Manipulasi Data

```mermaid
flowchart TD
    A([php artisan demo:tamper-log]) --> B[Baris log terbaru diubah langsung\nlewat database]
    B --> C[Melewati AuditLogger, hash tidak diperbarui]
    C --> D([Auditor verifikasi integritas])
    D --> E[Rantai dinyatakan terputus\nmulai dari baris itu]
```

---

## 8. Alur Deteksi Anomali dan Pemeriksaan Temuan

```mermaid
flowchart TD
    A([Auditor buka /audit/anomali]) --> B[AnomalyDetector]
    B --> C[Donasi nominal ekstrem:\nlebih dari rata-rata program + 2 simpangan baku]
    B --> D[Penyaluran disetujui\nkurang dari 60 detik setelah diajukan]
    B --> E[3 atau lebih login gagal\ndalam 15 menit untuk email yang sama]
    C --> F[Daftar temuan\nbaris dengan penanda merah]
    D --> F
    E --> F
    F --> G[Auditor klik Detail:\nrincian temuan dan jejak terkait]
    G --> H[Tandai diperiksa\nlog anomaly.reviewed]
    H --> I[Baris menjadi abu-abu,\ntercatat siapa dan kapan]
```

---

## 9. Alur Laporan dan Verifikasi Checksum

```mermaid
flowchart TD
    A([Auditor buka Laporan Donasi,\nPenyaluran, atau Ringkasan Saldo]) --> B[Filter dan ringkasan hasil filter]
    B --> C[Unduh PDF]
    C --> D[Render PDF dengan dompdf]
    D --> E[SHA256 isi PDF ditulis di footer]
    E --> F[report_exports: kode, jenis, checksum, pembuat]
    F --> G([Unduh lewat /laporan/unduh/kode])

    H([Cek Keaslian Laporan]) --> I[Unggah PDF yang pernah diunduh]
    I --> J[Hitung ulang checksum]
    J --> K{Cocok dengan report_exports?}
    K -->|cocok| L[Laporan asli, tampil siapa dan kapan membuatnya]
    K -->|tidak| M[Sudah diubah atau tidak dikenali]
```

---

## 10. Peta Hak Akses Ringkas

```mermaid
flowchart LR
    subgraph Publik["Tanpa login"]
        P1[Lihat dan cari program]
        P2[Donasi dan unduh kuitansi]
        P3[Ajukan program, pantau lewat tautan pribadi]
        P4[Tiket bantuan, FAQ, syarat, privasi]
    end

    subgraph Bendahara["Bendahara"]
        B1[Buat dan ubah program]
        B2[Ajukan penyaluran dana]
        B3[Balas tiket]
    end

    subgraph Admin["Admin"]
        A1[Kelola program, hapus bila belum ada uang]
        A2[Setujui / tolak pengajuan program\nhanya jika email pengaju terkonfirmasi]
        A3[Setujui / tolak penyaluran\nkecuali pengajuan sendiri]
        A4[Balas tiket]
    end

    subgraph Super["Super Admin"]
        S1[Semua kewenangan staf]
        S2[Kelola pengguna: buat, ubah peran,\natur ulang sandi, nonaktifkan]
    end

    subgraph Auditor["Auditor"]
        D1[Verifikasi integritas, log aktivitas dan login]
        D2[Dashboard anomali + tandai diperiksa]
        D3[Laporan berchecksum, cek keaslian]
        D4[Baca tiket]
    end
```
