<?php

namespace Database\Seeders;

use App\Enums\DisbursementStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Disbursement;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class DisbursementSeeder extends Seeder
{
    public function run(AuditLogger $logger): void
    {
        $bendaharas = User::query()->where('role', UserRole::Bendahara)->get();
        $admins = User::query()->where('role', UserRole::Admin)->get();
        $seededFastApproval = false;

        foreach (Campaign::all() as $campaign) {
            if ($campaign->availableBalance() <= 0) {
                continue;
            }

            $count = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $count; $i++) {
                $balance = $campaign->availableBalance();

                if ($balance < 50_000) {
                    break;
                }

                $bendahara = $bendaharas->random();
                $amount = fake()->numberBetween(50_000, min($balance, 500_000));

                $disbursement = Disbursement::create([
                    'campaign_id' => $campaign->id,
                    'amount' => $amount,
                    'description' => fake()->randomElement([
                        'Pembelian terpal, selimut, dan tikar untuk hunian sementara',
                        'Paket sembako untuk 40 keluarga terdampak',
                        'Biaya pengiriman logistik ke lokasi',
                        'Pembayaran biaya sekolah semester berjalan',
                        'Perbaikan atap dan kamar mandi',
                        'Obat-obatan dan perlengkapan kesehatan dasar',
                        'Pembelian air bersih dan perlengkapan bayi',
                        'Honor relawan lapangan selama seminggu',
                    ]),
                    'status' => DisbursementStatus::Submitted,
                    'submitted_by' => $bendahara->id,
                ]);

                $logger->log('disbursement.submitted', $bendahara, $disbursement, [], $disbursement->only([
                    'campaign_id', 'amount', 'description', 'status', 'submitted_by',
                ]));

                $roll = fake()->numberBetween(1, 100);

                if ($roll <= 50) {
                    $admin = $admins->random();
                    $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at']);

                    $reviewedAt = $seededFastApproval
                        ? $disbursement->created_at->addMinutes(fake()->numberBetween(30, 2000))
                        : $disbursement->created_at->addSeconds(fake()->numberBetween(5, 45));

                    $seededFastApproval = true;

                    $disbursement->update([
                        'status' => DisbursementStatus::Approved,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => $reviewedAt,
                    ]);

                    $logger->log('disbursement.approved', $admin, $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at']));
                } elseif ($roll <= 80) {
                    $admin = $admins->random();
                    $before = $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']);

                    $disbursement->update([
                        'status' => DisbursementStatus::Rejected,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => $disbursement->created_at->addMinutes(fake()->numberBetween(30, 2000)),
                        'rejection_reason' => 'Keterangan penggunaan dana kurang rinci.',
                    ]);

                    $logger->log('disbursement.rejected', $admin, $disbursement, $before, $disbursement->only(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason']));
                }

                $campaign->refresh();
            }
        }
    }
}
