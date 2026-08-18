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
        Schema::create('asset_issue_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete();
            $table->foreignId('custodian_id')->constrained('custodians')->cascadeOnDelete();
            $table->string('user_type');
            $table->string('operator_name')->nullable();
            $table->date('issued_date');
            $table->date('returned_date')->nullable();
            $table->enum('issue_status', ['Issued', 'Returned'])->default('Issued');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_issue_registers');
    }
};
