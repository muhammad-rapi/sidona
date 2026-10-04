<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Qris = 'qris';
    case VirtualAccount = 'va';
    case EWallet = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::VirtualAccount => 'Virtual Account',
            self::EWallet => 'E-Wallet',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Qris => 'Scan dari aplikasi bank atau e-wallet apa pun',
            self::VirtualAccount => 'Transfer dari ATM atau mobile banking',
            self::EWallet => 'GoPay, OVO, DANA, ShopeePay',
        };
    }
}
