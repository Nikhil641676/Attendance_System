<?php
// database/migrations/2024_01_01_000005_create_gps_trackings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();
            
            $table->index(['user_id', 'tracked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_trackings');
    }
};