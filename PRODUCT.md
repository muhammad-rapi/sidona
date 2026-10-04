# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
- **Donatur (tamu, tanpa akun):** publik umum Indonesia, sering di ponsel, ingin menyumbang ke program sosial/kemanusiaan dengan cepat dan yakin dananya sampai. Tidak login.
- **Staf panel (login):** Bendahara (kelola program, ajukan penyaluran), Admin (kelola/hapus program, setujui penyaluran), Auditor (verifikasi integritas log, laporan, anomali). Bekerja di desktop.

## Product Purpose
SIDONA adalah platform galang dana umum untuk berbagai program sosial/kemanusiaan, dengan jejak audit yang tidak bisa diubah diam-diam (hash chain). Sukses: donatur selesai berdonasi dalam hitungan detik tanpa menunggu persetujuan siapa pun, dan staf dapat membuktikan ke mana setiap rupiah pergi.

## Positioning
Galang dana yang transparan secara teknis: setiap donasi, perubahan, dan penyaluran tercatat di log berantai yang dapat diverifikasi auditor, dan laporan PDF punya checksum keaslian.

## Operating Context
Laravel + Livewire, bahasa Indonesia. Donasi dikonfirmasi otomatis oleh sistem pembayaran; tidak ada verifikasi manual oleh admin. Gateway asli belum dipasang: tahap ini memakai pembayaran simulasi (QRIS tiruan yang otomatis terkonfirmasi) di balik antarmuka yang dapat diganti Midtrans/Xendit kelak. Penyaluran dana tetap melalui pengajuan Bendahara dan persetujuan Admin.

## Capabilities and Constraints
- Donasi: pilih program, nominal, nama/kontak (anonim opsional), bayar, kode referensi, cek status. Tidak ada upload bukti transfer dan tidak ada antrean verifikasi/tolak donasi.
- Panel staf wajib memakai sidebar; area publik memakai navbar; tautan login staf disembunyikan dari navbar publik (akses via URL /login).
- Audit log hash-chain, laporan PDF, deteksi anomali, penyaluran: dipertahankan.
- Belum diputuskan: gateway pembayaran produksi.

## Product Principles
1. Donatur tidak pernah menunggu manusia untuk donasinya sah.
2. Kepercayaan dibangun dari bukti (progres, transparansi), bukan klaim.
3. Panel staf adalah alat kerja: padat, terbaca, konsisten.
4. Setiap perubahan uang tetap meninggalkan jejak audit.

## Accessibility & Inclusion
Dominan pengguna ponsel di Indonesia; kontras dan target sentuh memadai, teks bahasa Indonesia yang jelas.
