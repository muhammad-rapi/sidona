<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportChecksum
{
    public const PLACEHOLDER = '0000000000000000000000000000000000000000000000000000000000000000';

    public function footerHtml(): string
    {
        return '<div style="white-space: nowrap;">Checksum SHA-256: '.self::PLACEHOLDER.'</div>';
    }

    public function renderDraft(string $html): string
    {
        return Pdf::loadHTML($html)->output(['compress' => 0]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function stamp(string $draftBytes): array
    {
        $checksum = hash('sha256', $draftBytes);
        $final = str_replace(self::PLACEHOLDER, $checksum, $draftBytes);

        return [$checksum, $final];
    }

    /**
     * @return array{valid: bool, checksum: ?string}
     */
    public function verify(string $uploadedBytes): array
    {
        if (! preg_match('/Checksum SHA-256:\s*([0-9a-f]{64})/', $uploadedBytes, $matches)) {
            return ['valid' => false, 'checksum' => null];
        }

        $claimedChecksum = $matches[1];
        $reconstructed = str_replace($claimedChecksum, self::PLACEHOLDER, $uploadedBytes);
        $recomputed = hash('sha256', $reconstructed);

        return [
            'valid' => $recomputed === $claimedChecksum,
            'checksum' => $claimedChecksum,
        ];
    }
}
