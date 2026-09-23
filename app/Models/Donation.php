<?php

namespace App\Models;

use App\Enums\DonationStatus;
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
        'amount',
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
            'verified_at' => 'datetime',
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
