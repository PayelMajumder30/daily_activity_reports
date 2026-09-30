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
            $table->string('type_of_activity')->nullable()->after('complaint_title');
            $table->string('asset_tag_no')->nullable()->after('type_of_activity');

        });

        Schema::table('complaint_temps', function (Blueprint $table) {
            //
            $table->string('type_of_activity')->nullable()->after('complaint_title');
            $table->string('asset_tag_no')->nullable()->after('type_of_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            //
            $table->dropColumn('type_of_activity', 'asset_tag_no');
        });

        Schema::table('complaint_temps', function (Blueprint $table) {
            //
            $table->dropColumn('type_of_activity', 'asset_tag_no');
        });
    }
};
