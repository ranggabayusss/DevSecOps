<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected static function booted(): void
    {
        static::updating(static function (): bool {
            return false;
        });

        static::deleting(static function (): bool {
            return false;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
