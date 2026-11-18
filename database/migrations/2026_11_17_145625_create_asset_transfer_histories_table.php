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
        Schema::create('asset_transfer_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_asset_issue_register_id')->constrained('asset_issue_registers')->restrictOnDelete();
            $table->foreignId('to_asset_issue_register_id')->constrained('asset_issue_registers')->restrictOnDelete();
            $table->date('transfer_date');   
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();              
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfer_histories');
    }
};
