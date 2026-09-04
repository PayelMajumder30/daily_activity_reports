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
        Schema::table('asset_inventories', function (Blueprint $table) {
            //
            $table->foreignId('asset_type_id')->nullable()->after('tag_no')->constrained('asset_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_inventories', function (Blueprint $table) {
            //
            $table->dropForeign(['asset_type_id']);
        });
    }
};
