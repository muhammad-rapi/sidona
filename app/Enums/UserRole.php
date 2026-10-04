<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Bendahara = 'bendahara';
    case Admin = 'admin';
    case Auditor = 'auditor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Bendahara => 'Bendahara',
            self::Admin => 'Admin',
            self::Auditor => 'Auditor',
        };
    }
}
