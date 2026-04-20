<?php
// database/migrations/2024_01_01_000004_create_attendances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();
            $table->decimal('clock_in_distance', 10, 2)->nullable();
            $table->decimal('clock_out_distance', 10, 2)->nullable();
            $table->foreignId('clock_in_location_id')->nullable()->constrained('locations');
            $table->foreignId('clock_out_location_id')->nullable()->constrained('locations');
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'holiday'])->default('absent');
            $table->text('remarks')->nullable();
            $table->boolean('is_corrected')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};