<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    public const STATUS_ATTENDING = 'attending';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ATTENDING,
            self::STATUS_DECLINED,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ATTENDING => 'Akan Ikut',
            self::STATUS_DECLINED => 'Tidak Ikut',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
