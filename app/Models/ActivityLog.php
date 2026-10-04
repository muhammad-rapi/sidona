<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'before',
        'after',
        'prev_hash',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Deskripsi singkat objek yang dikenai aksi, tanpa data pribadi tambahan.
     */
    public function subjectLabel(): string
    {
        $subject = $this->subject;
        $name = data_get($this->after, 'name') ?? data_get($this->before, 'name');

        return match (true) {
            $subject instanceof Campaign => 'Program: '.$subject->name,
            $subject instanceof Donation => 'Donasi '.$subject->reference_code.' (Rp '.number_format($subject->amount, 0, ',', '.').')',
            $subject instanceof Disbursement => 'Penyaluran Rp '.number_format($subject->amount, 0, ',', '.').' ('.($subject->campaign?->name ?? 'program').')',
            $subject instanceof User => 'Pengguna: '.$subject->name,
            $this->subject_type === Campaign::class => 'Program: '.($name ?? '#'.$this->subject_id).' (sudah dihapus)',
            $this->subject_type === AnomalyReview::class => 'Temuan anomali',
            $this->subject_type !== null => class_basename($this->subject_type).' #'.$this->subject_id,
            default => '-',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
