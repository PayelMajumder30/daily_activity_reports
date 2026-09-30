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
        Schema::create('asset_repair_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete();
            $table->string('vendor_name');
            $table->date('send_date');
            $table->date('return_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_repair_histories');
    }
};
