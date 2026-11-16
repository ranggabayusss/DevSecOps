<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These tables only supported the retired live GPS feature.
        Schema::dropIfExists('location_histories');
        Schema::dropIfExists('tracking_locations');
    }

    public function down(): void
    {
        // Rollback recreates empty legacy tables; deleted GPS history is not recoverable.
        if (! Schema::hasTable('tracking_locations')) {
            Schema::create('tracking_locations', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('location_histories')) {
            Schema::create('location_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('accuracy', 8, 2)->nullable();
                $table->decimal('speed', 8, 2)->nullable();
                $table->decimal('heading', 6, 2)->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();
                $table->index(['tour_id', 'user_id', 'recorded_at']);
            });
        }
    }
};
