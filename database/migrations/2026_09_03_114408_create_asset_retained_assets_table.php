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
        Schema::create('asset_retained_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete();
            $table->foreignId('custodian_id')->constrained('custodians')->cascadeOnDelete();              
            $table->foreignId('from_location_id')->constrained('locations');       
            $table->foreignId('from_station_id')->nullable()->constrained('airport_stations');            
            $table->foreignId('to_location_id')->constrained('locations');       
            $table->foreignId('to_station_id')->constrained('airport_stations');
            $table->date('retained_date');
            $table->enum('retained_status',['Retained', 'Received'])->default('Retained');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_retained_assets');
    }
};
