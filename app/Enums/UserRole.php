<?php

namespace App\Enums;

enum UserRole: string
{
    case Bendahara = 'bendahara';
    case Admin = 'admin';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::Bendahara => 'Bendahara',
            self::Admin => 'Admin',
            self::Auditor => 'Auditor',
        };
    }
}
