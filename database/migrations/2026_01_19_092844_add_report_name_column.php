<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taskdownloadreports', function (Blueprint $table) {
            $table->string('report_name')->nullable();
        });

        // For existing records, set report_name to report_id
        DB::table('taskdownloadreports')->update([
            'report_name' => DB::raw('report_id')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taskdownloadreports', function (Blueprint $table) {
            $table->dropColumn('report_name');
        });
    }
};
