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
        Schema::create('asset_outstation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete();
            $table->foreignId('from_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('from_station_id')->constrained('airport_stations')->restrictOnDelete();
            $table->foreignId('to_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('to_station_id')->constrained('airport_stations')->restrictOnDelete();
            $table->date('outstation_date');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();               
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_outstation_history');
    }
};
