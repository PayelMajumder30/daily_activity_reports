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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->tinyInteger('status')
                  ->default(1)
                  ->after('role');
        });

        Schema::table('activities', function (Blueprint $table) {
            //
            $table->tinyInteger('status')
                  ->default(1)
                  ->after('title');
        });

        Schema::table('statuses', function (Blueprint $table) {
            //
            $table->tinyInteger('status')
                  ->default(1)
                  ->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });

        Schema::table('activities', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });

        Schema::table('statuses', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
};
