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
        Schema::create('issue_registers', function (Blueprint $table) {
            $table->id();
            $table->string('custodian_name');

            $table->foreignId('designation_id')->constrained('designations')->cascadeOnDelete();
               
            $table->foreignId('discipline_id')->constrained('disciplines')->cascadeOnDelete();

            $table->foreignId('section_id')->constrained('dept_sections')->cascadeOnDelete();

            $table->string('user_type');
            $table->string('operator_name')->nullable();

            // for asset tag no.
            $table->foreignId('asset_inventory_id')->constrained('asset_inventories')->cascadeOnDelete(); 
            $table->tinyInteger('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_registers');
    }
};
