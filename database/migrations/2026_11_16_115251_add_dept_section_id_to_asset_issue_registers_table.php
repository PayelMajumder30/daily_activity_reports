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
        Schema::table('asset_issue_registers', function (Blueprint $table) {
            //
            $table->foreignId('discipline_id')->nullable()->after('custodian_id')->constrained('disciplines')->nullOnDelete();
            $table->foreignId('dept_section_id')->nullable()->after('discipline_id')->constrained('dept_sections')->nullOnDelete();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_issue_registers', function (Blueprint $table) {
            //
            $table->dropConstrainedForeignId('discipline_id');
            $table->dropConstrainedForeignId('dept_section_id');
        });
    }
};
