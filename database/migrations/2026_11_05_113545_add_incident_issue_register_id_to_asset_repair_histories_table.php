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
        Schema::table('asset_repair_histories', function (Blueprint $table) {
            //
            $table->foreignId('incident_issue_register_id')->nullable()->after('asset_inventory_id')->constrained('incident_issue_registers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_repair_histories', function (Blueprint $table) {
            //
            $table->dropForeign(['incident_issue_register_id']);
            $table->dropColumn(['incident_issue_register_id']);
        });
    }
};
