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
        Schema::table('complaints', function (Blueprint $table) {
            //
            $table->string('emp_code')->nullable()->after('engineer_name');
        });

        Schema::table('complaint_temps', function (Blueprint $table) {
            //
            $table->string('emp_code')->nullable()->after('engineer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            //
            $table->dropColumn('emp_code');
        });

        Schema::table('complaint_temps', function (Blueprint $table) {
            //
            $table->dropColumn('emp_code');
        });
    }
};
