<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CampaignPhoto extends Model
{
    protected $fillable = ['campaign_id', 'path', 'caption', 'position'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function url(): string
    {
        return Storage::url($this->path);
    }
}
