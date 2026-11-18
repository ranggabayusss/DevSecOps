<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public const STATUS_UPCOMING = 'upcoming';

    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'title',
        'description',
        'location',
        'starts_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_UPCOMING,
            self::STATUS_ONGOING,
            self::STATUS_FINISHED,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_UPCOMING => 'Akan Datang',
            self::STATUS_ONGOING => 'Berlangsung',
            self::STATUS_FINISHED => 'Selesai',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendingRsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class)->where('status', EventRsvp::STATUS_ATTENDING);
    }

    public function rsvpFor(?User $user): ?EventRsvp
    {
        if (! $user) {
            return null;
        }

        return $this->rsvps()->where('user_id', $user->id)->first();
    }
}
