<?php

namespace App\Support;

class Rupiah
{
    /**
     * Bentuk ringkas untuk angka besar: 2.898.869 menjadi "2,9 jt".
     */
    public static function short(int $amount): string
    {
        $format = fn (float $n) => rtrim(rtrim(number_format($n, 1, ',', '.'), '0'), ',');

        return match (true) {
            $amount >= 1_000_000_000 => $format($amount / 1_000_000_000).' M',
            $amount >= 1_000_000 => $format($amount / 1_000_000).' jt',
            $amount >= 1_000 => $format($amount / 1_000).' rb',
            default => (string) $amount,
        };
    }
}
