<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * Records security-relevant application events without storing passwords,
 * authentication tokens, or other secrets.
 */
class ActivityLogger
{
    public static function record(
        string $activity,
        ?string $description = null,
        ?int $userId = null
    ): void {
        ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'activity' => $activity,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500) ?: null,
        ]);
    }
}
