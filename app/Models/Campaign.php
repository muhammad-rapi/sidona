<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cover_image',
        'target_amount',
        'bank_name',
        'account_number',
        'account_holder',
        'starts_on',
        'ends_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => CampaignStatus::class,
        ];
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function verifiedDonationsTotal(): int
    {
        return (int) $this->donations()->where('status', DonationStatus::Verified)->sum('amount');
    }

    public function approvedDisbursementsTotal(): int
    {
        return (int) $this->disbursements()->where('status', DisbursementStatus::Approved)->sum('amount');
    }

    public function availableBalance(): int
    {
        return $this->verifiedDonationsTotal() - $this->approvedDisbursementsTotal();
    }
}
