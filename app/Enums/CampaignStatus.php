<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu tinjauan',
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }
}
