<?php

namespace App\Enums;

enum TicketCategory: string
{
    case Donation = 'donation';
    case Payment = 'payment';
    case Proposal = 'proposal';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Donation => 'Donasi saya',
            self::Payment => 'Masalah pembayaran',
            self::Proposal => 'Pengajuan program',
            self::Other => 'Lainnya',
        };
    }
}
