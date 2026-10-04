# Alur Kerja SIDONA

Dokumen ini memetakan alur kerja (workflow) tiap fitur yang ada di SIDONA dalam bentuk diagram. Untuk penjelasan cara instalasi, akun demo, dan peta halaman, lihat [`PANDUAN_PENGGUNAAN.md`](PANDUAN_PENGGUNAAN.md).

Diagram memakai format Mermaid, otomatis tampil sebagai gambar di GitHub dan kebanyakan pratinjau markdown.

---

## 1. Peta Alur Keseluruhan Sistem

Gambaran bagaimana keempat jenis pengguna berinteraksi dengan bagian bagian utama sistem.

```mermaid
flowchart TD
    Donatur([Donatur / Tamu]) -->|isi form donasi| Donasi[(Donasi)]
    Donatur -->|cek status via kode referensi| Donasi

    Bendahara([Bendahara]) -->|kelola| Program[(Program Donasi)]
    Bendahara -->|verifikasi / tolak| Donasi
    Bendahara -->|ajukan| Penyaluran[(Penyaluran Dana)]

    Admin([Admin]) -->|kelola / hapus| Program
    Admin -->|setujui / tolak| Penyaluran

    Program -.setiap perubahan tercatat.-> Log[(Activity Log + Hash Chain)]
    Donasi -.setiap perubahan tercatat.-> Log
    Penyaluran -.setiap perubahan tercatat.-> Log
    Login[(Login Log)] -.dibaca.-> Anomali

    Auditor([Auditor]) -->|verifikasi keutuhan| Log
    Auditor -->|pantau| Anomali[Dashboard Anomali]
    Auditor -->|lihat & export| Log
    Auditor -->|lihat| Login
    Auditor -->|buat & unduh| Laporan[Laporan PDF Berchecksum]
    Auditor -->|cek keaslian| Laporan

    Log -.sumber data.-> Anomali
    Donasi -.sumber data.-> Anomali
    Penyaluran -.sumber data.-> Anomali
    Program -.sumber data.-> Laporan
    Donasi -.sumber data.-> Laporan
    Penyaluran -.sumber data.-> Laporan
```

---

## 2. Alur Donasi

Dari donatur mengisi form sampai donasi diverifikasi atau ditolak Bendahara.

```mermaid
flowchart TD
    A([Donatur buka /program/{id}]) --> B[Isi form donasi:\nnama, kontak, nominal,\nwaktu transfer, bukti transfer]
    B --> C{Validasi input}
    C -->|nominal tidak valid\natau bukti transfer kosong| B
    C -->|valid| D[Sistem simpan donasi\nstatus: Menunggu\n+ buat kode referensi unik]
    D --> E[Donatur menerima kode referensi]
    E --> F([Donatur bisa cek status\nkapan saja di /donasi/cek])

    D --> G[Bendahara buka /donasi]
    G --> H{Bendahara tinjau\nbukti transfer}
    H -->|setuju| I[Klik Verifikasi]
    H -->|tidak sesuai| J[Klik Tolak\n+ wajib isi alasan]

    I --> K[Status donasi: Terverifikasi\ntercatat verified_by dan verified_at]
    J --> L[Status donasi: Ditolak\ntercatat rejection_reason]

    K --> M[AuditLogger mencatat\naksi donation.verified]
    J --> N[AuditLogger mencatat\naksi donation.rejected]

    K --> O{Kontak donatur\nberupa email valid?}
    O -->|ya| P[Kirim email pemberitahuan\ndonasi terverifikasi]
    O -->|tidak| Q[Lewati pengiriman email]

    K --> R[Saldo program bertambah,\nikut dihitung di Laporan Saldo]
```

Catatan aturan: hanya donasi berstatus Menunggu yang bisa diverifikasi atau ditolak, dan hanya Bendahara yang berwenang melakukannya (`DonationPolicy`).

---

## 3. Alur Pengajuan dan Persetujuan Penyaluran Dana

Ini menerapkan prinsip pemisahan tugas (maker checker): yang mengajukan tidak boleh menjadi yang menyetujui.

```mermaid
flowchart TD
    A([Bendahara buka\n/campaigns/{id}/penyaluran/ajukan]) --> B{Program aktif dan\nsaldo tersedia lebih dari 0?}
    B -->|tidak| C[Form ditolak sistem]
    B -->|ya| D[Isi jumlah dan\nketerangan penggunaan dana]
    D --> E{Jumlah melebihi\nsaldo tersedia?}
    E -->|ya| D
    E -->|tidak| F[Simpan pengajuan\nstatus: Diajukan\nsubmitted_by = Bendahara]

    F --> G[AuditLogger mencatat\naksi disbursement.created]
    F --> H[Admin buka /penyaluran]
    H --> I{Admin yang login\n= pengaju pengajuan ini?}
    I -->|ya| J[Tombol setujui/tolak\ntidak tersedia untuk pengajuan ini]
    I -->|tidak| K{Admin setujui\natau tolak?}

    K -->|Setujui| L{Cek ulang saldo program\nsaat ini, terkunci transaksi DB}
    L -->|saldo cukup| M[Status: Disetujui\nreviewed_by, reviewed_at tercatat]
    L -->|saldo sudah tidak cukup\nkarena pengajuan lain| N[Persetujuan ditolak sistem]

    K -->|Tolak| O[Status: Ditolak\nwajib isi alasan]

    M --> P[AuditLogger mencatat\naksi disbursement.approved]
    O --> Q[AuditLogger mencatat\naksi disbursement.rejected]

    M --> R[Saldo program berkurang,\nikut dihitung di Laporan Saldo]
```

Catatan aturan: `DisbursementPolicy` menolak persetujuan/penolakan kalau pengaju dan penyetuju adalah user yang sama, dan pengecekan saldo diulang dengan row lock saat persetujuan diproses supaya dua pengajuan yang diproses bersamaan tidak membuat saldo minus.

---

## 4. Alur Login dan Pencatatan Login Log

```mermaid
flowchart TD
    A([User isi email dan kata sandi\ndi /login]) --> B{Auth::attempt}
    B -->|berhasil| C[Event Login dipicu]
    B -->|gagal| D[Event Failed dipicu]

    C --> E[Listener LogSuccessfulLogin\nsimpan baris login_logs\nstatus: success]
    D --> F[Listener LogFailedLogin\nsimpan baris login_logs\nstatus: failed]

    E --> G[User diarahkan ke /dashboard\nsesuai role masing masing]
    F --> H[Form login tampilkan\npesan kesalahan]

    F --> I{3 atau lebih percobaan\ngagal dalam 15 menit\nuntuk email yang sama?}
    I -->|ya| J[Muncul sebagai anomali\ndi Dashboard Anomali]
    I -->|tidak| K[Tidak ada flag]
```

---

## 5. Alur Activity Log dengan Hash Chain

Ini fitur pembeda utama SIDONA: setiap aksi penting terikat secara kriptografis ke aksi sebelumnya, sehingga manipulasi data lama bisa dideteksi.

### 5.1 Pencatatan (terjadi otomatis di setiap aksi tulis)

```mermaid
flowchart TD
    A([Aksi terjadi:\ncampaign/donation/disbursement\ndibuat, diubah, atau dihapus]) --> B[AuditLogger::log dipanggil]
    B --> C[Ambil hash baris terakhir\ndi activity_logs\ndengan row lock]
    C --> D[prev_hash = hash baris terakhir\natau genesis hash kalau baris pertama]
    D --> E[hash = SHA256 dari\nprev_hash + data aksi]
    E --> F[Simpan baris baru:\naction, data lama/baru,\nprev_hash, hash]
```

### 5.2 Verifikasi Integritas (halaman Auditor `/audit/integritas`)

```mermaid
flowchart TD
    A([Auditor buka /audit/integritas\nklik Verifikasi]) --> B[Ambil seluruh baris activity_logs\nberurutan dari yang paling lama]
    B --> C[Hitung ulang hash tiap baris\nberdasarkan prev_hash dan datanya]
    C --> D{Hash hasil hitung ulang\ncocok dengan hash tersimpan?}
    D -->|cocok semua| E[Status: Chain valid]
    D -->|ada yang tidak cocok| F[Status: Chain rusak,\ntunjukkan ID baris pertama\nyang mencurigakan]
```

### 5.3 Skenario Demo Manipulasi Data

```mermaid
flowchart TD
    A([Jalankan:\nphp artisan demo:tamper-log]) --> B[Baris activity log\npaling baru diubah langsung\nlewat database]
    B --> C[Perubahan ini melewati\nAuditLogger, tidak\nmemperbarui hash]
    C --> D([Auditor buka /audit/integritas\nklik Verifikasi])
    D --> E[Sistem mendeteksi\nhash baris tersebut\ntidak lagi cocok]
    E --> F[Chain dinyatakan rusak\nmulai dari baris itu]
```

---

## 6. Alur Deteksi Anomali

```mermaid
flowchart TD
    A([Auditor buka /audit/anomali]) --> B[AnomalyDetector dijalankan]

    B --> C[extremeDonations]
    C --> C1[Hitung rata rata dan\nstandar deviasi nominal donasi\nper program]
    C1 --> C2{Ada donasi lebih besar dari\nrata rata + 2 kali standar deviasi?}
    C2 -->|ya| C3[Tandai sebagai anomali:\ndonasi nominal ekstrem]

    B --> D[fastApprovedDisbursements]
    D --> D1{Ada penyaluran dana disetujui\nkurang dari 60 detik\nsetelah diajukan?}
    D1 -->|ya| D2[Tandai sebagai anomali:\npersetujuan tergesa gesa]

    B --> E[failedLoginStreaks]
    E --> E1{Ada 3 atau lebih\nlogin gagal dalam 15 menit\nuntuk email yang sama?}
    E1 -->|ya| E2[Tandai sebagai anomali:\npercobaan login mencurigakan]

    C3 --> F[Semua anomali ditampilkan\ndi Dashboard Anomali]
    D2 --> F
    E2 --> F
```

---

## 7. Alur Laporan dan Verifikasi Checksum

### 7.1 Membuat dan Mengunduh Laporan

```mermaid
flowchart TD
    A([Auditor buka salah satu:\n/laporan/donasi, /laporan/penyaluran,\natau /laporan/saldo]) --> B[Sistem susun data laporan\ndari database]
    B --> C[Render ke PDF pakai dompdf]
    C --> D[ReportChecksum hitung\nSHA256 dari isi PDF]
    D --> E[Checksum ditulis\nke footer PDF]
    E --> F[Simpan record di report_exports:\nkode referensi, jenis laporan,\nchecksum, siapa yang membuat]
    F --> G[File PDF disimpan\ndi disk privat]
    G --> H([Auditor unduh lewat\n/laporan/unduh/{kode referensi}])
```

### 7.2 Cek Keaslian Laporan

```mermaid
flowchart TD
    A([Auditor buka /laporan/cek-keaslian]) --> B[Unggah file PDF laporan\nyang pernah diunduh]
    B --> C[Sistem hitung ulang checksum\ndari isi PDF yang diunggah]
    C --> D{Cocok dengan checksum\ndi tabel report_exports?}
    D -->|cocok| E[Tampilkan: laporan asli,\ntunjukkan siapa dan kapan\nlaporan awalnya dibuat]
    D -->|tidak cocok| F[Tampilkan: laporan\nsudah diubah / tidak dikenali]
```

---

## 8. Peta Hak Akses Ringkas

```mermaid
flowchart LR
    subgraph Publik["Tanpa login"]
        P1[Lihat daftar & detail program]
        P2[Kirim donasi]
        P3[Cek status donasi]
    end

    subgraph Bendahara["Bendahara"]
        B1[Kelola program]
        B2[Verifikasi / tolak donasi]
        B3[Ajukan penyaluran dana]
    end

    subgraph Admin["Admin"]
        A1[Kelola & hapus program]
        A2[Setujui / tolak penyaluran\nkecuali pengajuan sendiri]
    end

    subgraph Auditor["Auditor, hanya baca"]
        D1[Verifikasi integritas hash chain]
        D2[Log aktivitas & login]
        D3[Dashboard anomali]
        D4[Laporan berchecksum]
        D5[Cek keaslian laporan]
    end
```
