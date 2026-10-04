<?php

namespace App\Models;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'donor_name',
        'donor_contact',
        'is_anonymous',
        'amount',
        'payment_method',
        'transferred_at',
        'paid_at',
        'proof_path',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => DonationStatus::class,
            'payment_method' => PaymentMethod::class,
            'is_anonymous' => 'boolean',
            'verified_at' => 'datetime',
            'transferred_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isPaid(): bool
    {
        return $this->status === DonationStatus::Verified;
    }

    public function publicName(): string
    {
        return $this->is_anonymous ? 'Hamba Allah' : $this->donor_name;
    }

    public static function generateReferenceCode(): string
    {
        do {
            $code = 'DON-'.strtoupper(Str::random(8));
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation) {
            $donation->reference_code ??= self::generateReferenceCode();
        });
    }
}
