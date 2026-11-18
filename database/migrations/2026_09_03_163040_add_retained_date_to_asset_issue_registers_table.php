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
            $table->date('retained_date')->nullable()->after('issued_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_issue_registers', function (Blueprint $table) {
            //
            $table->dropForeign(['retained_date']);
        });
    }
};
