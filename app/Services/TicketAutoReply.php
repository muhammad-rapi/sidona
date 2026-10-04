<?php

namespace App\Services;

use App\Enums\TicketCategory;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Ticket;
use App\Models\TicketMessage;

/**
 * Balasan otomatis saat tiket dibuat: konfirmasi, status terkini dari kode yang disebut,
 * dan petunjuk sesuai kategori. Tidak menjanjikan waktu balasan.
 */
class TicketAutoReply
{
    public const AUTHOR = 'SIDONA (otomatis)';

    public function post(Ticket $ticket): TicketMessage
    {
        return $ticket->messages()->create([
            'author_name' => self::AUTHOR,
            'is_staff' => true,
            'is_auto' => true,
            'body' => $this->body($ticket),
        ]);
    }

    public function body(Ticket $ticket): string
    {
        $lines = [
            "Terima kasih, {$ticket->name}. Pesan Anda sudah kami terima dengan kode {$ticket->code}.",
            'Tim kami akan membalas di halaman ini dan lewat email. Anda tidak perlu mengirim ulang pesan yang sama.',
        ];

        if ($status = $this->relatedStatus($ticket)) {
            $lines[] = $status;
        }

        $lines[] = $this->hint($ticket->category);

        return implode("\n\n", array_filter($lines));
    }

    private function relatedStatus(Ticket $ticket): ?string
    {
        $code = $ticket->related_code;

        if ($code && str_starts_with($code, 'DON-')) {
            $donation = Donation::query()->where('reference_code', $code)->first();

            return $donation
                ? "Sambil menunggu: donasi {$code} saat ini berstatus \"{$donation->status->label()}\"."
                : "Kode {$code} belum kami temukan. Mohon periksa lagi huruf dan angkanya.";
        }

        if ($code && str_starts_with($code, 'PRG-')) {
            $campaign = Campaign::query()->where('proposal_code', $code)->first();

            return $campaign
                ? "Sambil menunggu: pengajuan {$code} saat ini berstatus \"{$campaign->status->label()}\"."
                : "Kode {$code} belum kami temukan. Mohon periksa lagi huruf dan angkanya.";
        }

        return null;
    }

    private function hint(TicketCategory $category): string
    {
        return match ($category) {
            TicketCategory::Payment => 'Pembayaran biasanya terkonfirmasi otomatis dalam beberapa menit. Kalau status donasi masih menunggu, coba buka lagi kuitansi lewat menu Cek Donasi.',
            TicketCategory::Donation => 'Kuitansi donasi bisa dibuka kapan saja lewat menu Cek Donasi dengan kode DON-...',
            TicketCategory::Proposal => 'Status pengajuan program bisa dilihat lewat menu Cek Donasi dengan kode PRG-.... Pastikan email Anda sudah dikonfirmasi lewat tautan yang kami kirim.',
            TicketCategory::Other => 'Siapa tahu jawabannya sudah ada: baca juga halaman Pertanyaan Umum (FAQ).',
        };
    }
}
