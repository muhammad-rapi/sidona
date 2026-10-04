<?php

namespace App\Support;

class Banks
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'CIMB Niaga', 'Permata', 'BTN', 'Danamon',
            'OCBC', 'Panin', 'Maybank', 'Mega', 'Muamalat', 'Jago', 'SeaBank', 'Bank DKI',
            'BJB', 'Bank Jateng', 'Bank Jatim',
        ];
    }

    /**
     * Daftar bank, ditambah nilai yang sudah tersimpan agar data lama tetap bisa dipilih.
     *
     * @return array<int, string>
     */
    public static function with(?string $current): array
    {
        $banks = self::all();

        return ($current && ! in_array($current, $banks, true)) ? [...$banks, $current] : $banks;
    }
}
