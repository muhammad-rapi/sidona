<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Waiting = 'waiting';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::Waiting => 'Menunggu pengaju',
            self::Closed => 'Selesai',
        };
    }
}
