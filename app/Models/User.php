<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
    'motorcycle_type',
    'license_plate',
    'profile_photo',
    'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function communityMemberships()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function ledCommunities()
    {
        return $this->hasMany(Community::class, 'leader_id');
    }

    public function communities()
    {
        return $this->belongsToMany(
            Community::class,
            'community_members',
            'user_id',
            'community_id'
        )->withPivot('status', 'joined_at')
            ->withTimestamps();
    }

    public function eventRsvps()
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLeader(): bool
    {
        return $this->role === 'ketua';
    }

    public function canManageEvents(): bool
    {
        return in_array($this->role, ['admin', 'ketua'], true);
    }

    public function hasCompleteMemberProfile(): bool
    {
        return filled($this->member_number)
            && filled($this->motorcycle_type)
            && filled($this->license_plate);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
