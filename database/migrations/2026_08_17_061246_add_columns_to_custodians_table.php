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
        Schema::table('custodians', function (Blueprint $table) {
            //
            $table->foreignId('section_id')->nullable()->after('discipline_id')->constrained('dept_sections')->nullOnDelete();
            $table->string('emp_id')->string('emp_id')->after('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custodians', function (Blueprint $table) {
            //
            $table->dropForeign(['section_id']);
            $table->dropColumn(['section_id', 'emp_id']);
        });
    }
};
