<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A string keeps the set of statuses manageable in application validation
        // and supports the rejected state already used by the request workflow.
        Schema::table('community_members', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
        });
    }

    public function down(): void
    {
        // Keep the widened type to avoid data loss for rows already marked rejected.
    }
};
