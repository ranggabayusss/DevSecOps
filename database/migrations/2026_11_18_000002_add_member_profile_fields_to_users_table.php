<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('member_number', 32)->nullable()->after('id');
            $table->string('motorcycle_type', 100)->nullable()->after('phone');
            $table->string('license_plate', 20)->nullable()->after('motorcycle_type');
        });

        $prefix = strtolower(trim((string) config('membership.member_number_prefix', 'unrider')));
        $prefix = preg_replace('/[^a-z0-9-]/', '', $prefix) ?: 'unrider';
        $prefix = substr($prefix, 0, 20);

        $userIds = DB::table('users')
            ->select('id')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 WHEN role = 'ketua' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->pluck('id');

        $sequence = 0;

        foreach ($userIds as $userId) {
            $sequence++;

            DB::table('users')->where('id', $userId)->update([
                'member_number' => sprintf('%s-%02d', $prefix, $sequence),
            ]);
        }

        if ($sequence > 0) {
            DB::table('member_number_counters')->updateOrInsert(
                ['prefix' => $prefix],
                [
                    'last_number' => $sequence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('member_number', 'users_member_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_member_number_unique');
            $table->dropColumn(['member_number', 'motorcycle_type', 'license_plate']);
        });
    }
};
