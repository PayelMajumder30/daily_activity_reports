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
            DB::statement("
                ALTER TABLE asset_issue_registers
                MODIFY issue_status ENUM('Issued', 'Returned', 'Transferred')
                NOT NULL DEFAULT 'Issued'
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_issue_registers', function (Blueprint $table) {
            //
            DB::statement("
                ALTER TABLE asset_issue_registers
                MODIFY issue_status ENUM('Issued', 'Returned')
                NOT NULL DEFAULT 'Issued'
            ");
        });
    }
};
