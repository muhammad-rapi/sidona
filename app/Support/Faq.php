<?php

namespace App\Support;

class Faq
{
    /**
     * @return array<string, array<int, array{0: string, 1: string}>>
     */
    public static function groups(): array
    {
        return [
            'Berdonasi' => [
                ['Bagaimana cara berdonasi?', 'Buka program yang ingin Anda bantu, pilih nominal, isi nama dan email atau WhatsApp, pilih cara bayar, lalu bayar. Tidak perlu membuat akun.'],
                ['Cara bayar apa saja yang tersedia?', 'QRIS, Virtual Account, dan e-wallet. Saat ini pembayaran masih berjalan dalam mode simulasi: tidak ada uang yang berpindah, dan halaman bayar memberi tahu hal itu dengan jelas.'],
                ['Berapa nominal donasi minimal dan maksimal?', 'Minimal Rp 10.000 dan maksimal Rp 1.000.000.000 per transaksi.'],
                ['Kapan donasi saya dianggap berhasil?', 'Otomatis, begitu pembayaran terkonfirmasi. Tidak ada admin yang perlu menyetujui donasi. Setelah berhasil, kuitansi langsung terbit.'],
                ['Kuitansi atau kodenya hilang, bagaimana?', 'Kode kuitansi (DON-...) muncul di layar setelah membayar dan dikirim ke email kalau Anda mengisi email. Kode itu dipakai di menu Cek Donasi untuk membuka kuitansi lagi.'],
                ['Bisakah saya berdonasi tanpa nama?', 'Bisa. Centang "Samarkan nama saya". Di halaman publik nama Anda tampil sebagai Hamba Allah. Nama asli tetap tersimpan dan hanya terlihat oleh staf untuk keperluan pembukuan dan audit.'],
                ['Bisakah donasi dibatalkan atau dikembalikan?', 'SIDONA tidak menyediakan pembatalan otomatis untuk donasi yang sudah berhasil. Kalau terjadi salah transfer, hubungi pengelola dan sebutkan kode kuitansi Anda.'],
            ],
            'Penyaluran dana' => [
                ['Bagaimana dana disalurkan?', 'Bendahara mengajukan penyaluran, lalu admin yang berbeda menyetujuinya. Satu orang tidak bisa menyetujui pengajuannya sendiri, dan jumlahnya tidak boleh melebihi saldo program.'],
                ['Bagaimana saya tahu uangnya tidak diubah-ubah?', 'Setiap donasi, perubahan, dan penyaluran masuk ke log berantai: tiap catatan terikat pada catatan sebelumnya, jadi perubahan diam-diam akan terdeteksi. Auditor memeriksa log itu, dan laporan PDF yang mereka terbitkan memiliki checksum keaslian.'],
            ],
            'Mengajukan program' => [
                ['Bagaimana cara mengajukan program donasi?', 'Buka menu Ajukan Program, isi cerita, target, rekening penerima, dan kontak Anda. Admin akan memeriksa sebelum program tayang.'],
                ['Bagaimana saya tahu pengajuan saya sudah diputuskan?', 'Setelah mengirim, Anda mendapat kode pengajuan (PRG-...). Masukkan kode itu di menu Cek Donasi untuk melihat statusnya. Kalau Anda mengisi email, kami juga mengirim kabar ke sana saat program disetujui atau ditolak.'],
                ['Kenapa pengajuan bisa ditolak?', 'Misalnya data rekening tidak bisa diverifikasi, cerita kurang jelas, atau tujuannya tidak termasuk program sosial. Alasannya selalu ditampilkan, dan Anda boleh mengajukan lagi setelah memperbaikinya.'],
                ['Berapa lama program berjalan?', 'Anda memilih 14, 30, 60, atau 90 hari. Hitungannya dimulai pada hari program disetujui.'],
            ],
            'Data dan keamanan' => [
                ['Data apa yang disimpan tentang saya?', 'Nama, kontak, nominal, metode dan waktu pembayaran donasi Anda. Rinciannya ada di Kebijakan Privasi.'],
                ['Apakah data kartu atau rekening saya disimpan?', 'Tidak. SIDONA tidak meminta dan tidak menyimpan nomor kartu atau data login bank Anda.'],
            ],
        ];
    }

    /**
     * Pertanyaan teratas untuk halaman utama.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    public static function highlights(): array
    {
        $all = collect(self::groups())->flatten(1);
        $wanted = [
            'Bagaimana cara berdonasi?',
            'Kapan donasi saya dianggap berhasil?',
            'Bisakah saya berdonasi tanpa nama?',
            'Bagaimana saya tahu uangnya tidak diubah-ubah?',
            'Bagaimana cara mengajukan program donasi?',
        ];

        return $all->filter(fn (array $item) => in_array($item[0], $wanted, true))->values()->all();
    }
}
