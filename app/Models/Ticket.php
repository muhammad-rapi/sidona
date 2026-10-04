<?php

namespace App\Models;

use App\Enums\TicketCategory;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'code', 'token', 'name', 'email', 'category', 'related_code', 'subject',
        'status', 'assigned_to', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'category' => TicketCategory::class,
            'last_activity_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->oldest('id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TKT-'.strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function needsStaffReply(): bool
    {
        $last = $this->messages->last();

        return $this->status !== TicketStatus::Closed && $last !== null && ! $last->is_staff;
    }
}
