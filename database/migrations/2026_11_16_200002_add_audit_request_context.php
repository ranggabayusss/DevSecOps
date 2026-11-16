<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('description');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
            $table->index(['user_id', 'created_at'], 'activity_logs_user_created_idx');
            $table->index(['activity', 'created_at'], 'activity_logs_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_user_created_idx');
            $table->dropIndex('activity_logs_type_created_idx');
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
