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

            // Auto-generated tag number
            $table->string('tag_no')->unique();

            // Asset Model
            $table->foreignId('asset_model_id')
                ->constrained('asset_models')
                ->cascadeOnDelete();

            // Location
            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            // Purchase Details
            $table->string('po_number');

            // Unique Serial Number
            $table->string('serial_no')->nullable();

            // Installation Details
            $table->date('installation_date');

            // Warranty
            $table->unsignedTinyInteger('warranty_year');
            $table->date('warranty_end');

            // Asset Status
            $table->enum('asset_status', [
                'Available',
                'Assigned',
                'Repair',
                'Scrapped'
            ])->default('Available');

            // Remarks
            $table->text('remarks')->nullable();

            // Created By
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->boolean('status')->default(true);   
                 
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
