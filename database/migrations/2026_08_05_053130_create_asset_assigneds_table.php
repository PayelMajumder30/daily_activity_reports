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
        Schema::create('asset_assigneds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_inventory_id')
                    ->constrained('asset_inventories')
                    ->cascadeOnDelete();
            $table->foreignId('custodian_id')
                    ->constrained('custodians')
                    ->cascadeOnDelete();
            $table->enum('user_type', ['self', 'Operator', 'Multi User']);           
            $table->string('operator_name')->nullable();
            $table->date('assigned_date');
            $table->date('released_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')
                    ->constrained('users')
                    ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assigneds');
    }
};
