<?php

namespace App\Services;

use App\Enums\DonationStatus;
use App\Mail\DonationPaidMail;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Konfirmasi pembayaran donasi secara otomatis, tanpa verifikasi manual staf.
 *
 * Saat ini dipanggil oleh simulator pembayaran. Notifikasi dari gateway
 * sungguhan (Midtrans/Xendit) cukup memanggil confirm() yang sama.
 */
class DonationPayment
{
    public function __construct(private AuditLogger $logger) {}

    public function confirm(Donation $donation, string $source = 'simulation'): Donation
    {
        $confirmed = DB::transaction(function () use ($donation, $source) {
            $locked = Donation::query()->lockForUpdate()->findOrFail($donation->id);

            if ($locked->status !== DonationStatus::Pending) {
                return null;
            }

            $before = $locked->only(['status', 'paid_at']);

            $locked->update([
                'status' => DonationStatus::Verified,
                'paid_at' => now(),
            ]);

            $this->logger->log(
                'donation.paid',
                null,
                $locked,
                $before,
                $locked->only(['status', 'paid_at', 'payment_method']) + ['source' => $source],
            );

            return $locked;
        });

        if ($confirmed === null) {
            return $donation->refresh();
        }

        if (filter_var($confirmed->donor_contact, FILTER_VALIDATE_EMAIL)) {
            Mail::to($confirmed->donor_contact)->send(new DonationPaidMail($confirmed->load('campaign')));
        }

        return $confirmed;
    }
}
