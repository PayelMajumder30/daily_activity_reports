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
        Schema::create('asset_replacement_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete();
            $table->foreignId('old_asset_model_id')->nullable()->constrained('asset_models')->nullOnDelete();
            $table->foreignId('new_asset_model_id')->nullable()->constrained('asset_models')->nullOnDelete();
            $table->string('old_serial_no', 50)->nullable();
            $table->string('new_serial_no', 50)->nullable();
            $table->foreignId('replaced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replaced_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index('asset_inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_replacement_histories');
    }
};
