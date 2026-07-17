<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_temps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')
                  ->constrained('uploads')
                  ->cascadeOnDelete();

            $table->string('complaint_title');
            $table->string('engineer_name');
            $table->string('status');
            $table->string('resolution_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_temps');
    }
};
