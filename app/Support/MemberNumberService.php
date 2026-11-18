<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MemberNumberService
{
    public function prefix(): string
    {
        $prefix = strtolower(trim((string) config('membership.member_number_prefix', 'unrider')));
        $prefix = preg_replace('/[^a-z0-9-]/', '', $prefix) ?: 'unrider';

        return substr($prefix, 0, 20);
    }

    public function assignTo(User $user, bool $applySequenceRole = false): User
    {
        if (filled($user->member_number)) {
            return $user;
        }

        return DB::transaction(function () use ($user, $applySequenceRole) {
            $prefix = $this->prefix();

            DB::table('member_number_counters')->insertOrIgnore([
                'prefix' => $prefix,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = DB::table('member_number_counters')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                throw new RuntimeException('Member number counter could not be locked.');
            }

            $sequence = ((int) $counter->last_number) + 1;

            DB::table('member_number_counters')
                ->where('id', $counter->id)
                ->update([
                    'last_number' => $sequence,
                    'updated_at' => now(),
                ]);

            $attributes = [
                'member_number' => sprintf('%s-%02d', $prefix, $sequence),
            ];

            if ($applySequenceRole) {
                $attributes['role'] = $this->roleForSequence($sequence);
            }

            $user->forceFill($attributes)->save();

            return $user->refresh();
        }, 3);
    }

    public function roleForSequence(int $sequence): string
    {
        return match ($sequence) {
            1 => 'admin',
            2 => 'ketua',
            default => 'member',
        };
    }
}
