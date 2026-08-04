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
        Schema::create('asset_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_tag_id')
                ->constrained('asset_tags')
                ->cascadeOnDelete();

            $table->foreignId('asset_model_id')
                ->constrained('asset_models')
                ->cascadeOnDelete();

            $table->string('serial_no')->nullable();

            $table->date('purchase_date')->nullable();
            $table->date('warranty_end')->nullable();

            $table->enum('asset_status', [
                'Available',
                'Assigned',
                'Repair',
                'Scrapped'
            ])->default('Available');

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_inventories');
    }
};
