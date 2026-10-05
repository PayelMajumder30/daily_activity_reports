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
        Schema::create('incident_issue_registers', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->unique();
            $table->foreignId('asset_issue_register_id')->constrained('asset_issue_registers')->cascadeOnDelete();
            $table->foreignId('support_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('issue_categories')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Open', 'Attend', 'Close'])->default('Open');
            $table->timestamp('call_generated_at')->nullable();
            $table->timestamp('call_attended_at')->nullable();
            $table->timestamp('call_closed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('asset_issue_register_id');
            $table->index('support_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_issue_registers');
    }
};
