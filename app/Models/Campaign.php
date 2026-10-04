<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\DisbursementStatus;
use App\Enums\DonationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'pic_user_id',
        'pic_name',
        'pic_contact',
        'proposal_code',
        'proposer_name',
        'proposer_contact',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => CampaignStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    /**
     * Kontak PIC (hanya untuk staf, tidak pernah ditampilkan di publik).
     */
    public function picContact(): ?string
    {
        return $this->pic_name ? $this->pic_contact : ($this->pic?->email ?? $this->proposer_contact);
    }

    /**
     * Nama penanggung jawab: staf yang ditunjuk, atau pengaju untuk program dari tamu.
     */
    public function picName(): ?string
    {
        return $this->pic_name ?: ($this->pic?->name ?? $this->proposer_name);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CampaignPhoto::class)->orderBy('position')->orderBy('id');
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

    public function progressPercent(?int $raised = null): int
    {
        $raised ??= $this->verifiedDonationsTotal();

        return $this->target_amount > 0
            ? min(100, (int) floor($raised / $this->target_amount * 100))
            : 0;
    }

    public function daysLeft(): int
    {
        return max(0, (int) ceil(now()->startOfDay()->diffInDays($this->ends_on->startOfDay(), false)));
    }

    public function availableBalance(): int
    {
        return $this->verifiedDonationsTotal() - $this->approvedDisbursementsTotal();
    }
}
