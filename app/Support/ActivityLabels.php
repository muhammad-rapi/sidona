<?php

namespace App\Support;

class ActivityLabels
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'donation.created' => 'Donasi dibuat',
            'donation.paid' => 'Donasi dibayar',
            'campaign.proposed' => 'Program diajukan tamu',
            'campaign.proposer_verified' => 'Email pengaju dikonfirmasi',
            'campaign.created' => 'Program dibuat',
            'campaign.updated' => 'Program diubah',
            'campaign.approved' => 'Pengajuan program disetujui',
            'campaign.rejected' => 'Pengajuan program ditolak',
            'campaign.deleted' => 'Program dihapus',
            'campaign.photos_added' => 'Foto galeri ditambah',
            'campaign.photo_removed' => 'Foto galeri dihapus',
            'disbursement.submitted' => 'Penyaluran diajukan',
            'disbursement.approved' => 'Penyaluran disetujui',
            'disbursement.rejected' => 'Penyaluran ditolak',
            'ticket.created' => 'Tiket bantuan dibuat',
            'ticket.replied' => 'Tiket dibalas',
            'ticket.status_changed' => 'Status tiket diubah',
            'ticket.assigned' => 'Tiket ditangani',
            'anomaly.reviewed' => 'Anomali ditandai diperiksa',
            'user.created' => 'Pengguna dibuat',
            'user.updated' => 'Pengguna diubah',
            'user.password_reset' => 'Kata sandi diatur ulang admin',
            'user.activated' => 'Akun diaktifkan',
            'user.deactivated' => 'Akun dinonaktifkan',
            'profile.updated' => 'Profil diubah',
            'profile.password_changed' => 'Kata sandi diganti sendiri',
        ];
    }

    public static function label(string $action): string
    {
        return self::all()[$action] ?? $action;
    }
}
